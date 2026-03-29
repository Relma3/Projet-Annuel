<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['password'])) {

        $prenom   = htmlspecialchars($_POST['prenom']);
        $nom      = htmlspecialchars($_POST['nom']);
        $email    = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
            $stmtUser->execute([$email, $password]);

            $id_utilisateur = $pdo->lastInsertId();

            $stmtSenior = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom) VALUES (?, ?, ?)");
            $stmtSenior->execute([$id_utilisateur, $nom, $prenom]);

            $pdo->commit();

            header('Location: connexion.php?inscrit=1');
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erreur lors de l'inscription : " . $e->getMessage());
        }

    } else {
        header('Location: inscription.php?error=champs_manquants');
        exit();
    }
}