<?php
// tokenService.php
declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class TokenService
{
    public function __construct(private PDO $pdo) {}

    private function env(string $k, string $def=''): string {
        return $_ENV[$k] ?? getenv($k) ?: $def;
    }

    public function makeAccessToken(array $user): string {
        $now = time();
        $ttl = (int)$this->env('JWT_ACCESS_TTL', '900');
        $payload = [
            'iss' => $this->env('JWT_ISS', 'http://localhost'),
            'aud' => $this->env('JWT_AUD', 'app'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'sub' => (string)$user['user_id'],
            'role'=> $user['role'] ?? 'player',
        ];
        return JWT::encode($payload, $this->env('JWT_SECRET'), 'HS256');
    }

    public function verifyAccessToken(string $jwt): array {
        $decoded = JWT::decode($jwt, new Key($this->env('JWT_SECRET'), 'HS256'));
        if (($decoded->iss ?? null) !== $this->env('JWT_ISS')) throw new \Exception('bad iss');
        if (($decoded->aud ?? null) !== $this->env('JWT_AUD')) throw new \Exception('bad aud');
        return (array)$decoded;
    }

    public function issueRefreshToken(int $userId): string {
        $bytes = random_bytes(32);
        $token = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
        
        $hash  = password_hash($token, PASSWORD_DEFAULT);
        $lookup= rtrim(strtr(base64_encode(hash('sha256', $token, true)), '+/', '-_'), '=');
        
        $ttl = (int)$this->env('JWT_REFRESH_TTL', '1209600');
        $exp = (new \DateTimeImmutable())->setTimestamp(time() + $ttl)->format('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare('INSERT INTO refresh_tokens (user_id, token_hash, lookup_hash, expires_at) VALUES (?,?,?,?)');
        $stmt->execute([$userId, $hash, $lookup, $exp]);

        return $token;
    }

    public function validateRefreshToken(int $userId, string $token): array {
        $stmt = $this->pdo->prepare('SELECT * FROM refresh_tokens WHERE user_id=? AND revoked_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (password_verify($token, $row['token_hash'])) {
                return $row;
            }
        }
        throw new \Exception('invalid_or_expired_refresh');
    }

    public function rotateRefreshToken(array $row): string {
        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare('UPDATE refresh_tokens SET revoked_at=NOW() WHERE id=?');
            $upd->execute([$row['id']]);

            $new = $this->issueRefreshToken((int)$row['user_id']);
            $this->pdo->commit();
            return $new;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function revokeByToken(int $userId, string $token): void {
        $stmt = $this->pdo->prepare('SELECT * FROM refresh_tokens WHERE user_id=? AND revoked_at IS NULL');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (password_verify($token, $row['token_hash'])) {
                $upd = $this->pdo->prepare('UPDATE refresh_tokens SET revoked_at=NOW() WHERE id=?');
                $upd->execute([$row['id']]);
                return;
            }
        }
    }

    public function findValidRefreshByPlain(string $plain): array {
        $lookup = rtrim(strtr(base64_encode(hash('sha256', $plain, true)), '+/', '-_'), '=');

        $st = $this->pdo->prepare('SELECT * FROM refresh_tokens WHERE lookup_hash=? AND revoked_at IS NULL AND expires_at > NOW() LIMIT 1');
        $st->execute([$lookup]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new \Exception('invalid_or_expired_refresh');

        if (!password_verify($plain, $row['token_hash'])) {
            throw new \Exception('reused_or_tampered');
        }
        return $row;
    }

    public function revokeAllForUser(int $userId): void {
        $stmt = $this->pdo->prepare('UPDATE refresh_tokens SET revoked_at=NOW() WHERE user_id=? AND revoked_at IS NULL');
        $stmt->execute([$userId]);
    }
}
