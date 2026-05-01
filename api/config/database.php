<?php
require_once __DIR__ . '/../../db_connect.php';

if (!function_exists('getDB')) {
    function getDB(): PDO {
        global $pdo;
        return $pdo;
    }
}