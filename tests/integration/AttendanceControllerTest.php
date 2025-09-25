<?php
// attendanceControllerTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/v1/attendanceController.php';
require_once __DIR__ . '/../../src/controllers/v1/playerController.php';
require_once __DIR__ . '/../../src/controllers/v1/authController.php';

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
    seed_player_positions($pdo);

    $academyId = seed_academy($pdo, 'Academia X');
    $catId     = seed_category($pdo, 'Sub-14', 2011);
    $teamId    = seed_team($pdo, $academyId, 'Equipo A', $catId);

    $sessionId = seed_session($pdo, $teamId, 'training', '2025-09-01');

    $personId  = seed_person($pdo, [
      'first_name' => 'Leo',
      'last_name'  => 'García',
      'birth_date' => '2011-03-03',
    ]);
    seed_player($pdo, $personId);

    return ['session_id' => $sessionId, 'player_id' => $personId];
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
    $this->assertSame(100, (int)$sum['percent']);
  }
}
