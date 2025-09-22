<?php
// AuthTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/validators.php';
require_once __DIR__ . '/../../src/controllers/v1/authController.php';

final class AuthTest extends TestCase {

  protected function setUp(): void {
    test_reset_db();
    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
  }

  public function test_register_and_login(): void {
    $pdo = test_pdo();

    $GLOBALS['__TEST_BODY__'] = [
      'email' => 'coach@demo.com',
      'password' => 'secret1234'
    ];
    register_handler($pdo);
    $this->assertSame(201, $GLOBALS['__TEST_RESPONSE__']['code']);
    $this->assertSame('coach@demo.com', $GLOBALS['__TEST_RESPONSE__']['data']['email']);
    $this->assertSame('player', $GLOBALS['__TEST_RESPONSE__']['data']['role']);

    $pdo->prepare("UPDATE user SET role='coach' WHERE email=?")->execute(['coach@demo.com']);

    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $GLOBALS['__TEST_BODY__'] = [
      'email' => 'coach@demo.com',
      'password' => 'secret1234'
    ];
    login_handler($pdo);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $this->assertArrayHasKey('token', $GLOBALS['__TEST_RESPONSE__']['data']);
    $this->assertSame('coach', $GLOBALS['__TEST_RESPONSE__']['data']['user']['role']);
  }
}
