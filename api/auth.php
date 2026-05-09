<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

function login() {
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"]) || !isset($data["mot_de_passe"])) {
        http_response_code(400);
        echo json_encode(["message" => "Champs manquants"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$data["email"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data["mot_de_passe"], $user["mot_de_passe"])) {
        http_response_code(401);
        echo json_encode(["message" => "Email ou mot de passe incorrect"]);
        return;
    }

$token = generer_token($user["id_utilisateur"], $user["type_utilisateur"]);

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

    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur) VALUES (?, ?, 'senior')");
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

    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur) VALUES (?, ?, 'prestataire')");
    $stmt->execute([$data["email"], $hash]);

    echo json_encode(["message" => "Inscription prestataire OK"]);
}

function forgotPassword() {
    $pdo  = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["email"])) {
        http_response_code(400);
        echo json_encode(["message" => "Email manquant"]);
        return;
    }

    $email = trim($data["email"]);

    // Vérifier que l'email existe
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Toujours renvoyer le même message (sécurité)
    if (!$user) {
        echo json_encode(["message" => "Si cet email existe, un lien de réinitialisation vous a été envoyé."]);
        return;
    }

    // Générer un token de réinitialisation (valable 1h)
    $token   = bin2hex(random_bytes(32));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $pdo->prepare("UPDATE utilisateur SET token_confirmation = ?, token_expiration = ? WHERE id_utilisateur = ?")
        ->execute([$token, $expiration, $user['id_utilisateur']]);

    // Envoyer l'email
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'silverhappy.contact@gmail.com';
        $mail->Password   = 'dbzpbzmnrrdfkreo';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('silverhappy.contact@gmail.com', 'Silver Happy');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Réinitialisation de votre mot de passe';
        $link = "https://www.silverhappy.com/reset_password.php?token=$token";
        $mail->Body = "
            <h2>Réinitialisation de mot de passe</h2>
            <p>Cliquez sur ce lien pour réinitialiser votre mot de passe (valable 1 heure) :</p>
            <a href='$link' style='background:#FF885B;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                Réinitialiser mon mot de passe
            </a>
            <p style='color:#999;margin-top:20px;font-size:12px;'>Si vous n'avez pas fait cette demande, ignorez cet email.</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur mail reset: " . $mail->ErrorInfo);
    }

    echo json_encode(["message" => "Si cet email existe, un lien de réinitialisation vous a été envoyé."]);
}