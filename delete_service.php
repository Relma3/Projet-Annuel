<?php
session_start();
require_once 'db_connect.php';

if (isset($_GET['id']) && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $id_reservation = intval($_GET['id']); 
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = ? AND id_prestataire = ?");
        $stmt->execute([$id_reservation, $_SESSION['id']]);
    } catch (PDOException $e) {
        
    }
}

header('Location: dashboardP.php');
exit();
