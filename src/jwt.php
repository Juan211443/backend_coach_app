<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function jwt_make(array $payload): string {
  $now = time();
  $exp = $now + (int)envv('JWT_EXPIRES', 3600);
  $payload = array_merge(['iat'=>$now,'exp'=>$exp], $payload);
  return JWT::encode($payload, envv('JWT_SECRET'), 'HS256');
}

function jwt_verify(string $token) {
  return JWT::decode($token, new Key(envv('JWT_SECRET'), 'HS256'));
}
