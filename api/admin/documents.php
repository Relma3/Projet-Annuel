<?php
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $stmt = $pdo->query("
        SELECT d.*, u.email
        FROM documents_presta d
        JOIN utilisateur u ON d.id_prestataire = u.id_utilisateur
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'PATCH') {

    $data = json_decode(file_get_contents("php://input"), true);

    // 1. update statut
    $stmt = $pdo->prepare("
        UPDATE documents_presta
        SET statut = ?
        WHERE id_document = ?
    ");
    $stmt->execute([$data['statut'], $data['id_document']]);

    // 2. récupérer prestataire
    $stmt = $pdo->prepare("
        SELECT id_prestataire FROM documents_presta WHERE id_document = ?
    ");
    $stmt->execute([$data['id_document']]);
    $id_prestataire = $stmt->fetchColumn();

    // 3. vérifier si TOUS les docs sont validés
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM documents_presta
        WHERE id_prestataire = ? AND statut != 'valide'
    ");
    $stmt->execute([$id_prestataire]);

    if ($stmt->fetchColumn() == 0) {

        // 4. activer prestataire
        $pdo->prepare("
            UPDATE utilisateur 
            SET est_actif = 1 
            WHERE id_utilisateur = ?
        ")->execute([$id_prestataire]);
    }

    echo json_encode(['success' => true]);
    exit;
}