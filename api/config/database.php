<?php
require_once __DIR__ . '/../../db_connect.php';

function getDB() {
    global $pdo;
    return $pdo;
}