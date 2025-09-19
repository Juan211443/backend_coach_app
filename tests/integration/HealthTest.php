<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/healthController.php';

final class HealthTest extends TestCase {
  public function test_live(): void {
    unset($GLOBALS['__TEST_RESPONSE__']);
    health_live();
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $this->assertSame('ok', $GLOBALS['__TEST_RESPONSE__']['data']['status']);
  }

  public function test_ready(): void {
    $pdo = test_pdo();
    unset($GLOBALS['__TEST_RESPONSE__']);
    health_ready($pdo);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $this->assertTrue($GLOBALS['__TEST_RESPONSE__']['data']['db']);
  }
}
