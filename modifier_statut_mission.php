<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    die("Accès refusé");
}

$id_reservation = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id_reservation > 0) {
    $nouveau_statut = ($action === 'accepter') ? 'confirme' : 'annule';
    
    try {
        $stmt = $pdo->prepare("UPDATE reservation SET statut = ? WHERE id_reservation = ? AND id_prestataire = ?");
        $stmt->execute([$nouveau_statut, $id_reservation, $_SESSION['id']]);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

header('Location: dashboardP.php#demandes');
exit();
