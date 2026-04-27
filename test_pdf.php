<?php
require_once 'check_session.php';
if (!$is_connected || $_SESSION['type'] !== 'admin') {
    die('Accès refusé');
}
require_once 'db_connect.php';
require_once 'PdfGenerator.php';

$pdf = new PdfGenerator();
$chemin = $pdf->genererFacturePrestataire(getDB(), 1, 4, 2026);
echo 'PDF créé : ' . $chemin;