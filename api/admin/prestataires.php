<?php
require_once '../config/database.php';
require_once '../utils/log.php';

$method = $_SERVER['REQUEST_METHOD'];

//  récupérer prestataires
if ($method === 'GET') {

    $stmt = $pdo->query("
        SELECT id_utilisateur, email, est_actif, created_at
        FROM utilisateur
        WHERE type_utilisateur = 'prestataire'
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}


// valider prestataire
if ($method === 'PATCH') {

    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $pdo->prepare("
        UPDATE utilisateur 
        SET est_actif = ?
        WHERE id_utilisateur = ?
    ");

    $nouveauStatut = $data['est_actif'] == 1 ? 'valide' : 'suspendu';
    $pdo->prepare("UPDATE prestataire SET statut = ?, date_validation = NOW() WHERE id_prestataire = ?")
        ->execute([$nouveauStatut, $data['id_utilisateur']]);

    $stmt->execute([
        $data['est_actif'],
        $data['id_utilisateur']
    ]);

    addLog($pdo, $data['id_utilisateur'], 'validation_prestataire');

    echo json_encode(['success' => true]);
}