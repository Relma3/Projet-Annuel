<?php
session_start();
require_once 'db_connect.php';
require_once 'api/middleware.php';

if (isset($_POST['email'], $_POST['password'])) {
    $email    = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $source = match($_POST['source'] ?? '') {
    'prestataire' => 'connexionpres.php',
    'admin'       => 'connexion_admin.php',
    default       => 'connexion.php'
};

    try {
        $stmt = $pdo->prepare(
            "SELECT u.id_utilisateur, u.mot_de_passe, u.type_utilisateur, u.est_actif
             FROM utilisateur u WHERE u.email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {

            if (($_POST['source'] ?? '') === 'admin' && $user['type_utilisateur'] !== 'admin') {
                header('Location: connexion_admin.php?error=403');
                exit();
            }

            if ($user['est_actif'] == 0) {
                header("Location: $source?pending=1");
                exit();
            }

            $_SESSION['id']   = $user['id_utilisateur'];
            $_SESSION['type'] = $user['type_utilisateur'];

            if ($user['type_utilisateur'] === 'senior') {
                $stmtS = $pdo->prepare("SELECT prenom FROM senior WHERE id_senior = ?");
                $stmtS->execute([$user['id_utilisateur']]);
                $senior = $stmtS->fetch();
                $_SESSION['prenom'] = $senior['prenom'] ?? 'Adhérent';
            } elseif ($user['type_utilisateur'] === 'prestataire') {
                $stmtP = $pdo->prepare("SELECT nom FROM prestataire WHERE id_prestataire = ?");
                $stmtP->execute([$user['id_utilisateur']]);
                $pres = $stmtP->fetch();
                $_SESSION['prenom'] = $pres['nom'] ?? 'Prestataire';
            } else {
                $_SESSION['prenom'] = 'Admin';
            }

            $token = generer_token($user['id_utilisateur'], $user['type_utilisateur']);
            $_SESSION['jwt_token'] = $token;

            if ($user['type_utilisateur'] === 'senior') {
                header('Location: dashboardS.php');
            } elseif ($user['type_utilisateur'] === 'prestataire') {
                header('Location: dashboardP.php');
            } else {
                header('Location: /frontend/admin/dashboard.php?token=' . urlencode($token));
            }
            exit();

        } else {
            header("Location: $source?error=1");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Erreur connexion : " . $e->getMessage());
        header("Location: $source?error=500");
        exit();
    }
}
