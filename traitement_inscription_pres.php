<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom       = htmlspecialchars($_POST['nom']);
    $email     = htmlspecialchars($_POST['email']);
    $siret     = htmlspecialchars($_POST['siret'] ?? '');
    $categorie = htmlspecialchars($_POST['categorie'] ?? '');
    $password  = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        $stmtUser = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
        $stmtUser->execute([$email, $password]);

        $id_utilisateur = $pdo->lastInsertId();

        $description = "Domaine : " . $categorie . ($siret ? " | SIRET : " . $siret : "");

        $stmtPres = $pdo->prepare("INSERT INTO prestataire (id_prestataire, nom, prenom, ville, description, statut) VALUES (?, ?, 'Pro', 'À définir', ?, 'en_attente')");
        $stmtPres->execute([$id_utilisateur, $nom, $description]);

        $pdo->commit();

        header('Location: connexionpres.php?inscrit=1');
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erreur lors de l'inscription : " . $e->getMessage());
    }

} else {
    header('Location: inscriptionpres.php');
    exit();
}