<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/../jwt.php';
require __DIR__ . '/../utils.php';

function register_handler(PDO $pdo){
  $b = body_json();
  foreach (['username','password','role'] as $k) if (empty($b[$k])) json_err("Falta $k", 400);

  if (!in_array($b['role'], ['estudiante','jugador','entrenador','admin'])) json_err('Rol inválido', 400);

  $hash = password_hash($b['password'], PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
  try {
    $stmt->execute([$b['username'], $hash, $b['role']]);
  } catch(Throwable $e){ json_err('Usuario ya existe', 409); }

  json_ok(['success' => true]);
}

function login_handler(PDO $pdo){
  $b = body_json();
  foreach (['username','password'] as $k) if (empty($b[$k])) json_err("Falta $k", 400);

  $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username=?");
  $stmt->execute([$b['username']]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u || !password_verify($b['password'], $u['password'])) json_err('Credenciales inválidas', 401);

  $token = jwt_make(['sub'=>$u['id'], 'role'=>$u['role']]);
  json_ok(['token'=>$token,'role'=>$u['role']]);
}
