<?php
// ValidatorsTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/validators.php';
require_once __DIR__ . '/../../src/utils.php';

final class ValidatorsTest extends TestCase {

  public function test_email_valido(): void {
    $this->expectNotToPerformAssertions();
    assert_email('a@b.co');
  }

  public function test_email_invalido(): void {
    $this->expectExceptionMessage('INVALID_EMAIL');
    assert_email('zzz');
  }

  public function test_password_corta(): void {
    $this->expectExceptionMessage('WEAK_PASSWORD_MIN8');
    assert_password('1234567');
  }

  public function test_enum_ok(): void {
    $this->expectNotToPerformAssertions();
    assert_enum('player', ['player','coach']);
  }

  public function test_enum_fail(): void {
    $this->expectExceptionMessage('INVALID_ENUM');
    assert_enum('admin', ['player','coach']);
  }

  public function test_assert_uploaded_image_png_ok(): void {
    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/fe2yWwAAAAASUVORK5CYII=';
    $bytes = base64_decode($pngBase64, true);
    $tmp = tempnam(sys_get_temp_dir(), 'png');
    file_put_contents($tmp, $bytes);
    $file = ['tmp_name'=>$tmp, 'size'=>filesize($tmp), 'error'=>UPLOAD_ERR_OK];
    $meta = assert_uploaded_image($file);
    $this->assertSame('image/png', $meta['mime']);
    unlink($tmp);
  }
}
