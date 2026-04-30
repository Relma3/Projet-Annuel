<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $id_pres = $_SESSION['id'];

    $nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : null;
    $ville = isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : null;
    $desc = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : null;
    $iban = isset($_POST['iban']) ? htmlspecialchars(trim($_POST['iban'])) : null;


    $iban_propre = $iban ? str_replace(' ', '', $iban) : null;

    try {

        $stmtCurrent = $pdo->prepare("SELECT nom, ville, description, iban FROM prestataire WHERE id_prestataire = ?");
        $stmtCurrent->execute([$id_pres]);
        $current = $stmtCurrent->fetch();

      
        $final_nom = $nom !== null ? $nom : $current['nom'];
        $final_ville = $ville !== null ? $ville : $current['ville'];
        $final_desc = $desc !== null ? $desc : $current['description'];
        $final_iban = $iban_propre !== null ? $iban_propre : $current['iban'];

        
        $update = $pdo->prepare("UPDATE prestataire SET nom = ?, ville = ?, description = ?, iban = ? WHERE id_prestataire = ?");
        $update->execute([$final_nom, $final_ville, $final_desc, $final_iban, $id_pres]);

       
        header('Location: dashboardP.php?profil=ok#profil');
        exit();

    } catch (PDOException $e) {

        header('Location: dashboardP.php?error=sql#profil');
        exit();
    }
}

// Redirection par défaut si on accède à la page sans POST
header('Location: dashboardP.php#profil');
exit();
