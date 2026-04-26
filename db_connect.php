<?php
// Mmina 
error_log("DB host=$host dbname=$dbname user=$db_user pass=" . (empty($db_pass) ? 'VIDE' : 'OK'));
if (file_exists(__DIR__ . '/.env.php')) {
    require_once __DIR__ . '/.env.php';
}

$host    = getenv('DB_HOST') ?: 'localhost';
$dbname  = getenv('DB_NAME') ?: 'silver_happy';
$db_user = getenv('DB_USER') ?: 'sh_user';
$db_pass = getenv('DB_PASS') ?: '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if (!function_exists('getDB')) {
    function getDB(): PDO {
        global $pdo;
        return $pdo;
    }
}
?>
