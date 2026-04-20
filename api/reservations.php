<?php

require_once '../db_connect.php';
require_once 'middleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if ($method == 'POST') {
    verifier_token();
    $data = json_decode(file_get_contents('php://input'), true);

    $id_senior = $data['id_senior'] ?? null;
    $id_prestataire = $data['id_prestataire'] ?? null;
    $date_reservation = $data['date_reservation'] ?? null;
    $description = $data['description'] ?? '';

    if (!$id_senior || !$id_prestataire || !$date_reservation) {
        http_response_code(400);
        echo json_encode(['message' => 'Champs obligatoires']);
        exit;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut)
            VALUES (?, ?, ?, ?, 'en_attente')
        ");
        $stmt->execute([$id_senior, $id_prestataire, $date_reservation, $description]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'id' => $db->lastInsertId()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Erreur serveur']);
    }

    exit;
}

if ($method == 'GET') {
    verifier_token();

    preg_match('/\/api\/reservations\/senior\/(\d+)/', $uri, $matches);
    $id_senior = $matches[1] ?? null;

    if (!$id_senior) {
        http_response_code(400);
        echo json_encode(['message' => 'ID manquant']);
        exit;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT r.id_reservation, r.date_reservation, r.statut, r.description, r.created_at,
                   p.nom AS pres_nom, p.prenom AS pres_prenom, p.categorie
            FROM reservation r
            JOIN prestataire p ON p.id_prestataire = r.id_prestataire
            WHERE r.id_senior = ?
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([$id_senior]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Erreur serveur']);
    }

    exit;
}

http_response_code(405);
echo json_encode(['message' => 'Methode non autorisee']);