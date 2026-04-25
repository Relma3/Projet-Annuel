<?php

// Charger .env 
if (file_exists(__DIR__ . '/.env.php')) {
    require_once __DIR__ . '/.env.php';
}

// Détection environnement
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost');

$dbname = 'silverhappy';

if ($isLocal) {
    //  LOCAL (MAMP)
    $host = 'localhost';
    $db_user = 'root';
    $db_pass = 'root';
} else {
    //  SERVEUR (Docker)
    $host = 'silverhappy-db-1'; //  nom du container MySQL
    $db_user = getenv('DB_USER') ?: 'sh_user';
    $db_pass = getenv('DB_PASS') ?: '';
}

// Connexion PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

// Fonction globale 
if (!function_exists('getDB')) {
    function getDB(): PDO {
        global $pdo;
        return $pdo;
    }
}
?>