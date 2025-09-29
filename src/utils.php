<?php
// utils.php
if (!function_exists('json_ok')) {
  function json_ok($data, int $code=200){ 
    if (envv('APP_ENV') === 'test') {
      $GLOBALS['__TEST_RESPONSE__'] = ['code'=>$code, 'data'=>$data];
      return;
    }
    http_response_code($code); echo json_encode($data); exit;
  }
}
if (!function_exists('json_err')) {
  function json_err(string $msg, int $code=400){ 
    if (envv('APP_ENV') === 'test') {
      throw new RuntimeException($msg, $code);
    }
    http_response_code($code); echo json_encode(['error'=>$msg]); exit;
  }
}
if (!function_exists('envv')) {
  function envv(string $key, $default=null) {
    $v = getenv($key);
    return $v === false ? $default : $v;
  }
}
if (!function_exists('move_uploaded_file_safe')) {
  function move_uploaded_file_safe(string $tmp, string $dest): bool {
    if (envv('APP_ENV') === 'test') {
      return @rename($tmp, $dest);
    }
    return move_uploaded_file($tmp, $dest);
  }
}
if (!function_exists('body_json')) {
  function body_json(): array {
    if (envv('APP_ENV') === 'test' && isset($GLOBALS['__TEST_BODY__'])) {
      return (array)$GLOBALS['__TEST_BODY__'];
    }

    static $cache = null;
    if ($cache !== null) return $cache;

    $raw = file_get_contents('php://input');

    if ($raw === '' || $raw === null) {
      json_err('EMPTY_BODY', 400);
    }

    $data = json_decode($raw, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
      json_err('INVALID_JSON', 400);
    }

    if (!is_array($data)) {
      json_err('INVALID_JSON', 400);
    }

    return $cache = $data;
  }
}
if (!function_exists('base64url')) {
  function base64url(string $bin): string {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
  }
}