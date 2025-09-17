<?php
// config.php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

header('Content-Type: application/json; charset=utf-8');

$env = envv('APP_ENV', 'dev');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$allowed = array_filter(array_map('trim', explode(',', envv('FRONTEND_ORIGINS',''))));
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($env === 'dev') {
  header('Access-Control-Allow-Origin: *');
} else {
  if ($origin && in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
  } else if ($origin) {
    if ($method === 'OPTIONS') { http_response_code(403); exit; }
  }
  if ($method === 'OPTIONS') { http_response_code(204); exit; }
}

header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($method === 'OPTIONS') { http_response_code(204); exit; }

function envv($key, $default = null) {
  return $_ENV[$key] ?? getenv($key) ?: $default;
}
