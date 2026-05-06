<?php
session_start();
require_once 'db_connect.php';
require_once 'api/middleware.php';

if (!isset($_POST['email'], $_POST['password'])) {
    exit();
}

$email    = htmlspecialchars(trim($_POST['email']));
$password = $_POST['password'];
$source   = $_POST['source'] ?? 'senior';

$page_erreur = match($source) {
    'prestataire' => 'connexionpres.php',
    'admin'       => 'connexion_admin.php',
    default       => 'connexion.php'
};

$type_attendu = match($source) {
    'prestataire' => 'prestataire',
    'admin'       => 'admin',
    default       => 'senior'
};

try {
    $stmt = $pdo->prepare("
        SELECT id_utilisateur, mot_de_passe, type_utilisateur, est_actif
        FROM utilisateur
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        header("Location: $page_erreur?error=1");
        exit();
    }
    if ($user['type_utilisateur'] !== $type_attendu) {
        header("Location: $page_erreur?error=mauvais_compte");
        exit();
    }

    if ($user['est_actif'] == 0) {
        header("Location: $page_erreur?pending=1");
        exit(); 
    }

    session_unset();
    session_destroy(); 
    session_start();

    $_SESSION['id']   = $user['id_utilisateur'];
    $_SESSION['type'] = $user['type_utilisateur'];
    $_SESSION['email'] = $email;

    if ($user['type_utilisateur'] === 'senior') {
        $stmtS = $pdo->prepare("SELECT prenom, nom FROM senior WHERE id_senior = ?");
        $stmtS->execute([$user['id_utilisateur']]);
        $senior = $stmtS->fetch();
        $_SESSION['prenom'] = $senior['prenom'] ?? 'Adhérent';
        $_SESSION['nom']    = $senior['nom']    ?? '';

    } elseif ($user['type_utilisateur'] === 'prestataire') {
        $stmtP = $pdo->prepare("SELECT nom, prenom FROM prestataire WHERE id_prestataire = ?");
        $stmtP->execute([$user['id_utilisateur']]);
        $pres = $stmtP->fetch();
        $_SESSION['nom']    = $pres['nom']    ?? '';
        $_SESSION['prenom'] = $pres['prenom'] ?? 'Prestataire';

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

} catch (PDOException $e) {
    error_log("Erreur connexion : " . $e->getMessage());
    header("Location: $page_erreur?error=500");
    exit();
}
