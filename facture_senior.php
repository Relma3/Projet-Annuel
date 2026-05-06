<?php
session_start();
require_once 'db_connect.php';
require_once 'PdfGenerator.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php'); exit();
}

$id_paiement = (int)($_GET['id'] ?? 0);

// Vérifier que ce paiement appartient bien au senior connecté
$check = $pdo->prepare("SELECT id_paiement FROM paiements WHERE id_paiement = ? AND id_payeur = ? AND statut = 'reussi'");
$check->execute([$id_paiement, $_SESSION['id']]);
if (!$check->fetch()) { http_response_code(403); exit('Accès refusé'); }

$pdf = new PdfGenerator();
$chemin = $pdf->genererFactureSenior($pdo, $id_paiement);
PdfGenerator::servir($chemin);