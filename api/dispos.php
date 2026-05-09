<?php
require_once '../db_connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id_pres = $_GET['id_prestataire'] ?? null;
    if (!$id_pres) {
        http_response_code(400);
        echo json_encode(["error" => "ID prestataire manquant"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT d.*, s.nom_service 
            FROM disponibilites d 
            LEFT JOIN services s ON d.id_service = s.id_service 
            WHERE d.id_prestataire = ? 
            ORDER BY d.date_debut ASC
        ");
        $stmt->execute([$id_pres]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Erreur SQL dispos: " . $e->getMessage());
        echo json_encode(["error" => "Erreur serveur"]);
    }
    exit;
} 

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $id_p = $data['id_p'] ?? null;
    $id_service = $data['id_service'] ?? null;
    $debut = $data['debut'] ?? null;
    $fin = $data['fin'] ?? null;

    if (!$id_p || !$id_service || !$debut || !$fin) {
        http_response_code(400);
        echo json_encode(["error" => "Tous les champs sont requis."]);
        exit;
    }

    $time_debut = strtotime($debut);
    $time_fin = strtotime($fin);
    $now = time();

    if ($time_debut < $now) {
        http_response_code(400);
        echo json_encode(["error" => "Impossible de créer une disponibilité dans le passé."]);
        exit;
    }

    if ($time_fin <= $time_debut) {
        http_response_code(400);
        echo json_encode(["error" => "La date de fin doit être après la date de début."]);
        exit;
    }

    if (($time_fin - $time_debut) > (12 * 3600)) {
        http_response_code(400);
        echo json_encode(["error" => "Un créneau ne peut pas dépasser 12 heures."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO disponibilites (id_prestataire, id_service, date_debut, date_fin, type) VALUES (?, ?, ?, ?, 'libre')");
        $stmt->execute([$id_p, $id_service, $debut, $fin]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Erreur SQL dispos: " . $e->getMessage());
        echo json_encode(["error" => "Erreur serveur"]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_disponibilite = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "deleted"]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("Erreur SQL dispos: " . $e->getMessage());
        echo json_encode(["error" => "Erreur serveur"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "ID de disponibilité manquant."]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
