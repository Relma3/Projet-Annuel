<?php
session_start();
require_once 'db_connect.php';

// filtre par ID prestataires
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

if(isset($_GET['id']) && isset($_GET['action'])) {
    $id_res = intval($_GET['id']);
    $action = $_GET['action']; 

    $statuts_autorises = ['confirme', 'annule', 'termine', 'en_attente'];
    if (!in_array($action, $statuts_autorises)) {
        header('Location: dashboardP.php?err=statut_invalide');
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE reservation SET statut = ? WHERE id_reservation = ? AND id_prestataire = ?");
        $stmt->execute([$action, $id_res, $_SESSION['id']]);
        
        header('Location: dashboardP.php?msg=success#demandes');
        exit();
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
