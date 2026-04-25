<?php
require_once '../db_connect.php';
header("Content-Type: application/json");
 
$method = $_SERVER['REQUEST_METHOD'];
 
if ($method === 'GET') {
    $id_pres = $_GET['id_prestataire'] ?? null;
 
    if (!$id_pres || !is_numeric($id_pres)) {
        http_response_code(400);
        echo json_encode(["error" => "ID manquant ou invalide"]);
        exit;
    }
 
    $stmt = $pdo->prepare("
        SELECT id_disponibilite, date_debut, date_fin, type, note
        FROM disponibilites
        WHERE id_prestataire = ?
        ORDER BY date_debut ASC
    ");
    $stmt->execute([(int)$id_pres]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
 
    if (empty($data['id_p']) || empty($data['debut']) || empty($data['fin'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs manquants (id_p, debut, fin)"]);
        exit;
    }
 
    if (!is_numeric($data['id_p'])) {
        http_response_code(400);
        echo json_encode(["error" => "id_p invalide"]);
        exit;
    }
 
    $debut = strtotime($data['debut']);
    $fin   = strtotime($data['fin']);
 
    if ($debut === false || $fin === false) {
        http_response_code(400);
        echo json_encode(["error" => "Format de date invalide"]);
        exit;
    }
 
    if ($debut < time()) {
        http_response_code(400);
        echo json_encode(["error" => "La date de début est dans le passé"]);
        exit;
    }
 
    if ($fin <= $debut) {
        http_response_code(400);
        echo json_encode(["error" => "La date de fin doit être après le début"]);
        exit;
    }
 
    if (($fin - $debut) > 86400) {
        http_response_code(400);
        echo json_encode(["error" => "Créneau trop long (max 24h)"]);
        exit;
    }
 
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM disponibilites
        WHERE id_prestataire = ?
          AND date_debut < ?
          AND date_fin   > ?
    ");
    $stmtCheck->execute([
        (int)$data['id_p'],
        $data['fin'],
        $data['debut']
    ]);
 
    if ($stmtCheck->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Ce créneau chevauche une disponibilité existante"]);
        exit;
    }
 
    $stmt = $pdo->prepare("
        INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type)
        VALUES (?, ?, ?, 'libre')
    ");
    $stmt->execute([(int)$data['id_p'], $data['debut'], $data['fin']]);
 
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "id"     => $pdo->lastInsertId()
    ]);
    exit;
}
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
 
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "ID manquant ou invalide"]);
        exit;
    }
 
    // Empêcher la suppression d'un créneau déjà réservé
    $stmtCheck = $pdo->prepare("
        SELECT type FROM disponibilites WHERE id_disponibilite = ?
    ");
    $stmtCheck->execute([(int)$id]);
    $dispo = $stmtCheck->fetch(PDO::FETCH_ASSOC);
 
    if (!$dispo) {
        http_response_code(404);
        echo json_encode(["error" => "Créneau introuvable"]);
        exit;
    }
 
    if ($dispo['type'] === 'reserve') {
        http_response_code(403);
        echo json_encode(["error" => "Impossible de supprimer un créneau déjà réservé"]);
        exit;
    }
 
    $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_disponibilite = ?");
    $stmt->execute([(int)$id]);
 
    echo json_encode(["status" => "deleted"]);
    exit;
}
http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
