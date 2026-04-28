<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/middleware.php';

header('Content-Type: application/json');

// Vérifie que le senior est connecté
session_start();
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Récupère le body JSON
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['player_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Player ID manquant']);
    exit();
}

$player_id = trim($data['player_id']);
$id_senior = $_SESSION['id'];

try {
    $stmt = $pdo->prepare("
        UPDATE senior 
        SET onesignal_player_id = ? 
        WHERE id_senior = ?
    ");
    $stmt->execute([$player_id, $id_senior]);

    echo json_encode(['success' => true, 'message' => 'Player ID enregistré']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données']);
}