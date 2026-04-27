<?php
require_once 'db_connect.php';
require_once 'PdfGenerator.php';

$pdf = new PdfGenerator();
$chemin = $pdf->genererFacturePrestataire(getDB(), 269, 4, 2026);
echo 'PDF créé : ' . $chemin;