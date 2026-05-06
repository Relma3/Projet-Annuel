<?php
session_start();
require_once 'db_connect.php';
require_once 'api/utils/captcha.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prenom         = htmlspecialchars(trim($_POST['prenom'] ?? ''));
    $nom            = htmlspecialchars(trim($_POST['nom'] ?? ''));
    $email          = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password       = $_POST['password'] ?? '';
    $telephone      = htmlspecialchars(trim($_POST['telephone'] ?? ''));
    $date_naissance = $_POST['date_naissance'] ?? '';
    $adresse        = htmlspecialchars(trim($_POST['adresse'] ?? ''));
    $ville          = htmlspecialchars(trim($_POST['ville'] ?? ''));
    $captcha        = $_POST['captcha'] ?? '';

    if (empty($prenom) || empty($nom) || empty($email) || empty($password)) {
        header('Location: inscription.php?error=champs_manquants');
        exit();
    }

    if (!verifyCaptcha($captcha)) {
        header('Location: inscription.php?error=captcha_incorrect');
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        $token = bin2hex(random_bytes(32));

        $stmtUser = $pdo->prepare("
            INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif, token_confirmation) 
            VALUES (?, ?, 'senior', 0, ?)
        ");
        $stmtUser->execute([$email, $password_hash, $token]);

        $id_utilisateur = $pdo->lastInsertId();

        $stmtSenior = $pdo->prepare("
            INSERT INTO senior (id_senior, nom, prenom, telephone, date_naissance, adresse, ville) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtSenior->execute([
            $id_utilisateur,
            $nom,
            $prenom,
            $telephone,
            $date_naissance ?: null,
            $adresse,
            $ville
        ]);

        $pdo->commit();

        // ===== ENVOI MAIL =====
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'silverhappy.contact@gmail.com';
            $mail->Password = 'dbzp bzmn rrdf kreo';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('silverhappy.contact@gmail.com', 'Silver Happy');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirme ton compte';

            $link = "https://www.silverhappy.com/confirmer_compte.php?token=$token";

            $mail->Body = "
                <h2>Bienvenue !</h2>
                <p>Clique ici pour activer ton compte :</p>
                <a href='$link'>Activer mon compte</a>
            ";

            $mail->send();

        } catch (Exception $e) {
            error_log("Erreur mail : " . $mail->ErrorInfo);
        }

        header('Location: connexion.php?inscrit=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();

        if ($e->getCode() == 23000) {
            header('Location: inscription.php?error=email_existe');
            exit();
        }

        die("Erreur : " . $e->getMessage());
    }
}