<?php
/** Annulation commande par le senior */
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: /connexion.php'); exit();
}

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'annuler' && $id) {
    $pdo = getDB();
    // Vérifie que la commande appartient au senior et est annulable
    $stmt = $pdo->prepare("UPDATE commandes SET statut = 'annulee' WHERE id_commande = ? AND id_senior = ? AND statut = 'en_attente'");
    $stmt->execute([$id, $_SESSION['id']]);
}

header('Location: /dashboardS.php?msg=commande_annulee#commandes');
exit();