<?php

session_start();
require_once 'db_connect.php';
require_once 'api/middleware.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $source = "connexion.php";

    if (isset($_POST['source']) && $_POST['source'] == "prestataire") {
        $source = "connexionpres.php";
    }

    try {
        $stmt = $pdo->prepare("SELECT id_utilisateur, mot_de_passe, type_utilisateur, est_actif FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {

            if (isset($_POST['source']) && $_POST['source'] == "admin" && $user['type_utilisateur'] != "admin") {
                header("Location: connexion_admin.php?error=403");
                exit();
            }

            if ($user['est_actif'] == 0) {
                header("Location: " . $source . "?pending=1");
                exit();
            }

            $_SESSION['id'] = $user['id_utilisateur'];
            $_SESSION['type'] = $user['type_utilisateur'];

            if ($user['type_utilisateur'] == 'senior') {
                $stmtS = $pdo->prepare("SELECT prenom FROM senior WHERE id_senior = ?");
                $stmtS->execute([$user['id_utilisateur']]);
                $senior = $stmtS->fetch();
                $_SESSION['prenom'] = $senior['prenom'] ? $senior['prenom'] : 'Adherent';
            }

            if ($user['type_utilisateur'] == 'prestataire') {
                $stmtP = $pdo->prepare("SELECT nom FROM prestataire WHERE id_prestataire = ?");
                $stmtP->execute([$user['id_utilisateur']]);
                $pres = $stmtP->fetch();
                $_SESSION['prenom'] = $pres['nom'] ? $pres['nom'] : 'Prestataire';
            }

            if ($user['type_utilisateur'] == 'admin') {
                $_SESSION['prenom'] = 'Admin';
            }

            $token = generer_token($user['id_utilisateur'], $user['type_utilisateur']);
            $_SESSION['jwt_token'] = $token;

            if ($user['type_utilisateur'] == 'senior') {
                header("Location: dashboardS.php");
                exit();
            }

            if ($user['type_utilisateur'] == 'prestataire') {
                header("Location: dashboardP.php");
                exit();
            }

            if ($user['type_utilisateur'] == 'admin') {
                header("Location: frontend/admin/dashboard.html?token=" . urlencode($token));
                exit();
            }

        } else {
            header("Location: " . $source . "?error=1");
            exit();
        }

    } catch (PDOException $e) {
        header("Location: " . $source . "?error=500");
        exit();
    }
}