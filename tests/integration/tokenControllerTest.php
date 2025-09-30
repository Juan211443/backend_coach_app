<?php
use PHPUnit\Framework\TestCase;
use App\Services\TokenService;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../src/controllers/v1/tokenController.php';
require_once __DIR__ . '/../../src/Services/TokenService.php';

final class TokenControllerTest extends TestCase
{
    protected function setUp(): void {
        test_reset_db();
        unset($GLOBALS['__TEST_RESPONSE__']);
    }

    public function test_refresh_flow(): void {
        $pdo = test_pdo();

        $hash = password_hash('secret', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO user (email,password_hash,role) VALUES (?,?,?)")
            ->execute(['j@t.com',$hash,'player']);

        $svc = new TokenService($pdo);
        $rt = $svc->issueRefreshToken(1);
        $_COOKIE['rt'] = $rt;

        ob_start();
        refresh_handler($pdo);
        $out = ob_get_clean();
        $data = json_decode($out, true);

        $this->assertArrayHasKey('access_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertIsInt($data['expires_in']);
    }
}
