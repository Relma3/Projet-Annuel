<?php
$host    = "silverhappy-db-1";
$dbname  = "silver_happy";
$db_user = "sh_user";
$db_pass = "ChoisisUnMotDePasseFort";

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
if (!function_exists("getDB")) {
    function getDB(): PDO {
        global $pdo;
        return $pdo;
    }
}
