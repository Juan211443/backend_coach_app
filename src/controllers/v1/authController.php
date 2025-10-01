<?php
// authController.php
require_once __DIR__ . '/../../jwt.php';
require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../../validators.php';
require_once __DIR__ . '/../../validators.php';

use CoachApp\ApiPhp\services\TokenService;

function register_handler(PDO $pdo){
  $b = body_json();
  must($b, ['email','password']);

  assert_email($b['email']);
  assert_password($b['password']);

  $role = 'player';

  $st = $pdo->prepare("SELECT user_id FROM user WHERE email=? LIMIT 1");
  $st->execute([$b['email']]);
  if ($st->fetch()) json_err('EMAIL_IN_USE', 409);

  $hash = password_hash($b['password'], PASSWORD_BCRYPT);
  $ins  = $pdo->prepare("INSERT INTO user (email, password_hash, role) VALUES (?,?,?)");
  $ins->execute([$b['email'], $hash, $role]);

  json_ok(['user_id' => (int)$pdo->lastInsertId(), 'email'=>$b['email'], 'role'=>$role], 201);
}

function login_handler(PDO $pdo){
  $b = body_json();
  must($b, ['email','password']);
  assert_email($b['email']);

  $st = $pdo->prepare("SELECT user_id, email, password_hash, role FROM user WHERE email=? AND is_active=1");
  $st->execute([$b['email']]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u || !password_verify($b['password'], $u['password_hash'])) json_err('INVALID_CREDENTIALS', 401);

  $svc = new TokenService($pdo);

  $access = $svc->makeAccessToken([
    'user_id'=>(int)$u['user_id'],
    'role'=>$u['role']
  ]);

  $refresh = $svc->issueRefreshToken((int)$u['user_id']);

  setcookie('rt', $refresh, [
    'expires'  => time() + (int)($_ENV['JWT_REFRESH_TTL'] ?? 1209600),
    'path'     => '/api',
    'secure'   => filter_var($_ENV['COOKIE_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'httponly' => true,
    'samesite' => $_ENV['COOKIE_SAMESITE'] ?? 'Lax',
  ]);

  json_ok([
    'access_token'=>$access,
    'token'=>$access,
    'token_type'=>'Bearer',
    'expires_in'=>(int)($_ENV['JWT_ACCESS_TTL'] ?? 900),
    'user'=>['user_id'=>(int)$u['user_id'],'email'=>$u['email'],'role'=>$u['role']]
  ]);
}

function logout_handler(PDO $pdo): void {
  $rt = $_COOKIE['rt'] ?? null;
  if ($rt) {
    $lookup = base64url(hash('sha256', $rt, true));
    $pdo->prepare('UPDATE refresh_tokens SET revoked_at=NOW() WHERE lookup_hash=? AND revoked_at IS NULL')->execute([$lookup]);
    setcookie('rt','', ['expires'=>time()-3600, 'path'=>'/api']);
  }
  json_ok(['ok'=>true]);
}

function register_coach_handler(PDO $pdo){
  $b = body_json();
  must($b, ['email','password']);
  assert_email($b['email']);
  assert_password($b['password']);

  $st = $pdo->prepare("SELECT user_id FROM user WHERE email=? LIMIT 1");
  $st->execute([$b['email']]);
  if ($st->fetch()) json_err('EMAIL_IN_USE', 409);

  $hash = password_hash($b['password'], PASSWORD_BCRYPT);
  $ins  = $pdo->prepare("INSERT INTO user (email, password_hash, role) VALUES (?,?,?)");
  $ins->execute([$b['email'], $hash, 'coach']);
  $userId = (int)$pdo->lastInsertId();

  json_ok(['user_id'=>$userId, 'email'=>$b['email'], 'role'=>'coach'], 201);
}
