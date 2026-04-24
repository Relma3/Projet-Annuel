<?php
require_once '../db_connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$id_pres = $_GET['id_prestataire'] ?? null;

if ($method === 'GET' && $id_pres) {
    $stmt = $pdo->prepare("SELECT * FROM disponibilites WHERE id_prestataire = ? ORDER BY date_debut ASC");
    $stmt->execute([$id_pres]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} 

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')");
    $stmt->execute([$data['id_p'], $data['debut'], $data['fin']]);
    echo json_encode(["status" => "success"]);
}

if ($method === 'DELETE') {
    $id_dispo = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_disponibilite = ?");
    $stmt->execute([$id_dispo]);
    echo json_encode(["status" => "deleted"]);
}
