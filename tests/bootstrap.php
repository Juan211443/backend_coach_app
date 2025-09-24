<?php
// bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.test');
$dotenv->safeLoad();

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/utils.php';
require_once __DIR__ . '/../src/jwt.php';

function test_pdo(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4',
    envv('DB_HOST','127.0.0.1'),
    envv('DB_PORT','3306')
  );
  $rootPdo = new PDO($dsn, envv('DB_USER','root'), envv('DB_PASS',''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  $dbName = envv('DB_NAME','coach_app_test');
  $rootPdo->exec("DROP DATABASE IF EXISTS `$dbName`;");
  $rootPdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

  $pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
      envv('DB_HOST','127.0.0.1'), envv('DB_PORT','3306'), $dbName),
    envv('DB_USER','root'), envv('DB_PASS',''),
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );

  $schema = file_get_contents(__DIR__ . '/../db/init/001_schema.test.sql');
  $pdo->exec($schema);

  return $pdo;
}

function test_reset_db(): void {
  $pdo = test_pdo();
  $schema = file_get_contents(__DIR__ . '/../db/init/001_schema.test.sql');
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
  foreach (['attendance','player_metric','session','player','team','category',
            'sports_academy','player_position','person','user'] as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `$t`;");
  }
  $pdo->exec($schema);
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
}


if (!isset($_SERVER['REQUEST_METHOD'])) {
  $_SERVER['REQUEST_METHOD'] = 'GET';
}

putenv('APP_ENV=test');
putenv('APP_BASE_URL=http://localhost:8000');

function seed_academy(PDO $pdo, string $name = 'Academia Demo'): int {
  $st = $pdo->prepare("INSERT INTO sports_academy(name) VALUES (?)");
  $st->execute([$name]);
  return (int)$pdo->lastInsertId();
}

function seed_category(PDO $pdo, string $name, ?int $year): int {
  $st = $pdo->prepare("INSERT INTO category(name,year) VALUES (?,?)");
  $st->execute([$name, $year]);
  return (int)$pdo->lastInsertId();
}

function seed_team(PDO $pdo, int $academyId, string $name, int $categoryId): int {
  $st = $pdo->prepare("INSERT INTO team(sports_academy_id,name,category_id) VALUES (?,?,?)");
  $st->execute([$academyId, $name, $categoryId]);
  return (int)$pdo->lastInsertId();
}