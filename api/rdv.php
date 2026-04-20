<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware.php';

function mes_rdv_medicaux() {
    $data = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT r.id, r.date_rdv, r.statut, r.notes,
               u.email AS medecin_email,
               p.nom AS medecin_nom,
               p.prenom AS medecin_prenom,
               p.ville AS medecin_ville
        FROM rdv_medicaux r
        JOIN utilisateur u ON u.id_utilisateur = r.id_medecin
        JOIN prestataire p ON p.id_prestataire = r.id_medecin
        WHERE r.id_senior = ?
        ORDER BY r.date_rdv DESC
    ");
    $stmt->execute([$data['id_utilisateur']]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function prendre_rdv() {
    $data = verifier_senior();
    $pdo = getDB();
    $body = json_decode(file_get_contents('php://input'), true);

    if (empty($body['id_medecin']) || empty($body['date_rdv'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Champs obligatoires']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id_prestataire FROM prestataire WHERE id_prestataire = ? AND statut = 'valide'");
    $stmt->execute([$body['id_medecin']]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['message' => 'Medecin introuvable']);
        return;
    }

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM rdv_medicaux WHERE id_medecin = ? AND date_rdv = ? AND statut != 'annule'");
    $stmt2->execute([$body['id_medecin'], $body['date_rdv']]);

    if ($stmt2->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['message' => 'Creneau deja pris']);
        return;
    }

    $notes = $body['notes'] ?? '';

    $stmt3 = $pdo->prepare("INSERT INTO rdv_medicaux (id_senior, id_medecin, date_rdv, statut, notes) VALUES (?, ?, ?, 'en_attente', ?)");
    $stmt3->execute([$data['id_utilisateur'], $body['id_medecin'], $body['date_rdv'], $notes]);

    http_response_code(201);
    echo json_encode([
        'message' => 'RDV cree',
        'id' => $pdo->lastInsertId()
    ]);
}

function annuler_rdv($id) {
    $data = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("UPDATE rdv_medicaux SET statut = 'annule' WHERE id = ? AND id_senior = ?");
    $stmt->execute([$id, $data['id_utilisateur']]);

    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['message' => 'RDV introuvable']);
        return;
    }

    echo json_encode(['message' => 'RDV annule']);
}

function admin_lister_rdv_anonymises() {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT r.id, r.date_rdv, r.statut,
               SHA2(CONCAT('senior_', r.id_senior), 256) AS patient_anonymise,
               p.nom AS medecin_nom,
               p.prenom AS medecin_prenom,
               p.ville AS medecin_ville
        FROM rdv_medicaux r
        JOIN prestataire p ON p.id_prestataire = r.id_medecin
        ORDER BY r.date_rdv DESC
        LIMIT 500
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function admin_stats_rdv() {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT p.nom AS medecin_nom, p.prenom AS medecin_prenom,
               COUNT(r.id) AS total_rdv,
               SUM(r.statut = 'confirme') AS confirmes,
               SUM(r.statut = 'annule') AS annules,
               SUM(r.statut = 'en_attente') AS en_attente
        FROM rdv_medicaux r
        JOIN prestataire p ON p.id_prestataire = r.id_medecin
        GROUP BY r.id_medecin
        ORDER BY total_rdv DESC
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}