<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $nom      = htmlspecialchars($_POST['nom'] ?? '');
    $ville    = htmlspecialchars($_POST['ville'] ?? '');
    $desc     = htmlspecialchars($_POST['description'] ?? '');
    $categorie = htmlspecialchars($_POST['categorie'] ?? '');
    $tarif    = isset($_POST['tarif_horaire']) && is_numeric($_POST['tarif_horaire'])
                ? (float)$_POST['tarif_horaire'] : null;
    $id_pres  = $_SESSION['id'];

    try {
        $stmt = $pdo->prepare("UPDATE prestataire SET nom = ?, ville = ?, description = ?, categorie = ?, tarif_horaire = COALESCE(?, tarif_horaire) WHERE id_prestataire = ?");
        $stmt->execute([$nom, $ville, $desc, $categorie, $tarif, $id_pres]);
    } catch (PDOException $e) {
        
    }
}

header('Location: dashboardP.php#profil');
exit();
