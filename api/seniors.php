<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function get_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT id, email, type_utilisateur, created_at FROM utilisateurs WHERE id = ?");
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

    if (!isset($data["email"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email requis"]);
        return;
    }

    $stmt = $pdo->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
    $stmt->execute([$data["email"], $payload["id_utilisateur"]]);

    echo json_encode(["message" => "Profil mis à jour"]);
}