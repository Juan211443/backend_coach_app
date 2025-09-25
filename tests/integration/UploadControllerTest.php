<?php
// uploadControllerTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/v1/uploadController.php';
require_once __DIR__ . '/../../src/controllers/v1/authController.php';

final class UploadControllerTest extends TestCase {

  protected function setUp(): void {
    test_reset_db();
    unset($GLOBALS['__TEST_RESPONSE__'], $_FILES);
    $_SERVER['HTTP_AUTHORIZATION'] = '';
    putenv('APP_ENV=test');
    putenv('APP_BASE_URL=http://localhost:8000');
  }

  private function makeCoachToken(PDO $pdo): string {
    $GLOBALS['__TEST_BODY__'] = ['email'=>'c@c.com','password'=>'secret1234'];
    register_handler($pdo);
    $pdo->exec("UPDATE user SET role='coach' WHERE email='c@c.com'");
    return jwt_make(['sub'=>1,'role'=>'coach']);
  }

  private function makeTempPng(int $bytes = 1000): string {
    $tmp = tempnam(sys_get_temp_dir(), 'png');
    $fh = fopen($tmp, 'wb');
    fwrite($fh, "\x89PNG\r\n\x1a\n");
    fwrite($fh, str_repeat("\0", max(0, $bytes-8)));
    fclose($fh);
    return $tmp;
  }

  public function test_upload_profile_photo_success(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $tmp = $this->makeTempPng(2048);
    $_FILES = [
      'file' => [
        'name' => 'avatar.png',
        'type' => 'image/png',
        'tmp_name' => $tmp,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
      ],
    ];

    upload_profile_photo($pdo);

    $this->assertSame(201, $GLOBALS['__TEST_RESPONSE__']['code']);
    $data = $GLOBALS['__TEST_RESPONSE__']['data'];
    $this->assertArrayHasKey('url', $data);
    $this->assertArrayHasKey('filename', $data);
    $this->assertArrayHasKey('mime', $data);
    $this->assertSame('image/png', $data['mime']);
    $expectedPath = dirname(__DIR__, 2) . '/public/uploads/profiles/' . $data['filename'];
    $this->assertFileExists($expectedPath);

    @unlink($expectedPath);
    $this->assertFileDoesNotExist($expectedPath);
  }

  public function test_upload_unsupported_type(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $tmp = tempnam(sys_get_temp_dir(), 'txt');
    file_put_contents($tmp, "not-an-image");

    $_FILES = [
      'file' => [
        'name' => 'a.txt',
        'type' => 'text/plain',
        'tmp_name' => $tmp,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
      ],
    ];

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('UNSUPPORTED_IMAGE_TYPE');
    upload_profile_photo($pdo);
  }

  public function test_upload_too_large(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $tmp = $this->makeTempPng(5 * 1024 * 1024 + 10);

    $_FILES = [
      'file' => [
        'name' => 'big.png',
        'type' => 'image/png',
        'tmp_name' => $tmp,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
      ],
    ];

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('IMAGE_TOO_LARGE_MAX_5MB');
    upload_profile_photo($pdo);
  }
}
