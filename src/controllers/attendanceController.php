<?php
// attendanceController.php
require_once __DIR__ . '/../middlewares.php';
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../validators.php';

function attendance_mark(PDO $pdo){
  $claims = require_auth_role(['coach']);

  $b = body_json();
  must($b, ['session_id','player_id','status']);

  assert_enum($b['status'], ['present','absent','late','excused'], 'INVALID_STATUS');

  if (isset($b['checkin_at'])) assert_datetime($b['checkin_at'], 'checkin_at');
  if (!is_numeric($b['session_id']) || !is_numeric($b['player_id'])) json_err('INVALID_IDS', 422);

  $sql = "
    INSERT INTO attendance (session_id, player_id, status, checkin_at, remarks)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE status=VALUES(status), checkin_at=VALUES(checkin_at), remarks=VALUES(remarks)
  ";
  $pdo->prepare($sql)->execute([
    $b['session_id'], $b['player_id'], $b['status'],
    $b['checkin_at'] ?? null, $b['remarks'] ?? null
  ]);
  json_ok(['ok'=>true], 201);
}

function attendance_monthly(PDO $pdo, int $playerId){
  $year  = (int)($_GET['year']  ?? date('Y'));
  $month = (int)($_GET['month'] ?? date('n'));

  $st = $pdo->prepare("
    SELECT SUM(a.status='present') AS presents, COUNT(*) AS total
    FROM attendance a
    JOIN session s ON s.id = a.session_id
    WHERE a.player_id = ? AND YEAR(s.date) = ? AND MONTH(s.date) = ?
  ");
  $st->execute([$playerId, $year, $month]);
  $row = $st->fetch(PDO::FETCH_ASSOC) ?: ['presents'=>0,'total'=>0];

  $presents = (int)($row['presents'] ?? 0);
  $total    = (int)($row['total'] ?? 0);
  $percent  = $total ? round(($presents / $total) * 100) : 0;

  json_ok(['player_id'=>$playerId,'year'=>$year,'month'=>$month,'presents'=>$presents,'total'=>$total,'percent'=>$percent]);
}
