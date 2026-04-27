<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function lister_rdv() {
    $payload = verifier_token();
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT r.*, u.email as email_medecin, p.nom, p.prenom, p.specialite
        FROM rdv_medical r
        JOIN utilisateur u ON r.id_prestataire = u.id_utilisateur
        JOIN prestataire p ON r.id_prestataire = p.id_prestataire
        WHERE r.id_senior = ?
        ORDER BY r.date_rdv ASC
    ");
    $stmt->execute([$payload["id_utilisateur"]]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function creer_rdv() {
    $payload = verifier_token();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["id_prestataire"]) || empty($data["date_rdv"])) {
        http_response_code(400);
        echo json_encode(["message" => "Données manquantes"]);
        return;
    }

    $pdo->prepare("
        INSERT INTO rdv_medical (id_senior, id_prestataire, date_rdv, statut)
        VALUES (?, ?, ?, 'planifie')
    ")->execute([
        $payload["id_utilisateur"],
        $data["id_prestataire"],
        $data["date_rdv"]
    ]);

    echo json_encode(["message" => "RDV créé avec succès"]);
}

function annuler_rdv($id_rdv) {
    $payload = verifier_token();
    $pdo = getDB();

    // Vérifier que le RDV appartient bien à ce senior
    $stmt = $pdo->prepare("SELECT id_senior FROM rdv_medical WHERE id_rdv = ?");
    $stmt->execute([$id_rdv]);
    $rdv = $stmt->fetch();

    if (!$rdv || $rdv["id_senior"] != $payload["id_utilisateur"]) {
        http_response_code(403);
        echo json_encode(["message" => "Accès refusé"]);
        return;
    }

    $pdo->prepare("UPDATE rdv_medical SET statut = 'annule' WHERE id_rdv = ?")
        ->execute([$id_rdv]);

    echo json_encode(["message" => "RDV annulé"]);
}

function lister_medecins() {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT p.id_prestataire, p.nom, p.prenom, p.specialite, u.email
        FROM prestataire p
        JOIN utilisateur u ON p.id_prestataire = u.id_utilisateur
        WHERE p.statut = 'valide' AND p.est_medecin = 1
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}


//annuler un evenement 
function annuler_inscription_evenement($id_evenement) {
    $payload = verifier_token();
    $pdo = getDB();
    $pdo->prepare("DELETE FROM inscription_evenement WHERE id_senior = ? AND id_evenement = ?")
        ->execute([$payload["id_utilisateur"], $id_evenement]);
    echo json_encode(["message" => "Inscription annulée"]);
}