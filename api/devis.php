<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware.php';

function creer_devis() {
    $data = verifier_prestataire();
    $pdo = getDB();
    $body = json_decode(file_get_contents('php://input'), true);

    if (empty($body['id_senior']) || empty($body['montant']) || empty($body['description'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Champs obligatoires']);
        return;
    }

    if (!is_numeric($body['montant']) || $body['montant'] <= 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Montant invalide']);
        return;
    }

    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE id_utilisateur = ? AND type_utilisateur = 'senior'");
    $check->execute([$body['id_senior']]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['message' => 'Senior introuvable']);
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO devis (id_prestataire, id_senior, montant, description, statut) VALUES (?, ?, ?, ?, 'en_attente')");
    $stmt->execute([
        $data['id_utilisateur'],
        $body['id_senior'],
        $body['montant'],
        $body['description']
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Devis cree',
        'id' => $pdo->lastInsertId()
    ]);
}

function mes_devis_senior() {
    $data = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT d.id, d.montant, d.description, d.statut, d.created_at,
               p.nom AS pres_nom, p.prenom AS pres_prenom, p.categorie
        FROM devis d
        JOIN prestataire p ON p.id_prestataire = d.id_prestataire
        WHERE d.id_senior = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$data['id_utilisateur']]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function mes_devis_prestataire() {
    $data = verifier_prestataire();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT d.id, d.montant, d.description, d.statut, d.created_at,
               s.nom AS senior_nom, s.prenom AS senior_prenom
        FROM devis d
        JOIN senior s ON s.id_senior = d.id_senior
        WHERE d.id_prestataire = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$data['id_utilisateur']]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function accepter_devis($id) {
    $data = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("UPDATE devis SET statut = 'accepte' WHERE id = ? AND id_senior = ? AND statut = 'en_attente'");
    $stmt->execute([$id, $data['id_utilisateur']]);

    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Devis introuvable']);
        return;
    }

    echo json_encode(['message' => 'Devis accepte']);
}

function refuser_devis($id) {
    $data = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("UPDATE devis SET statut = 'refuse' WHERE id = ? AND id_senior = ? AND statut = 'en_attente'");
    $stmt->execute([$id, $data['id_utilisateur']]);

    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Devis introuvable']);
        return;
    }

    echo json_encode(['message' => 'Devis refuse']);
}

function admin_lister_devis() {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT d.id, d.montant, d.description, d.statut, d.created_at,
               s.nom AS senior_nom, s.prenom AS senior_prenom,
               p.nom AS pres_nom, p.prenom AS pres_prenom
        FROM devis d
        JOIN senior s ON s.id_senior = d.id_senior
        JOIN prestataire p ON p.id_prestataire = d.id_prestataire
        ORDER BY d.created_at DESC
        LIMIT 200
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}