<?php
// uploadController.php
require_once __DIR__ . '/../../middlewares.php';
require_once __DIR__ . '/../../utils.php';
require_once __DIR__ . '/../../validators.php';

function upload_profile_photo(PDO $pdo) {

  if (!isset($_FILES['file'])) json_err('FILE_REQUIRED', 400);

  $meta = assert_uploaded_image($_FILES['file']);
  $ext  = $meta['ext'];

  $uuid  = bin2hex(random_bytes(16));
  $fname = $uuid . '.' . $ext;

  $dir = __DIR__ . '/../../../public/uploads/profiles';
  if (!is_dir($dir)) mkdir($dir, 0775, true);

  $dest = $dir . '/' . $fname;
  if (!move_uploaded_file_safe($_FILES['file']['tmp_name'], $dest)) {
    json_err('MOVE_FAILED', 500);
  }

  $baseUrl = rtrim(envv('APP_BASE_URL', 'http://localhost:8000'), '/');
  $url = $baseUrl . '/uploads/profiles/' . $fname;

  json_ok([
    'url' => $url,
    'filename' => $fname,
    'mime' => $meta['mime'],
    'size' => (int)$_FILES['file']['size']
  ], 201);
}
