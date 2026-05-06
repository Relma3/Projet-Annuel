<?php
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token invalide");
}

$stmt = $pdo->prepare("
    SELECT id_utilisateur 
    FROM utilisateur 
    WHERE token_confirmation = ?
");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Lien invalide ou déjà utilisé");
}

// Activation du compte
$stmt = $pdo->prepare("
    UPDATE utilisateur 
    SET est_actif = 1, token_confirmation = NULL 
    WHERE id_utilisateur = ?
");
$stmt->execute([$user['id_utilisateur']]);

echo "<h2> Compte activé !</h2>";
echo "<a href='connexion.php'>Se connecter</a>";