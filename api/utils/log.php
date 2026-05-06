<?php

function addLog($pdo, $userId, $action) {

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $pdo->prepare("
        INSERT INTO logs (utilisateur_id, action, ip)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$userId, $action, $ip]);
}