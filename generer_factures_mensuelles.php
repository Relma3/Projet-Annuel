<?php
// À lancer via cron le 1er de chaque mois : 0 8 1 * * php /var/www/generer_factures_mensuelles.php

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/PdfGenerator.php';

$db   = getDB();
$mois  = (int)date('n', strtotime('last month'));
$annee = (int)date('Y', strtotime('last month'));
$pdf  = new PdfGenerator();

// Récupérer tous les prestataires actifs
$prestataires = $db->query("SELECT id_prestataire FROM prestataire WHERE statut = 'valide'")->fetchAll();

foreach ($prestataires as $p) {
    try {
        $chemin = $pdf->genererFacturePrestataire($db, $p['id_prestataire'], $mois, $annee);
        echo "✓ Facture générée : $chemin\n";
    } catch (Exception $e) {
        echo "✗ Erreur prestataire {$p['id_prestataire']} : " . $e->getMessage() . "\n";
    }
}