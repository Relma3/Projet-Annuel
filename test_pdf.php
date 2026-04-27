<?php
require_once 'db_connect.php';
require_once 'PdfGenerator.php';

$pdf = new PdfGenerator();
$chemin = $pdf->genererFactureSenior(getDB(), 1);
echo 'PDF créé : ' . $chemin;