<?php
session_start();
require_once 'db_connect.php';

// Action tutoriel (inchangée)
if (isset($_GET['action']) && $_GET['action'] === 'tutoriel' && isset($_SESSION['id'])) {
    $pdo->prepare("UPDATE senior SET tutoriel_vu = 1 WHERE id_senior = ?")
        ->execute([$_SESSION['id']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'senior') {
    $id = $_SESSION['id'];

    $prenom         = htmlspecialchars(trim($_POST['prenom']   ?? ''));
    $nom            = htmlspecialchars(trim($_POST['nom']      ?? ''));
    $telephone      = htmlspecialchars(trim($_POST['telephone'] ?? ''));
    $ville          = htmlspecialchars(trim($_POST['ville']    ?? ''));
    $adresse        = htmlspecialchars(trim($_POST['adresse']  ?? ''));
    $date_naissance = $_POST['date_naissance'] ?? null;
    $email          = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    try {
        // Mise à jour table senior
        $pdo->prepare("
            UPDATE senior
            SET prenom = ?, nom = ?, telephone = ?, ville = ?, adresse = ?, date_naissance = ?
            WHERE id_senior = ?
        ")->execute([$prenom, $nom, $telephone, $ville, $adresse,
                     $date_naissance ?: null, $id]);

        // Mise à jour email dans utilisateur
        if ($email) {
            $pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_utilisateur = ?")
                ->execute([$email, $id]);
        }

        // Mettre à jour la session avec le nouveau prénom
        $_SESSION['prenom'] = $prenom;

        header('Location: dashboardS.php?msg=profil_mis_a_jour#profil');
    } catch (PDOException $e) {
        header('Location: dashboardS.php?err=update_failed#profil');
    }
} else {
    header('Location: dashboardS.php');
}
exit();