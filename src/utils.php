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
if (!function_exists('body_json')) {
  function body_json(){ 
    if (envv('APP_ENV') === 'test' && isset($GLOBALS['__TEST_BODY__'])) {
      return $GLOBALS['__TEST_BODY__'];
    }
    return json_decode(file_get_contents('php://input'), true) ?? []; 
  }
}
