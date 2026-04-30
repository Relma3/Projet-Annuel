<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $id_pres = $_SESSION['id'];
    
    // On récupère uniquement l'IBAN
    $iban = isset($_POST['iban']) ? htmlspecialchars(trim($_POST['iban'])) : null;

    // Nettoyage (suppression des espaces pour la BDD)
    $iban_propre = $iban ? str_replace(' ', '', $iban) : null;

    if ($iban_propre !== null) {
        try {
            $update = $pdo->prepare("UPDATE prestataire SET iban = ? WHERE id_prestataire = ?");
            $update->execute([$iban_propre, $id_pres]);
            
            header('Location: dashboardP.php?profil=ok#profil');
            exit();
        } catch (PDOException $e) {
            header('Location: dashboardP.php?error=sql#profil');
            exit();
        }
    }
}

header('Location: dashboardP.php#profil');
exit();
