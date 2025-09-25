<?php
// healthController.php
require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../../config.php';

function health_live(): void {
  json_ok(['status' => 'ok'], 200);
}

function health_ready(?PDO $pdo = null): void {
  $ok = false;
  try {
    if (!$pdo) {
      $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        envv('DB_HOST', '127.0.0.1'),
        envv('DB_PORT', '3306'),
        envv('DB_NAME', 'coach_app_dev')
      );
      $pdo = new PDO($dsn, envv('DB_USER','root'), envv('DB_PASS',''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2,
      ]);
    }
    $pdo->query('SELECT 1');
    $ok = true;
  } catch (Throwable $e) {
    $ok = false;
  }

  json_ok(['db' => $ok, 'time' => date(DATE_ATOM)], $ok ? 200 : 503);
}
