<?php
// playerController.php
require_once __DIR__ . '/../../middlewares.php';
require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../../validators.php';

function players_index(PDO $pdo){
  $limit  = (int)($_GET['limit']  ?? 20);
  $offset = (int)($_GET['offset'] ?? 0);
  sanitize_paging($limit, $offset, 100);

  $categoryTxt = trim($_GET['category'] ?? '');
  $teamTxt     = trim($_GET['team'] ?? '');

  $from = "
    FROM player pl
    JOIN person p ON p.person_id = pl.person_id
    LEFT JOIN player_position pos ON pos.id = pl.position_id
    LEFT JOIN category c ON c.id = pl.current_category_id
    LEFT JOIN sports_academy sa ON sa.id = pl.sports_academy_id
    LEFT JOIN team t ON t.id = pl.current_team_id
    WHERE 1=1
  ";

  $where  = '';
  $params = [];

  if ($categoryTxt !== '') {
    if (ctype_digit($categoryTxt)) {
      $where   .= " AND (c.year = ? OR CAST(c.year AS CHAR) LIKE ? OR c.name LIKE ?)";
      $params[] = (int)$categoryTxt;
      $params[] = "%$categoryTxt%";
      $params[] = "%$categoryTxt%";
    } else {
      $where   .= " AND (c.name LIKE ? OR CAST(c.year AS CHAR) LIKE ?)";
      $params[] = "%$categoryTxt%";
      $params[] = "%$categoryTxt%";
    }
  }

  if ($teamTxt !== '') {
    $where   .= " AND (t.name LIKE ?)";
    $params[] = "%$teamTxt%";
  }

  $countSql = "SELECT COUNT(*) $from $where";
  $st = $pdo->prepare($countSql);
  $st->execute($params);
  $total = (int)$st->fetchColumn();

  $select = "
    SELECT
      p.person_id, p.first_name, p.last_name,
      pl.jersey_number, pl.position_id, pos.code AS position_code, pos.name AS position_name,
      pl.current_category_id, c.name AS category_name, c.year AS category_year,
      pl.current_team_id, t.name AS team_name
    $from
    $where
    ORDER BY c.year DESC, t.name ASC, p.last_name, p.first_name
    LIMIT ? OFFSET ?
  ";
  $selParams = array_merge($params, [$limit, $offset]);

  $st = $pdo->prepare($select);
  $st->execute($selParams);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  json_ok([
    'data' => $rows,
    'meta' => ['total'=>$total, 'limit'=>$limit, 'offset'=>$offset],
  ]);
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
  $claims = require_auth_role(['coach']);
  
  $b = body_json();
  must($b, ['first_name','last_name','birth_date']);

  assert_date($b['birth_date'], 'birth_date');
  assert_enum($b['preferred_foot'] ?? 'Right', ['Left','Right','Both'], 'INVALID_PREFERRED_FOOT');

  assert_int_range($b['jersey_number'] ?? null, 1, 99, 'jersey_number');
  $yearNow = (int)date('Y');
  assert_int_range($b['enrollment_year'] ?? null, 1900, $yearNow, 'enrollment_year');

  assert_decimal($b['height_cm'] ?? null, 0, 300, 'height_cm');
  assert_decimal($b['weight_kg'] ?? null, 0, 500, 'weight_kg');

  try {
    $pdo->beginTransaction();

    $p = $pdo->prepare("
      INSERT INTO person (first_name,last_name,birth_date,preferred_foot,height_cm,weight_kg,phone,profile_photo_url)
      VALUES (?,?,?,?,?,?,?,?)");
    $p->execute([
      $b['first_name'],$b['last_name'],$b['birth_date'],
      $b['preferred_foot'] ?? 'Right',
      $b['height_cm'] ?? null, $b['weight_kg'] ?? null,
      $b['phone'] ?? null, $b['profile_photo_url'] ?? null
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
  $claims = require_auth_role(['coach']);

  $b = body_json();
  
  if (array_key_exists('birth_date', $b))     assert_date($b['birth_date'], 'birth_date');
  if (array_key_exists('preferred_foot',$b))  assert_enum($b['preferred_foot'], ['Left','Right','Both'], 'INVALID_PREFERRED_FOOT');
  if (array_key_exists('jersey_number',$b))   assert_int_range($b['jersey_number'], 1, 99, 'jersey_number');
  if (array_key_exists('enrollment_year',$b)) assert_int_range($b['enrollment_year'], 1900, (int)date('Y'), 'enrollment_year');
  if (array_key_exists('height_cm',$b))       assert_decimal($b['height_cm'], 0, 300, 'height_cm');
  if (array_key_exists('weight_kg',$b))       assert_decimal($b['weight_kg'], 0, 500, 'weight_kg');

  try {
    $pdo->beginTransaction();

    $pCols = $pVals = [];
    foreach (['first_name','last_name','birth_date','preferred_foot','height_cm','weight_kg','phone','profile_photo_url'] as $f) {
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
  $claims = require_auth_role(['coach']);

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
