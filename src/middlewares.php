<?php
function require_auth() {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
    http_response_code(401); echo json_encode(['error'=>'No token']); exit;
  }
  try {
    $decoded = jwt_verify($m[1]);
    return (array)$decoded;
  } catch (Throwable $e) {
    http_response_code(401); echo json_encode(['error'=>'Invalid token']); exit;
  }
}
