<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

$id_res = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['a']) ? $_GET['a'] : '';

if ($id_res > 0 && !empty($action)) {
    $nouveau_statut = '';
    
    if ($action === 'accepter') {
        $nouveau_statut = 'confirme';
    } elseif ($action === 'refuser') {
        $nouveau_statut = 'annule';
    } elseif ($action === 'terminer') {
        $nouveau_statut = 'termine';
    }

    if (!empty($nouveau_statut)) {
        try {
            $stmt = $pdo->prepare("UPDATE reservation SET statut = ? WHERE id_reservation = ? AND id_prestataire = ?");
            $stmt->execute([$nouveau_statut, $id_res, $_SESSION['id']]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
}

header('Location: dashboardP.php'); 
exit();
