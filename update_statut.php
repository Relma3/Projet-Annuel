<?php
session_start();
require_once 'db_connect.php';

if(isset($_GET['id']) && isset($_GET['action'])) {
    $id_res = intval($_GET['id']);
    $action = $_GET['action']; 

    $statuts_autorises = ['confirme', 'annule', 'termine', 'en_attente'];
    if (!in_array($action, $statuts_autorises)) {
        header('Location: dashboardP.php?err=statut_invalide');
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE reservation SET statut = ? WHERE id_reservation = ?");
        $stmt->execute([$action, $id_res]);
        
        header('Location: dashboardP.php?msg=success#demandes');
        exit();
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
