<?php
session_start();
require_once 'db_connect.php';
require_once 'api/utils/captcha.php';

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

   if (!isset($captcha) || !verifyCaptcha($captcha)) {
        header('Location: inscription.php?error=captcha_incorrect');
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        $stmtUser = $pdo->prepare("
            INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) 
            VALUES (?, ?, 'senior', 1)
        ");
        $stmtUser->execute([$email, $password_hash]);
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
        header('Location: connexion.php?inscrit=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        // Email déjà utilisé
        if ($e->getCode() == 23000) {
            header('Location: inscription.php?error=email_existe');
            exit();
        }
        die("Erreur lors de l'inscription : " . $e->getMessage());
    }
}