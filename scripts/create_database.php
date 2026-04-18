<?php
$env = @file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($env === false) {
    echo "Could not read .env\n";
    exit(1);
}
$vars = [];
foreach ($env as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $v = trim($v, " \t\n\r\0\x0B\"'");
    $vars[$k] = $v;
}
$host = $vars['DB_HOST'] ?? '127.0.0.1';
$port = $vars['DB_PORT'] ?? '3306';
$user = $vars['DB_USERNAME'] ?? 'root';
$pass = $vars['DB_PASSWORD'] ?? '';
$dbName = $argv[1] ?? ($vars['DB_DATABASE'] . '_fresh');
$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Created database: {$dbName}\n";
    exit(0);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
