<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function get_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT id_utilisateur, email, type_utilisateur, created_at FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$payload["id_utilisateur"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "Utilisateur non trouve"]);
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
        echo json_encode(["message" => "Email manquant"]);
        return;
    }

    $stmt = $pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_utilisateur = ?");
    $stmt->execute([$data["email"], $payload["id_utilisateur"]]);

    echo json_encode(["message" => "Profil mis a jour"]);
}