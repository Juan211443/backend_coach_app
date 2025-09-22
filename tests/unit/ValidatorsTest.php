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
}
