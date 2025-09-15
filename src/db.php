<?php
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
  envv('DB_HOST','127.0.0.1'),
  envv('DB_PORT','3306'),
  envv('DB_NAME','coach_app')
);

try {
  $pdo = new PDO($dsn, envv('DB_USER','root'), envv('DB_PASS',''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}
