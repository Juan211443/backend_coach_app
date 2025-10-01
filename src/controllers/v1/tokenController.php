<?php
// tokenController.php
use CoachApp\ApiPhp\services\TokenService;

function refresh_handler(PDO $pdo): void {
    $rt = $_COOKIE['rt'] ?? null;
    if (!$rt) { http_response_code(401); echo json_encode(['error'=>'missing_refresh']); return; }

    $svc = new TokenService($pdo);

    try {
        $row = $svc->findValidRefreshByPlain($rt);
    } catch (\Exception $e) {
        if (isset($row['user_id'])) {
            $svc->revokeAllForUser((int)$row['user_id']);
        }
        http_response_code(401);
        echo json_encode(['error'=>'invalid_or_reused_refresh']);
        return;
    }

    $newRt = $svc->rotateRefreshToken($row);

    $st = $pdo->prepare('SELECT role FROM user WHERE user_id=?');
    $st->execute([(int)$row['user_id']]);
    $role = $st->fetchColumn() ?: 'player';

    $user = ['user_id' => (int)$row['user_id'], 'role' => $role];
    $access = $svc->makeAccessToken($user);

    $secure   = filter_var($_ENV['COOKIE_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    $sameSite = $_ENV['COOKIE_SAMESITE'] ?? 'Lax';
    setcookie('rt', $newRt, [
        'expires'  => time() + (int)($_ENV['JWT_REFRESH_TTL'] ?? 1209600),
        'path'     => '/api',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $sameSite,
    ]);

    echo json_encode([
        'access_token' => $access,
        'token_type'   => 'Bearer',
        'expires_in'   => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900),
    ]);
}
