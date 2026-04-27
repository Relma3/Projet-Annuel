<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function get_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT u.id_utilisateur, u.email, u.type_utilisateur, u.created_at,
               s.nom, s.prenom, s.telephone, s.adresse, s.date_naissance, s.tutoriel_vu
        FROM utilisateur u
        LEFT JOIN senior s ON s.id_senior = u.id_utilisateur
        WHERE u.id_utilisateur = ?
    ");
    $stmt->execute([$payload["id_utilisateur"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "Utilisateur non trouvé"]);
        return;
    }

    echo json_encode($user);
}

function modifier_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data["email"])) {
        $pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_utilisateur = ?")
            ->execute([$data["email"], $payload["id_utilisateur"]]);
    }

    $pdo->prepare("
        UPDATE senior 
        SET nom = COALESCE(?, nom),
            prenom = COALESCE(?, prenom),
            telephone = COALESCE(?, telephone),
            adresse = COALESCE(?, adresse),
            date_naissance = COALESCE(?, date_naissance)
        WHERE id_senior = ?
    ")->execute([
        $data["nom"] ?? null,
        $data["prenom"] ?? null,
        $data["telephone"] ?? null,
        $data["adresse"] ?? null,
        $data["date_naissance"] ?? null,
        $payload["id_utilisateur"]
    ]);

    echo json_encode(["message" => "Profil mis à jour"]);
}

function supprimer_compte_senior() {
    $payload = verifier_token();
    $pdo = getDB();
    $pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?")
        ->execute([$payload["id_utilisateur"]]);
    http_response_code(200);
    echo json_encode(["message" => "Compte supprimé"]);
    exit;
}