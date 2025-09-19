<?php
// healthController.php

function health_live(): void {
  header('Cache-Control: no-store');
  http_response_code(200);
  echo json_encode(['status' => ['app' => 'ok'], 'time' => date(DATE_ATOM)]);
  exit;
}

function health_ready(): void {
  $resp = ['app' => 'ok'];
  $code = 200;

  try {
    $dsn = sprintf(
      'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
      envv('DB_HOST', '127.0.0.1'),
      envv('DB_PORT', '3306'),
      envv('DB_NAME', 'coach_app')
    );
    $pdo = new PDO($dsn, envv('DB_USER','root'), envv('DB_PASS',''), [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_TIMEOUT            => 2,
    ]);
    $pdo->query('SELECT 1');
    $resp['db'] = 'ok';
  } catch (Throwable $e) {
    $resp['db']   = 'down';
    $resp['error'] = 'db_unreachable';
    $code = 503;
  }

  header('Cache-Control: no-store');
  http_response_code($code);
  echo json_encode(['status' => $resp, 'time' => date(DATE_ATOM)]);
  exit;
}
