<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT logs.*, utilisateur.email
    FROM logs
    LEFT JOIN utilisateur ON utilisateur.id_utilisateur = logs.utilisateur_id
    ORDER BY date_action DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));