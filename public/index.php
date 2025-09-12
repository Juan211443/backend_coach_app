<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/api/register' && $method==='POST') { require __DIR__ . '/../src/controllers/AuthController.php'; register_handler($pdo); }
if ($uri === '/api/login'    && $method==='POST') { require __DIR__ . '/../src/controllers/AuthController.php'; login_handler($pdo);  }

if ($uri === '/api/me'       && $method==='GET')  { require __DIR__ . '/../src/controllers/PlayerController.php'; me_stats_handler($pdo); }

http_response_code(404);
echo json_encode(['error'=>'Not found']);
