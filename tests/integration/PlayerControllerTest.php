<?php
// PlayerControllerTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/playerController.php';
require_once __DIR__ . '/../../src/controllers/authController.php';

final class PlayerControllerTest extends TestCase {

  protected function setUp(): void {
    test_reset_db();
    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $_SERVER['HTTP_AUTHORIZATION'] = '';
  }

  private function makeCoachToken(PDO $pdo): string {
    $GLOBALS['__TEST_BODY__'] = ['email'=>'c@c.com','password'=>'secret1234'];
    register_handler($pdo);
    $pdo->exec("UPDATE user SET role='coach' WHERE email='c@c.com'");
    return jwt_make(['sub'=>1,'role'=>'coach']);
  }

  public function test_store_and_list_players(): void {
    $pdo = test_pdo();
    $token = $this->makeCoachToken($pdo);
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";

    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $GLOBALS['__TEST_BODY__'] = [
      'first_name'=>'Juan',
      'last_name' =>'Pérez',
      'birth_date'=>'2010-01-05',
      'jersey_number'=>9,
      'preferred_foot'=>'Right'
    ];
    players_store($pdo);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $player = $GLOBALS['__TEST_RESPONSE__']['data'];
    $this->assertSame('Juan', $player['first_name']);

    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    players_index($pdo);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $list = $GLOBALS['__TEST_RESPONSE__']['data'];
    $this->assertCount(1, $list);
    $this->assertSame('Pérez', $list[0]['last_name']);
  }

  public function test_store_requires_coach_role(): void {
  $pdo = test_pdo();
  $GLOBALS['__TEST_BODY__'] = ['email'=>'p@p.com','password'=>'secret1234'];
  register_handler($pdo);
  $token = jwt_make(['sub'=>1,'role'=>'player']);
  $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
  $GLOBALS['__TEST_BODY__'] = [
    'first_name'=>'Ana','last_name'=>'Ruiz','birth_date'=>'2011-02-02'
  ];
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('FORBIDDEN_ROLE');
    players_store($pdo);
  }
}
