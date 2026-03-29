<?php
require_once __DIR__ . '/../../db_connect.php';

function getDB(): PDO {
    global $pdo;
    return $pdo;
}
?>