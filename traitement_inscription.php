<?php

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: inscription.php");
    exit();
}

$prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
$nom = isset($_POST['nom']) ? $_POST['nom'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$prenom || !$nom || !$email || !$password) {
    header("Location: inscription.php?error=champs_manquants");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: inscription.php?error=email_invalide");
    exit();
}

if (strlen($password) < 8) {
    header("Location: inscription.php?error=mdp_trop_court");
    exit();
}

$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $pdo->rollBack();
        header("Location: inscription.php?error=email_existe");
        exit();
    }

    $stmtUser = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
    $stmtUser->execute([$email, $hash]);

    $id_utilisateur = $pdo->lastInsertId();

    $stmtSenior = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom) VALUES (?, ?, ?)");
    $stmtSenior->execute([$id_utilisateur, $nom, $prenom]);

    $pdo->commit();

    header("Location: connexion.php?inscrit=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: inscription.php?error=erreur_serveur");
    exit();
}