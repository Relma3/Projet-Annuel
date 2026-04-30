<?php
session_start();
require_once 'db_connect.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: dashboardP.php#profil');
    exit();
}
 
$id_pres = $_SESSION['id'];
 
$iban        = trim($_POST['iban']        ?? '');
$telephone   = trim($_POST['telephone']   ?? '');
$adresse     = trim($_POST['adresse']     ?? '');
$ville       = trim($_POST['ville']       ?? '');
$bio         = trim($_POST['bio']         ?? '');
$tarif       = floatval($_POST['tarif_horaire'] ?? 0);

$iban_propre = strtoupper(str_replace(' ', '', $iban));
if (!empty($iban_propre)) {
    if (!preg_match('/^FR[0-9]{2}[0-9A-Z]{23}$/', $iban_propre)) {
        header('Location: dashboardP.php?error=iban_invalide#profil');
        exit();
    }
}
 
try {
    $fields = [];
    $values = [];
 
    if (!empty($iban_propre)) {
        $fields[] = 'iban = ?';
        $values[] = $iban_propre;
    }
    if (!empty($telephone)) {
        $fields[] = 'telephone = ?';
        $values[] = htmlspecialchars($telephone);
    }
    if (!empty($adresse)) {
        $fields[] = 'adresse = ?';
        $values[] = htmlspecialchars($adresse);
    }
    if (!empty($ville)) {
        $fields[] = 'ville = ?';
        $values[] = htmlspecialchars($ville);
    }
    if (!empty($bio)) {
        $fields[] = 'bio = ?';
        $values[] = htmlspecialchars($bio);
    }
    if ($tarif > 0) {
        $fields[] = 'tarif_horaire = ?';
        $values[] = $tarif;
    }
 
    if (!empty($fields)) {
        $values[] = $id_pres;
        $sql = "UPDATE prestataire SET " . implode(', ', $fields) . " WHERE id_prestataire = ?";
        $pdo->prepare($sql)->execute($values);
    }
 
    header('Location: dashboardP.php?profil=ok#profil');
    exit();
 
} catch (PDOException $e) {
    error_log("Erreur update_pres : " . $e->getMessage());
    header('Location: dashboardP.php?error=sql#profil');
    exit();
}
 
