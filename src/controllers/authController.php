<?php
require __DIR__ . '/../jwt.php';
require __DIR__ . '/../utils.php';

function register_handler(PDO $pdo){
  $b = body_json();
  foreach (['email','password'] as $k) if (empty($b[$k])) json_err("Missing $k", 400);

  $role = $b['role'] ?? 'player';
  if (!in_array($role, ['player','coach','admin'])) json_err('Invalid role', 400);

  $st = $pdo->prepare("SELECT user_id FROM user WHERE email=? LIMIT 1");
  $st->execute([$b['email']]);
  if ($st->fetch()) json_err('Email already in use', 409);

  $hash = password_hash($b['password'], PASSWORD_BCRYPT);
  $ins  = $pdo->prepare("INSERT INTO user (email, password_hash, role) VALUES (?,?,?)");
  $ins->execute([$b['email'], $hash, $role]);

  json_ok(['user_id' => (int)$pdo->lastInsertId(), 'email'=>$b['email'], 'role'=>$role], 201);
}

function login_handler(PDO $pdo){
  $b = body_json();
  foreach (['email','password'] as $k) if (empty($b[$k])) json_err("Missing $k", 400);

  $st = $pdo->prepare("SELECT user_id, email, password_hash, role FROM user WHERE email=? AND is_active=1");
  $st->execute([$b['email']]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u || !password_verify($b['password'], $u['password_hash'])) json_err('Invalid credentials', 401);

  $token = jwt_make(['sub'=>(int)$u['user_id'], 'role'=>$u['role']]);
  json_ok(['token'=>$token, 'user'=>['user_id'=>(int)$u['user_id'],'email'=>$u['email'],'role'=>$u['role']]]);
}
