<?php
// AttendanceControllerTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/attendanceController.php';
require_once __DIR__ . '/../../src/controllers/playerController.php';
require_once __DIR__ . '/../../src/controllers/authController.php';

final class AttendanceControllerTest extends TestCase {

  protected function setUp(): void {
    test_reset_db();
    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $_SERVER['HTTP_AUTHORIZATION'] = '';
  }

  private function coach(PDO $pdo): void {
    $GLOBALS['__TEST_BODY__'] = ['email'=>'coach@test.com','password'=>'secret1234'];
    register_handler($pdo);
    $pdo->exec("UPDATE user SET role='coach' WHERE email='coach@test.com'");
    $token = jwt_make(['sub'=>1,'role'=>'coach']);
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
  }

  private function seedBasicData(PDO $pdo): array {
    $pdo->exec("INSERT INTO sports_academy(name) VALUES('Academia X');");
    $pdo->exec("INSERT INTO category(name,year) VALUES('Sub-14', 2011);");
    $pdo->exec("INSERT INTO team(sports_academy_id,name,category_id) VALUES(1,'Equipo A',1);");
    $pdo->exec("INSERT INTO session(team_id,type,date) VALUES(1,'training','2025-09-01');");

    $pdo->exec("INSERT INTO person(first_name,last_name,birth_date) VALUES('Leo','García','2011-03-03');");
    $pdo->exec("INSERT INTO player(person_id) VALUES(1);");

    return ['session_id'=>1,'player_id'=>1];
  }

  public function test_mark_and_summary(): void {
    $pdo = test_pdo();
    $this->coach($pdo);
    $ids = $this->seedBasicData($pdo);

    $GLOBALS['__TEST_BODY__'] = [
      'session_id'=>$ids['session_id'],
      'player_id' =>$ids['player_id'],
      'status'    =>'present',
      'checkin_at'=>'2025-09-01 10:00:00',
      'remarks'   =>'A tiempo'
    ];
    attendance_mark($pdo);
    $this->assertSame(201, $GLOBALS['__TEST_RESPONSE__']['code']);

    unset($GLOBALS['__TEST_RESPONSE__'], $_GET);
    $_GET = ['year'=>2025,'month'=>9];
    attendance_monthly($pdo, $ids['player_id']);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $sum = $GLOBALS['__TEST_RESPONSE__']['data'];
    $this->assertSame(1, $sum['presents']);
    $this->assertSame(1, $sum['total']);
    $this->assertSame(100, $sum['percent']);
  }
}
