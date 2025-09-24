<?php
// PlayerControllerTest.php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../tests/bootstrap.php';
require_once __DIR__ . '/../../src/controllers/v1/playerController.php';
require_once __DIR__ . '/../../src/controllers/v1/authController.php';

final class PlayerControllerTest extends TestCase {

  protected function setUp(): void {
    test_reset_db();
    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $_SERVER['HTTP_AUTHORIZATION'] = '';
    $_GET = [];
  }

  private function makeCoachToken(PDO $pdo): string {
    $GLOBALS['__TEST_BODY__'] = ['email'=>'c@c.com','password'=>'secret1234'];
    register_handler($pdo);
    $pdo->exec("UPDATE user SET role='coach' WHERE email='c@c.com'");
    return jwt_make(['sub'=>1,'role'=>'coach']);
  }

  private function seedBasicPlayer(PDO $pdo, array $overrides=[]): int {
    $b = array_merge([
      'first_name'=>'Juan',
      'last_name' =>'Pérez',
      'birth_date'=>'2010-01-05',
      'jersey_number'=>9,
      'preferred_foot'=>'Right'
    ], $overrides);

    unset($GLOBALS['__TEST_RESPONSE__'], $GLOBALS['__TEST_BODY__']);
    $GLOBALS['__TEST_BODY__'] = $b;
    players_store($pdo);
    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    return (int)$GLOBALS['__TEST_RESPONSE__']['data']['person_id'];
  }

  public function test_store_and_list_players(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $this->seedBasicPlayer($pdo);

    unset($GLOBALS['__TEST_RESPONSE__']);
    $_GET = [];
    players_index($pdo);

    $this->assertSame(200, $GLOBALS['__TEST_RESPONSE__']['code']);
    $list = $GLOBALS['__TEST_RESPONSE__']['data']['data'];
    $this->assertCount(1, $list);
    $this->assertSame('Pérez', $list[0]['last_name']);
  }

  public function test_filter_by_category_year_and_name(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $academyId = seed_academy($pdo);
    $cat2014   = seed_category($pdo, '2014', 2014);
    $catSub15  = seed_category($pdo, 'Sub-15', null);
    $teamA     = seed_team($pdo, $academyId, 'Leones A', $cat2014);
      
    $this->seedBasicPlayer($pdo, ['current_category_id'=>$cat2014, 'current_team_id'=>$teamA, 'last_name'=>'Año2014']);
    $this->seedBasicPlayer($pdo, ['current_category_id'=>$catSub15, 'last_name'=>'Sub15']);

    $_GET = ['category' => '2014'];
    players_index($pdo);
    $rows = $GLOBALS['__TEST_RESPONSE__']['data']['data'];
    $this->assertCount(1, $rows);
    $this->assertSame('Año2014', $rows[0]['last_name']);

    $_GET = ['category' => 'Sub-15'];
    players_index($pdo);
    $rows = $GLOBALS['__TEST_RESPONSE__']['data']['data'];
    $this->assertCount(1, $rows);
    $this->assertSame('Sub15', $rows[0]['last_name']);
  }

  public function test_filter_by_team(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    $academyId = seed_academy($pdo);
    $cat2014   = seed_category($pdo, '2014', 2014);
    $teamA     = seed_team($pdo, $academyId, 'Leones A', $cat2014);
    $teamB     = seed_team($pdo, $academyId, 'Tigres',   $cat2014);

    $this->seedBasicPlayer($pdo, [
      'current_category_id'=>$cat2014,
      'current_team_id'=>$teamA,
      'last_name'=>'Leones'
    ]);
    $this->seedBasicPlayer($pdo, [
      'current_category_id'=>$cat2014,
      'current_team_id'=>$teamB,
      'last_name'=>'Tigres'
    ]);

    $_GET = ['team'=>'Leones'];
    players_index($pdo);
    $rows = $GLOBALS['__TEST_RESPONSE__']['data']['data'];
    $this->assertCount(1, $rows);
    $this->assertSame('Leones', $rows[0]['last_name']);
  }

  public function test_pagination(): void {
    $pdo = test_pdo();
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer ".$this->makeCoachToken($pdo);

    for ($i=0; $i<5; $i++) {
      $this->seedBasicPlayer($pdo, ['last_name'=>"P$i"]);
    }

    $_GET = ['limit'=>2, 'offset'=>0];
    players_index($pdo);
    $this->assertCount(2, $GLOBALS['__TEST_RESPONSE__']['data']['data']);

    $_GET = ['limit'=>2, 'offset'=>2];
    players_index($pdo);
    $this->assertCount(2, $GLOBALS['__TEST_RESPONSE__']['data']['data']);

    $_GET = ['limit'=>2, 'offset'=>4];
    players_index($pdo);
    $this->assertCount(1, $GLOBALS['__TEST_RESPONSE__']['data']['data']);
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
