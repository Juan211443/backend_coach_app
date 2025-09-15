<?php
require __DIR__ . '/../middlewares.php';
require __DIR__ . '/../utils.php';

function players_index(PDO $pdo){
  $q      = $_GET['q']      ?? null;
  $limit  = (int)($_GET['limit']  ?? 20);
  $offset = (int)($_GET['offset'] ?? 0);

  $sql = "
    SELECT
      p.person_id, p.first_name, p.last_name, p.birth_date,
      p.preferred_foot, p.height_cm, p.weight_kg, p.phone, p.profile_photo,
      pl.jersey_number, pl.position_id, pos.code AS position_code, pos.name AS position_name,
      pl.current_category_id, c.name AS category_name,
      pl.sports_academy_id, sa.name AS academy_name,
      pl.enrollment_year, pl.health_status, pl.current_injuries,
      pl.current_team_id, t.name AS team_name
    FROM player pl
    JOIN person p ON p.person_id = pl.person_id
    LEFT JOIN player_position pos ON pos.id = pl.position_id
    LEFT JOIN category c ON c.id = pl.current_category_id
    LEFT JOIN sports_academy sa ON sa.id = pl.sports_academy_id
    LEFT JOIN team t ON t.id = pl.current_team_id
    WHERE 1=1";
  $params = [];
  if ($q) { $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }
  $sql .= " ORDER BY p.last_name, p.first_name LIMIT ? OFFSET ?";
  $params[] = $limit; $params[] = $offset;

  $st = $pdo->prepare($sql); $st->execute($params);
  json_ok($st->fetchAll(PDO::FETCH_ASSOC));
}

function player_show(PDO $pdo, int $personId){
  $st = $pdo->prepare("
    SELECT p.*, pl.jersey_number, pl.position_id, pl.current_category_id,
           pl.sports_academy_id, pl.enrollment_year, pl.health_status, pl.current_injuries,
           pl.current_team_id
    FROM person p
    JOIN player pl ON pl.person_id = p.person_id
    WHERE p.person_id = ?");
  $st->execute([$personId]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) json_err('PLAYER_NOT_FOUND', 404);
  json_ok($row);
}

function players_store(PDO $pdo){
  $claims = require_auth(); // public

  $b = body_json();
  foreach (['first_name','last_name','birth_date'] as $k)
    if (!isset($b[$k])) json_err("Missing $k", 400);

  try {
    $pdo->beginTransaction();

    $p = $pdo->prepare("
      INSERT INTO person (first_name,last_name,birth_date,preferred_foot,height_cm,weight_kg,phone,profile_photo)
      VALUES (?,?,?,?,?,?,?,?)");
    $p->execute([
      $b['first_name'],$b['last_name'],$b['birth_date'],
      $b['preferred_foot'] ?? 'Right',
      $b['height_cm'] ?? null, $b['weight_kg'] ?? null,
      $b['phone'] ?? null, $b['profile_photo'] ?? null
    ]);
    $personId = (int)$pdo->lastInsertId();

    $pl = $pdo->prepare("
      INSERT INTO player (person_id,jersey_number,position_id,current_category_id,sports_academy_id,
                          enrollment_year,health_status,current_injuries,current_team_id)
      VALUES (?,?,?,?,?,?,?,?,?)");
    $pl->execute([
      $personId,
      $b['jersey_number'] ?? null,
      $b['position_id'] ?? null,
      $b['current_category_id'] ?? null,
      $b['sports_academy_id'] ?? null,
      $b['enrollment_year'] ?? null,
      $b['health_status'] ?? null,
      $b['current_injuries'] ?? null,
      $b['current_team_id'] ?? null
    ]);

    $pdo->commit();
    player_show($pdo, $personId);
  } catch(Throwable $e){
    $pdo->rollBack();
    json_err('TX_FAILED: '.$e->getMessage(), 500);
  }
}

function player_update(PDO $pdo, int $personId){
  $claims = require_auth();

  $b = body_json();
  try {
    $pdo->beginTransaction();

    $pCols = $pVals = [];
    foreach (['first_name','last_name','birth_date','preferred_foot','height_cm','weight_kg','phone','profile_photo'] as $f) {
      if (array_key_exists($f, $b)) { $pCols[]="$f=?"; $pVals[]=$b[$f]; }
    }
    if ($pCols) {
      $sql = "UPDATE person SET ".implode(', ', $pCols)." WHERE person_id=?";
      $pVals[] = $personId;
      $pdo->prepare($sql)->execute($pVals);
    }

    $plCols = $plVals = [];
    foreach (['jersey_number','position_id','current_category_id','sports_academy_id','enrollment_year','health_status','current_injuries','current_team_id'] as $f) {
      if (array_key_exists($f, $b)) { $plCols[]="$f=?"; $plVals[]=$b[$f]; }
    }
    if ($plCols) {
      $sql = "UPDATE player SET ".implode(', ', $plCols)." WHERE person_id=?";
      $plVals[] = $personId;
      $pdo->prepare($sql)->execute($plVals);
    }

    $pdo->commit();
    player_show($pdo, $personId);
  } catch(Throwable $e){
    $pdo->rollBack();
    json_err('TX_FAILED: '.$e->getMessage(), 500);
  }
}

function player_delete(PDO $pdo, int $personId){
  $claims = require_auth();

  try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM player WHERE person_id=?")->execute([$personId]);
    $pdo->prepare("DELETE FROM person WHERE person_id=?")->execute([$personId]);
    $pdo->commit();
    json_ok(['ok'=>true]);
  } catch(Throwable $e){
    $pdo->rollBack();
    json_err('DELETE_FAILED: '.$e->getMessage(), 500);
  }
}
