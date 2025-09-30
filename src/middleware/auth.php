<?php
// middleware/auth.php
declare(strict_types=1);

use CoachApp\ApiPhp\services\TokenService;

function require_auth(PDO $pdo): array {
    $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($hdr, 'Bearer ')) {
        http_response_code(401);
        exit(json_encode(['error'=>'missing_bearer']));
    }
    $jwt = substr($hdr, 7);
    try {
        $svc = new TokenService($pdo);
        $claims = $svc->verifyAccessToken($jwt);
        return $claims;
    } catch (Throwable $e) {
        http_response_code(401);
        exit(json_encode(['error'=>'invalid_token']));
    }
}
