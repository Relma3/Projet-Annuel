<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

define('FACTURES_DIR', __DIR__ . '/../storage/factures/');

if (!is_dir(FACTURES_DIR)) {
    mkdir(FACTURES_DIR, 0755, true);
}

function generer_facture_prestataire($id_pres, $mois, $pdo) {
    $debut = $mois . '-01';
    $fin = date('Y-m-t', strtotime($debut));

    $stmt = $pdo->prepare("
        SELECT u.email, p.nom, p.prenom, p.tarif_horaire, p.ville
        FROM utilisateur u
        JOIN prestataire p ON p.id_prestataire = u.id_utilisateur
        WHERE u.id_utilisateur = ?
    ");
    $stmt->execute([$id_pres]);
    $pres = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pres) {
        return null;
    }

    $stmt2 = $pdo->prepare("
        SELECT r.date_reservation, s.prenom AS s_prenom, s.nom AS s_nom
        FROM reservation r
        JOIN senior s ON s.id_senior = r.id_senior
        WHERE r.id_prestataire = ?
        AND r.statut = 'termine'
        AND r.date_reservation BETWEEN ? AND ?
        ORDER BY r.date_reservation ASC
    ");
    $stmt2->execute([$id_pres, $debut . ' 00:00:00', $fin . ' 23:59:59']);
    $reservations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $tarif = $pres['tarif_horaire'] ? $pres['tarif_horaire'] : 30;
    $nb = count($reservations);
    $total_ht = $nb * $tarif;
    $commission = $total_ht * 0.01;
    $total_net = $total_ht - $commission;
    $num = 'SH-' . $id_pres . '-' . str_replace('-', '', $mois);

    $lignes = '';
    foreach ($reservations as $r) {
        $date = date('d/m/Y H:i', strtotime($r['date_reservation']));
        $client = $r['s_prenom'] . ' ' . $r['s_nom'];
        $lignes .= "<tr>
            <td>$date</td>
            <td>Prestation pour $client</td>
            <td style='text-align:right'>" . number_format($tarif, 2) . " €</td>
        </tr>";
    }

    if ($lignes == '') {
        $lignes = "<tr><td colspan='3'>Aucune prestation</td></tr>";
    }

    $html = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; }
            th { background: #eeeeee; }
        </style>
    </head>
    <body>
        <h1>Facture $num</h1>
        <p>Prestataire : {$pres['prenom']} {$pres['nom']}</p>
        <p>Email : {$pres['email']}</p>
        <p>Ville : {$pres['ville']}</p>

        <table>
            <tr>
                <th>Date</th>
                <th>Prestation</th>
                <th>Montant</th>
            </tr>
            $lignes
        </table>

        <p>Nombre de prestations : $nb</p>
        <p>Montant brut : " . number_format($total_ht, 2) . " €</p>
        <p>Commission : " . number_format($commission, 2) . " €</p>
        <p><strong>Total net : " . number_format($total_net, 2) . " €</strong></p>
    </body>
    </html>
    ";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = $num . '.pdf';
    file_put_contents(FACTURES_DIR . $filename, $dompdf->output());

    return [
        'filename' => $filename,
        'mois' => $mois,
        'total_net' => round($total_net, 2)
    ];
}

function facturation_mensuelle_tous() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents('php://input'), true);
    $mois = $data['mois'] ?? date('Y-m', strtotime('-1 month'));

    $stmt = $pdo->query("SELECT id_prestataire FROM prestataire WHERE statut = 'valide'");
    $prestataires = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $resultats = [];

    foreach ($prestataires as $id_pres) {
        $facture = generer_facture_prestataire($id_pres, $mois, $pdo);
        if ($facture) {
            $resultats[] = $facture;
        }
    }

    echo json_encode([
        'success' => true,
        'factures' => count($resultats),
        'detail' => $resultats
    ]);
}

function telecharger_facture($id_pres) {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents('php://input'), true);
    $mois = $data['mois'] ?? date('Y-m', strtotime('-1 month'));

    $facture = generer_facture_prestataire($id_pres, $mois, $pdo);

    if (!$facture) {
        http_response_code(404);
        echo json_encode(['message' => 'Prestataire introuvable']);
        return;
    }

    $filepath = FACTURES_DIR . $facture['filename'];

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $facture['filename'] . '"');
    readfile($filepath);
    exit;
}

function lister_factures_archivees() {
    verifier_admin();

    $files = glob(FACTURES_DIR . '*.pdf');
    $liste = [];

    foreach ($files as $f) {
        $liste[] = [
            'nom' => basename($f),
            'taille' => filesize($f),
            'date' => date('d/m/Y H:i', filemtime($f))
        ];
    }

    echo json_encode(['factures' => $liste]);
}

function mes_factures_prestataire() {
    $data = verifier_prestataire();
    $id_pres = $data['id_utilisateur'];

    $files = glob(FACTURES_DIR . 'SH-' . $id_pres . '-*.pdf');
    $liste = [];

    foreach ($files as $f) {
        $liste[] = [
            'nom' => basename($f),
            'taille' => filesize($f),
            'date' => date('d/m/Y H:i', filemtime($f))
        ];
    }

    echo json_encode(['factures' => $liste]);
}

function telecharger_ma_facture() {
    $data = verifier_prestataire();
    $id_pres = $data['id_utilisateur'];
    $nom = basename($_GET['nom'] ?? '');

    if (strpos($nom, 'SH-' . $id_pres . '-') !== 0) {
        http_response_code(403);
        echo json_encode(['message' => 'Acces refuse']);
        return;
    }

    $filepath = FACTURES_DIR . $nom;

    if (!file_exists($filepath)) {
        http_response_code(404);
        echo json_encode(['message' => 'Facture introuvable']);
        return;
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nom . '"');
    readfile($filepath);
    exit;
}