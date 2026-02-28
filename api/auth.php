<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function login() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"]) || !isset($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Champs manquants"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$data["email"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data["mot_de_passe"], $user["mot_de_passe"])) {
        http_response_code(401);
        echo json_encode(["message" => "Email ou mot de passe incorrect"]);
        return;
    }

    $token = generer_token($user["id"], $user["type_utilisateur"]);

echo json_encode([
    "message" => "Connexion OK",
    "token" => $token,
    "type_utilisateur" => $user["type_utilisateur"]
]);

}

function registerSenior() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"]) || !isset($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Champs manquants"]);
        return;
    }

    $hash = password_hash($data["mot_de_passe"], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe, type_utilisateur) VALUES (?, ?, 'senior')");
    $stmt->execute([$data["email"], $hash]);

    echo json_encode(["message" => "Inscription senior OK"]);
}

function registerPrestataire() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"]) || !isset($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Champs manquants"]);
        return;
    }

    $hash = password_hash($data["mot_de_passe"], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe, type_utilisateur) VALUES (?, ?, 'prestataire')");
    $stmt->execute([$data["email"], $hash]);

    echo json_encode(["message" => "Inscription prestataire OK"]);
}

function forgotPassword() {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email manquant"]);
        return;
    }

    echo json_encode([
        "message" => "Si l'email existe, un lien de réinitialisation sera envoyé (fonctionnalité à venir)."
    ]);
}