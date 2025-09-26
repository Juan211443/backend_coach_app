<?php
// phinx.php
require __DIR__ . '/vendor/autoload.php';

$argv  = $_SERVER['argv'] ?? [];
$cliEnv = null;
for ($i = 0; $i < count($argv); $i++) {
  if (($argv[$i] === '-e' || $argv[$i] === '--environment') && isset($argv[$i+1])) {
    $cliEnv = $argv[$i+1];
    break;
  }
}

$env = $cliEnv ?: (getenv('APP_ENV') ?: 'dev');
$envFile = match ($env) {
  'prod' => '.env.prod',
  'test' => '.env.test',
  default => '.env.dev',
};
Dotenv\Dotenv::createImmutable(__DIR__, $envFile)->safeLoad();

return [
  'paths' => [
    'migrations' => 'db/migrations',
    'seeds'      => 'db/seeds',
  ],
  'environments' => [
    'default_migration_table' => 'phinxlog',
    'default_environment'     => $env,

    'dev' => [
      'adapter' => 'mysql',
      'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
      'name'    => 'coach_app_dev',
      'user'    => $_ENV['DB_USER'] ?? 'root',
      'pass'    => $_ENV['DB_PASS'] ?? '',
      'port'    => (int)($_ENV['DB_PORT'] ?? 3306),
      'charset' => 'utf8mb4',
      'collation'=> 'utf8mb4_general_ci',
    ],
    'test' => [
      'adapter' => 'mysql',
      'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
      'name'    => 'coach_app_test',
      'user'    => $_ENV['DB_USER'] ?? 'root',
      'pass'    => $_ENV['DB_PASS'] ?? '',
      'port'    => (int)($_ENV['DB_PORT'] ?? 3306),
      'charset' => 'utf8mb4',
      'collation'=> 'utf8mb4_general_ci',
    ],
    'prod' => [
      'adapter' => 'mysql',
      'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
      'name'    => 'coach_app_prod',
      'user'    => $_ENV['DB_USER'] ?? 'root',
      'pass'    => $_ENV['DB_PASS'] ?? '',
      'port'    => (int)($_ENV['DB_PORT'] ?? 3306),
      'charset' => 'utf8mb4',
      'collation'=> 'utf8mb4_general_ci',
    ],
  ],
  'version_order' => 'creation',
];