<?php
// middlewares.php
require_once __DIR__ . '/services/tokenService.php';
use CoachApp\ApiPhp\services\TokenService;

function require_auth(?PDO $pdo) {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
    json_err('No token', 401);
  }
  try {
    $svc = new TokenService($pdo ?? require __DIR__ . '/db.php');
    $claims = $svc->verifyAccessToken($m[1]);
    return $claims;
  } catch (Throwable $e) {
    json_err('Invalid token', 401);
  }
}

function require_auth_role(array $allowedRoles, ?PDO $pdo): array {
  $claims = require_auth($pdo);
  $role = $claims['role'] ?? null;
  if (!$role || !in_array($role, $allowedRoles, true)) {
    json_err('FORBIDDEN_ROLE', 403);
  }
  return $claims;
}