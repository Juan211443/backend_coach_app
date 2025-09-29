<?php
// index.php
if (PHP_SAPI === 'cli-server') {
  $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $file = __DIR__ . '/../' . ltrim($uri, '/');

  if ($uri === '/docs' || $uri === '/docs/') {
    readfile(__DIR__ . '/../docs/index.html');
    return true;
  }

  if ($uri !== '/' && is_file($file)) {
    return false;
  }
}

require __DIR__ . '/../../src/config.php';

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $uri === '/api/v1/health/live') {
  require __DIR__ . '/../../src/controllers/v1/healthController.php';
  health_live(); exit;
}
if ($method === 'GET' && $uri === '/api/v1/health/ready') {
  require __DIR__ . '/../../src/controllers/v1/healthController.php';
  health_ready(); exit;
}

require __DIR__ . '/../../src/db.php';

if ($uri === '/api/v1/auth/register' && $method === 'POST') { 
  require __DIR__ . '/../../src/controllers/v1/authController.php'; 
  register_handler($pdo); exit;
}
if ($uri === '/api/v1/auth/login' && $method === 'POST') { 
  require __DIR__ . '/../../src/controllers/v1/authController.php'; 
  login_handler($pdo); exit;
}
if ($uri === '/api/v1/players' && $method === 'GET') { 
  require __DIR__ . '/../../src/controllers/v1/playerController.php'; 
  players_index($pdo); exit;
}
if ($uri === '/api/v1/players' && $method === 'POST') { 
  require __DIR__ . '/../../src/controllers/v1/playerController.php'; 
  players_store($pdo); exit;
}
if ($uri === '/api/v1/uploads/profile-photo' && $method === 'POST') {
  require __DIR__ . '/../../src/controllers/v1/uploadController.php';
  upload_profile_photo($pdo); exit;
}
if (preg_match('#^/api/v1/players/(\d+)$#', $uri, $m)) {
  require __DIR__ . '/../../src/controllers/v1/playerController.php';
  $id = (int)$m[1];
  if ($method === 'GET')    { player_show($pdo, $id);   exit; }
  if ($method === 'PUT')    { player_update($pdo, $id); exit; }
  if ($method === 'DELETE') { player_delete($pdo, $id); exit; }
}
if ($uri === '/api/v1/attendance' && $method === 'POST') { 
  require __DIR__ . '/../../src/controllers/v1/attendanceController.php'; 
  attendance_mark($pdo); exit;
}
if (preg_match('#^/api/v1/attendance/summary/(\d+)$#', $uri, $m) && $method === 'GET') {
  require __DIR__ . '/../../src/controllers/v1/attendanceController.php';
  attendance_monthly($pdo, (int)$m[1]); exit;
}

if ($uri === '/api/v1/token/refresh' && $method === 'POST') {
  require __DIR__ . '/../../src/controllers/v1/tokenController.php';
  refresh_handler($pdo); exit;
}
if ($uri === '/api/v1/auth/logout' && $method === 'POST') {
  require __DIR__ . '/../../src/controllers/v1/authController.php';
  logout_handler($pdo); exit;
}
if ($uri === '/api/v1/auth/logout-all' && $method === 'POST') {
  require __DIR__ . '/../../src/controllers/v1/authController.php';
  logout_all_handler($pdo); exit;
}
http_response_code(404);
echo json_encode(['error' => 'Not found', 'path' => $uri]);
