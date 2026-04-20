<?php

require_once 'check_session.php';
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

if (!$is_connected) {
    header('Location: connexion.php');
    exit;
}

$id_pres = isset($_GET['id_prestataire']) ? (int) $_GET['id_prestataire'] : 0;
$mois = isset($_GET['mois']) ? $_GET['mois'] : date('Y-m');

if ($_SESSION['type'] == 'prestataire' && $_SESSION['id'] != $id_pres) {
    die('Acces refuse');
}

if ($_SESSION['type'] != 'admin' && $_SESSION['type'] != 'prestataire') {
    die('Acces refuse');
}

$db = getDB();

$stmt = $db->prepare("SELECT u.email, p.nom, p.prenom, p.tarif_horaire, p.ville
FROM utilisateur u
JOIN prestataire p ON p.id_prestataire = u.id_utilisateur
WHERE u.id_utilisateur = ?");
$stmt->execute([$id_pres]);
$pres = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pres) {
    die('Prestataire introuvable');
}

$debut = $mois . '-01';
$fin = date('Y-m-t', strtotime($debut));

$stmt2 = $db->prepare("SELECT r.*, s.prenom AS s_prenom, s.nom AS s_nom
FROM reservation r
JOIN senior s ON s.id_senior = r.id_senior
WHERE r.id_prestataire = ?
AND r.statut = 'termine'
AND r.date_reservation BETWEEN ? AND ?
ORDER BY r.date_reservation ASC");
$stmt2->execute([$id_pres, $debut . ' 00:00:00', $fin . ' 23:59:59']);
$reservations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$tarif = isset($pres['tarif_horaire']) ? (float) $pres['tarif_horaire'] : 30;
$nb_pres = count($reservations);
$total_ht = $nb_pres * $tarif;
$commission = $total_ht * 0.01;
$total_net = $total_ht - $commission;

$num_facture = 'SH-' . $id_pres . '-' . str_replace('-', '', $mois);
$date_gen = date('d/m/Y');

$lignes = '';

foreach ($reservations as $r) {
    $date = date('d/m/Y', strtotime($r['date_reservation']));
    $client = $r['s_prenom'] . ' ' . $r['s_nom'];

    $lignes .= "
    <tr>
        <td>$date</td>
        <td>Prestation aupres de $client</td>
        <td style='text-align:right'>" . $tarif . " €</td>
    </tr>";
}

if ($lignes == '') {
    $lignes = "<tr><td colspan='3' style='text-align:center;color:#888'>Aucune prestation ce mois-ci</td></tr>";
}

$mois_label = date('m/Y', strtotime($debut));

$html = "
<!DOCTYPE html>
<html lang='fr'>
<head>
<meta charset='UTF-8'>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
h1 { font-size: 18px; color: #333; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th { background: #FF885B; color: white; padding: 8px; text-align: left; }
td { padding: 8px; border-bottom: 1px solid #ddd; }
.total { margin-top: 20px; text-align: right; }
.footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; }
</style>
</head>
<body>

<h1>Facture $num_facture</h1>
<p>Date : $date_gen</p>
<p>Mois : $mois_label</p>

<p><strong>Prestataire :</strong> " . $pres['prenom'] . " " . $pres['nom'] . "</p>
<p><strong>Email :</strong> " . $pres['email'] . "</p>
<p><strong>Ville :</strong> " . $pres['ville'] . "</p>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th style='text-align:right'>Montant</th>
        </tr>
    </thead>
    <tbody>
        $lignes
    </tbody>
</table>

<div class='total'>
    <p>Nombre de prestations : $nb_pres</p>
    <p>Montant brut : " . number_format($total_ht, 2) . " €</p>
    <p>Commission : " . number_format($commission, 2) . " €</p>
    <p><strong>Total net : " . number_format($total_net, 2) . " €</strong></p>
</div>

<div class='footer'>
Silver Happy - Facture generee automatiquement
</div>

</body>
</html>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("facture_$num_facture.pdf", ['Attachment' => true]);