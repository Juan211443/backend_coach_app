<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/../middlewares.php';
require __DIR__ . '/../utils.php';

function me_stats_handler(PDO $pdo){
  $claims = require_auth();
  $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id=?");
  $stmt->execute([$claims['sub'] ?? 0]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) json_err('No encontrado', 404);

  json_ok(['user'=>$user, 'sample'=>'ok']);
}
