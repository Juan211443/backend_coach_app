<?php
use PHPUnit\Framework\TestCase;
use App\Services\TokenService;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../src/Services/TokenService.php';

final class tokenServicesTest extends TestCase
{
    private PDO $pdo;
    private TokenService $svc;

    protected function setUp(): void {
        $this->pdo = test_pdo();
        test_reset_db();
        $this->svc = new TokenService($this->pdo);

        $hash = password_hash('x', PASSWORD_BCRYPT);
        $st = $this->pdo->prepare("INSERT INTO user (email,password_hash,role) VALUES (?,?,?)");
        $st->execute(['u@test.com',$hash,'player']);
    }

    public function test_make_and_verify_acces(): void {
        $jwt = $this->svc->makeAccessToken(['user_id'=>1,'role'=>'player']);
        $claims = $this->svc->verifyAccessToken($jwt);
        $this->assertSame('player', $claims['role']);
        $this->assertSame('1', $claims['sub']);
    }

    public function test_issue_validate_rotate_refresh(): void {
        $plain = $this->svc->issueRefreshToken(1);

        $row = $this->svc->findValidRefreshByPlain($plain);
        $this->assertSame(1, (int)$row['user_id']);

        $newPlain = $this->svc->rotateRefreshToken($row);
        $this->assertNotSame($plain, $newPlain);

        $this->expectException(\Exception::class);
        $this->svc->findValidRefreshByPlain($plain);
    }
}