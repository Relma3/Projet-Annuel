<?php
require_once 'check_session.php';
require_once 'db_connect.php';
require_once 'PdfGenerator.php';

if (!$is_connected) { header('Location: connexion.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

// Vérifier que la facture appartient bien à l'utilisateur connecté (sécurité !)
if ($_SESSION['type'] === 'senior') {
    $stmt = $db->prepare("
        SELECT f.pdf_path FROM factures f
        JOIN paiements p ON p.id_paiement = f.id_paiement
        WHERE f.id_facture = ? AND p.id_payeur = ?
    ");
    $stmt->execute([$id, $_SESSION['id']]);
} elseif ($_SESSION['type'] === 'prestataire') {
    $stmt = $db->prepare("
        SELECT pdf_path FROM factures WHERE id_facture = ? AND id_prestataire = ?
    ");
    // récupère l'id_prestataire depuis la session ou BDD
    $stmt->execute([$id, $_SESSION['id_prestataire']]);
} elseif ($_SESSION['type'] === 'admin') {
    $stmt = $db->prepare("SELECT pdf_path FROM factures WHERE id_facture = ?");
    $stmt->execute([$id]);
}

$facture = $stmt->fetch();
if (!$facture) { http_response_code(403); exit('Accès refusé'); }

PdfGenerator::servir(__DIR__ . '/' . $facture['pdf_path']);