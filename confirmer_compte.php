<?php
require_once('db_connect.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

 
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE token_confirmation = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
       
        $valider = $pdo->prepare("UPDATE utilisateur SET est_actif = 1, token_confirmation = NULL WHERE id_utilisateur = ?");
        $valider->execute([$user['id_utilisateur']]);

        header('Location: connexion.php?valide=1');
        exit();
    } else {
        echo "Lien invalide ou compte déjà validé.";
    }
}
?>