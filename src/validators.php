<?php
// validators.php
class TestValidationException extends Exception {}

function must(array $b, array $required, int $code = 400): void {
  $missing = [];

  foreach ($required as $k) {
    if (!array_key_exists($k, $b) || $b[$k] === '' || $b[$k] === null) {
      $missing[] = $k;
    }
  }

  if ($missing) {
    $msg = count($missing) === 1
      ? "Missing {$missing[0]}"
      : 'Missing: ' . implode(', ', $missing);

    json_err($msg, $code);
  }
}

function assert_uploaded_image(array $file, int $maxBytes = 5242880): array {
  if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    json_err('UPLOAD_FAILED', 400);
  }
  if ($file['size'] > $maxBytes) json_err('IMAGE_TOO_LARGE_MAX_5MB', 413);

  $mime = null;
  if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
      $mime = finfo_file($finfo, $file['tmp_name']) ?: null;
      finfo_close($finfo);
    }
  }

  if (!$mime || $mime === 'application/octet-stream') {
    $head = file_get_contents($file['tmp_name'], false, null, 0, 64) ?: '';
    if (strncmp($head, "\x89PNG\r\n\x1a\n", 8) === 0)            $mime = 'image/png';
    elseif (strncmp($head, "\xFF\xD8\xFF", 3) === 0)               $mime = 'image/jpeg';
    elseif (substr($head, 0, 4) === "RIFF" && strpos($head, "WEBP") !== false)
      $mime = 'image/webp';
  }

  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  if (!isset($allowed[$mime])) json_err('UNSUPPORTED_IMAGE_TYPE', 422);

  return ['mime'=>$mime, 'ext'=>$allowed[$mime]];
}

function assert_email(string $email){
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_err('INVALID_EMAIL', 422);
}

function assert_password(string $pwd){
  if (strlen($pwd) < 8) json_err('WEAK_PASSWORD_MIN8', 422);
}

function assert_enum($value, array $allowed, string $err='INVALID_ENUM'){
  if ($value === null) return;
  if (!in_array($value, $allowed, true)) json_err($err, 422);
}

function assert_int_range($value, int $min, int $max, string $field){
  if ($value === null) return;
  if (!is_numeric($value)) json_err("INVALID_$field", 422);
  $v = (int)$value;
  if ($v < $min || $v > $max) json_err("OUT_OF_RANGE_$field", 422);
}

function assert_decimal($value, float $min, float $max, string $field){
  if ($value === null) return;
  if (!is_numeric($value)) json_err("INVALID_$field", 422);
  $v = (float)$value;
  if ($v < $min || $v > $max) json_err("OUT_OF_RANGE_$field", 422);
}

function assert_date($value, string $field){
  if ($value === null) return;
  $d = DateTime::createFromFormat('Y-m-d', $value);
  if (!$d || $d->format('Y-m-d') !== $value) json_err("INVALID_DATE_$field", 422);
}

function assert_datetime($value, string $field){
  if ($value === null) return;
  $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
  if (!$d || $d->format('Y-m-d H:i:s') !== $value) json_err("INVALID_DATETIME_$field", 422);
}

function sanitize_paging(&$limit, &$offset, int $max=100){
  $limit  = max(1, min((int)$limit, $max));
  $offset = max(0, (int)$offset);
}
