<?php
function must(array $b, array $keys){
  foreach ($keys as $k) if (!isset($b[$k]) || $b[$k] === '') json_err("Missing $k", 400);
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

/** sanea limit/offset */
function sanitize_paging(&$limit, &$offset, int $max=100){
  $limit  = max(1, min((int)$limit, $max));
  $offset = max(0, (int)$offset);
}
