<?php
require_once '../db_connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id_pres = $_GET['id_prestataire'] ?? null;
    if (!$id_pres) {
        http_response_code(400);
        echo json_encode(["error" => "ID manquant"]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM disponibilites WHERE id_prestataire = ? ORDER BY date_debut ASC");
    $stmt->execute([$id_pres]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $debut = strtotime($data['debut']);
    $fin = strtotime($data['fin']);
    $now = time();

    if ($debut < $now) {
        http_response_code(400);
        echo json_encode(["error" => "Date passée"]);
        exit;
    }

    if ($fin <= $debut) {
        http_response_code(400);
        echo json_encode(["error" => "Ordre chronologique invalide"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')");
    $stmt->execute([$data['id_p'], $data['debut'], $data['fin']]);
    echo json_encode(["status" => "success"]);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_disponibilite = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "deleted"]);
    }
}
