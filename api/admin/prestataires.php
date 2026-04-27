<?php
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id_utilisateur, email, est_valide FROM utilisateur WHERE type_utilisateur = 'prestataire'");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'PATCH') {
    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $pdo->prepare("UPDATE utilisateur SET est_valide = ? WHERE id_utilisateur = ?");
    $stmt->execute([$data['est_valide'], $data['id_utilisateur']]);

    echo json_encode(["success" => true]);
}