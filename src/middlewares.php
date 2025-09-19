<?php
// middlewares.php
function require_auth() {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
    json_err('No token', 401);
  }
  try {
    $decoded = jwt_verify($m[1]);
    return (array)$decoded;
  } catch (Throwable $e) {
    json_err('Invalid token', 401);
  }
}

function require_auth_role(array $allowedRoles): array {
  $claims = require_auth();
  $role = $claims['role'] ?? null;
  if (!$role || !in_array($role, $allowedRoles, true)) {
    json_err('FORBIDDEN_ROLE', 403);
  }
  return $claims;
}
