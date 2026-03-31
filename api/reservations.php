<?php

require_once '../db_connect.php';
require_once 'middleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if ($method == 'POST') {
    verifier_token();
    $data = json_decode(file_get_contents('php://input'), true);

    $id_senior = isset($data['id_senior']) ? $data['id_senior'] : null;
    $id_prestataire = isset($data['id_prestataire']) ? $data['id_prestataire'] : null;
    $date_reservation = isset($data['date_reservation']) ? $data['date_reservation'] : null;
    $description = isset($data['description']) ? $data['description'] : '';

    if (!$id_senior || !$id_prestataire || !$date_reservation) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Champs manquants']);
        exit;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut, date_creation) VALUES (?, ?, ?, ?, 'en_attente', NOW())");
        $stmt->execute([$id_senior, $id_prestataire, $date_reservation, $description]);

        http_response_code(201);
        echo json_encode(['succes' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
    }

    exit;
}

if ($method == 'GET') {
    verifier_token();

    preg_match('/\/api\/reservations\/senior\/(\d+)/', $uri, $matches);
    $id_senior = isset($matches[1]) ? $matches[1] : null;

    if (!$id_senior) {
        http_response_code(400);
        echo json_encode(['erreur' => 'ID manquant']);
        exit;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT r.*, u.prenom, u.nom FROM reservation r JOIN utilisateur u ON r.id_prestataire = u.id_utilisateur WHERE r.id_senior = ? ORDER BY r.date_reservation DESC");
        $stmt->execute([$id_senior]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
    }

    exit;
}