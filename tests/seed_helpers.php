<?php
// tests/seed_helpers.php

function seed_player_positions(PDO $pdo): void {
  $rows = [
    [1, 'GK',  'Goalkeeper'],
    [2, 'RB',  'Right Back'],
    [3, 'CB',  'Center Back'],
    [4, 'LB',  'Left Back'],
    [5, 'DM',  'Defensive Midfielder'],
    [6, 'CM',  'Central Midfielder'],
    [7, 'AM',  'Attacking Midfielder'],
    [8, 'RW',  'Right Winger'],
    [9, 'ST',  'Striker'],
    [10,'LW',  'Left Winger'],
  ];

  $st = $pdo->prepare("INSERT IGNORE INTO player_position (id, code, name) VALUES (?,?,?)");
  foreach ($rows as $r) $st->execute($r);
}

function seed_academy(PDO $pdo, string $name = 'Academia Demo'): int {
  $st = $pdo->prepare("INSERT INTO sports_academy(name) VALUES(?)");
  $st->execute([$name]);
  return (int)$pdo->lastInsertId();
}

function seed_category(PDO $pdo, string $name, ?int $year): int {
  $st = $pdo->prepare("INSERT INTO category(name, year) VALUES(?, ?)");
  $st->execute([$name, $year]);
  return (int)$pdo->lastInsertId();
}

function seed_team(PDO $pdo, int $academyId, string $name, int $categoryId, ?int $coachPersonId = null): int {
  $st = $pdo->prepare("
    INSERT INTO team(sports_academy_id, name, category_id, coach_person_id)
    VALUES(?, ?, ?, ?)
  ");
  $st->execute([$academyId, $name, $categoryId, $coachPersonId]);
  return (int)$pdo->lastInsertId();
}

function seed_person(PDO $pdo, array $overrides = []): int {
  $defaults = [
    'first_name'        => 'Nombre',
    'last_name'         => 'Apellido',
    'birth_date'        => '2010-01-01',
    'preferred_foot'    => 'Right',
    'height_cm'         => null,
    'weight_kg'         => null,
    'phone'             => null,
    'profile_photo_url' => null,
  ];
  $b = array_merge($defaults, $overrides);

  $st = $pdo->prepare("
    INSERT INTO person (first_name, last_name, birth_date, preferred_foot, height_cm, weight_kg, phone, profile_photo_url)
    VALUES(?,?,?,?,?,?,?,?)
  ");
  $st->execute([
    $b['first_name'], $b['last_name'], $b['birth_date'], $b['preferred_foot'],
    $b['height_cm'], $b['weight_kg'], $b['phone'], $b['profile_photo_url'],
  ]);
  return (int)$pdo->lastInsertId();
}

function seed_player(PDO $pdo, int $personId, array $attrs = []): int {
  $defaults = [
    'jersey_number'       => null,
    'position_id'         => null,
    'current_category_id' => null,
    'sports_academy_id'   => null,
    'enrollment_year'     => null,
    'health_status'       => null,
    'current_injuries'    => null,
    'current_team_id'     => null,
  ];
  $a = array_merge($defaults, $attrs);

  $st = $pdo->prepare("
    INSERT INTO player (person_id, jersey_number, position_id, current_category_id, sports_academy_id,
                        enrollment_year, health_status, current_injuries, current_team_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $st->execute([
    $personId,
    $a['jersey_number'],
    $a['position_id'],
    $a['current_category_id'],
    $a['sports_academy_id'],
    $a['enrollment_year'],
    $a['health_status'],
    $a['current_injuries'],
    $a['current_team_id'],
  ]);

  return $personId;
}

function seed_session(PDO $pdo, int $teamId, string $type = 'training', string $date = '2025-01-01', array $extra = []): int {
  $defaults = [
    'starts_at' => null,
    'ends_at'   => null,
    'location'  => null,
    'opponent'  => null,
    'notes'     => null,
  ];
  $e = array_merge($defaults, $extra);

  $st = $pdo->prepare("
    INSERT INTO session (team_id, type, date, starts_at, ends_at, location, opponent, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $st->execute([$teamId, $type, $date, $e['starts_at'], $e['ends_at'], $e['location'], $e['opponent'], $e['notes']]);
  return (int)$pdo->lastInsertId();
}

function upsert_attendance(PDO $pdo, int $sessionId, int $playerId, string $status = 'present', ?string $checkinAt = null, ?string $remarks = null): int {
  $pdo->prepare("
    INSERT INTO attendance (session_id, player_id, status, checkin_at, remarks)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status), checkin_at = VALUES(checkin_at), remarks = VALUES(remarks)
  ")->execute([$sessionId, $playerId, $status, $checkinAt, $remarks]);

  $st = $pdo->prepare("SELECT id FROM attendance WHERE session_id=? AND player_id=?");
  $st->execute([$sessionId, $playerId]);
  return (int)$st->fetchColumn();
}
