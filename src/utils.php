<?php
// utils.php
function json_ok($data, int $code=200){ http_response_code($code); echo json_encode($data); exit; }
function json_err(string $msg, int $code=400){ http_response_code($code); echo json_encode(['error'=>$msg]); exit; }
function body_json(){ return json_decode(file_get_contents('php://input'), true) ?? []; }
