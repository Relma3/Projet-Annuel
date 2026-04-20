<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function login() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["email"]) || empty($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email et mot de passe obligatoires"]);
        return;
    }

    $email = strtolower(trim($data["email"]));

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data["mot_de_passe"], $user["mot_de_passe"])) {
        http_response_code(401);
        echo json_encode(["message" => "Email ou mot de passe incorrect"]);
        return;
    }

    if (!$user["est_actif"]) {
        http_response_code(403);
        echo json_encode(["message" => "Compte non active"]);
        return;
    }

    $nom = "";
    $prenom = "";

    if ($user["type_utilisateur"] == "senior") {
        $stmt2 = $pdo->prepare("SELECT nom, prenom FROM senior WHERE id_senior = ?");
        $stmt2->execute([$user["id_utilisateur"]]);
        $info = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $nom = $info["nom"];
            $prenom = $info["prenom"];
        }
    }

    if ($user["type_utilisateur"] == "prestataire") {
        $stmt2 = $pdo->prepare("SELECT nom, prenom FROM prestataire WHERE id_prestataire = ?");
        $stmt2->execute([$user["id_utilisateur"]]);
        $info = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $nom = $info["nom"];
            $prenom = $info["prenom"];
        }
    }

    $token = generer_token($user["id_utilisateur"], $user["type_utilisateur"]);

    echo json_encode([
        "message" => "Connexion reussie",
        "token" => $token,
        "type_utilisateur" => $user["type_utilisateur"],
        "id_utilisateur" => $user["id_utilisateur"],
        "nom" => $nom,
        "prenom" => $prenom
    ]);
}

function registerSenior() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["email"]) || empty($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email et mot de passe obligatoires"]);
        return;
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Email invalide"]);
        return;
    }

    $email = strtolower(trim($data["email"]));

    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(["message" => "Email deja utilise"]);
        return;
    }

    $hash = password_hash($data["mot_de_passe"], PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(32));

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif, token_confirmation) VALUES (?, ?, 'senior', 1, ?)");
        $stmt->execute([$email, $hash, $token]);

        $id = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom, telephone, date_naissance, adresse) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->execute([
            $id,
            $data["nom"] ?? "",
            $data["prenom"] ?? "",
            $data["telephone"] ?? null,
            $data["date_naissance"] ?? null,
            $data["adresse"] ?? null
        ]);

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            "message" => "Inscription reussie",
            "id_utilisateur" => $id
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Erreur inscription"]);
    }
}

function registerPrestataire() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["email"]) || empty($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email et mot de passe obligatoires"]);
        return;
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Email invalide"]);
        return;
    }

    $email = strtolower(trim($data["email"]));

    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(["message" => "Email deja utilise"]);
        return;
    }

    $hash = password_hash($data["mot_de_passe"], PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
        $stmt->execute([$email, $hash]);

        $id = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO prestataire (id_prestataire, nom, prenom, ville, categorie, description, statut, tarif_horaire) VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?)");
        $stmt2->execute([
            $id,
            $data["nom"] ?? "",
            $data["prenom"] ?? "",
            $data["ville"] ?? "",
            $data["categorie"] ?? "Autre",
            $data["description"] ?? "",
            $data["tarif_horaire"] ?? 30
        ]);

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            "message" => "Inscription prestataire reussie",
            "id_utilisateur" => $id
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Erreur inscription"]);
    }
}

function forgotPassword() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["email"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email obligatoire"]);
        return;
    }

    $email = strtolower(trim($data["email"]));

    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare("UPDATE utilisateur SET token_confirmation = ? WHERE id_utilisateur = ?")
            ->execute([$token, $user["id_utilisateur"]]);
    }

    echo json_encode([
        "message" => "Si cet email existe, un lien sera envoye"
    ]);
}