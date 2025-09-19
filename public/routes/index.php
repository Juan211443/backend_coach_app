<?php
// index.php
require __DIR__ . '/../src/config.php';

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && $uri === '/health') {
  require __DIR__ . '/../src/controllers/healthController.php';
  health_ready();
}
if ($method === 'GET' && $uri === '/health/live') {
  require __DIR__ . '/../src/controllers/healthController.php';
  health_live();
}
if ($method === 'GET' && $uri === '/health/ready') {
  require __DIR__ . '/../src/controllers/healthController.php';
  health_ready();
}

require __DIR__ . '/../src/db.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/api/auth/register' && $method === 'POST') { require __DIR__ . '/../src/controllers/authController.php'; register_handler($pdo); }
if ($uri === '/api/auth/login'    && $method === 'POST') { require __DIR__ . '/../src/controllers/authController.php'; login_handler($pdo);  }

if ($uri === '/api/players'       && $method === 'GET')  { require __DIR__ . '/../src/controllers/playerController.php'; players_index($pdo); }
if ($uri === '/api/players'       && $method === 'POST') { require __DIR__ . '/../src/controllers/playerController.php'; players_store($pdo); }

if (preg_match('#^/api/players/(\d+)$#', $uri, $m)) {
  require __DIR__ . '/../src/controllers/playerController.php';
  $id = (int)$m[1];
  if ($method === 'GET')    { player_show($pdo, $id);   exit; }
  if ($method === 'PUT')    { player_update($pdo, $id); exit; }
  if ($method === 'DELETE') { player_delete($pdo, $id); exit; }
}

if ($uri === '/api/attendance' && $method === 'POST') { require __DIR__ . '/../src/controllers/attendanceController.php'; attendance_mark($pdo); }

if (preg_match('#^/api/attendance/summary/(\d+)$#', $uri, $m) && $method === 'GET') {
  require __DIR__ . '/../src/controllers/attendanceController.php';
  attendance_monthly($pdo, (int)$m[1]);
  exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found', 'path' => $uri]);
