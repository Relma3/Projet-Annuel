<?php

session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $siret = isset($_POST['siret']) ? $_POST['siret'] : '';
    $categorie = isset($_POST['categorie']) ? $_POST['categorie'] : '';
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        $stmtUser = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
        $stmtUser->execute([$email, $password]);

        $id_utilisateur = $pdo->lastInsertId();

        $description = "Domaine : " . $categorie;
        if ($siret != '') {
            $description = $description . " | SIRET : " . $siret;
        }

        $stmtPres = $pdo->prepare("INSERT INTO prestataire (id_prestataire, nom, prenom, ville, description, statut) VALUES (?, ?, 'Pro', 'A definir', ?, 'en_attente')");
        $stmtPres->execute([$id_utilisateur, $nom, $description]);

        $pdo->commit();

        header("Location: connexionpres.php?inscrit=1");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erreur inscription");
    }

} else {
    header("Location: inscriptionpres.php");
    exit();
}