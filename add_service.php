<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $id_pres = $_SESSION['id'];
    $nom_service = htmlspecialchars($_POST['nom_service'] ?? ''); 
    $prix = floatval($_POST['prix'] ?? 0);
    $ville = htmlspecialchars($_POST['ville'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $debut = $_POST['debut'] ?? '';
    $fin = $_POST['fin'] ?? '';

    if ($prix <= 0) {
        header('Location: dashboardP.php?error=prix_invalide#services');
        exit();
    }

    if (strlen($description) < 10) {
        header('Location: dashboardP.php?error=desc_courte#services');
        exit();
    }

    $time_debut = strtotime($debut);
    $time_fin = strtotime($fin);
    $now = time();

    if ($time_debut < $now || $time_fin <= $time_debut) {
        header('Location: dashboardP.php?error=dates_invalides#services');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmtSrv = $pdo->prepare("INSERT INTO services (id_prestataire, nom_service, prix, ville, description) VALUES (?, ?, ?, ?, ?)");
        $stmtSrv->execute([$id_pres, $nom_service, $prix, $ville, $description]);
        $id_service = $pdo->lastInsertId();

        $stmtDispo = $pdo->prepare("INSERT INTO disponibilites (id_prestataire, id_service, date_debut, date_fin, type) VALUES (?, ?, ?, ?, 'libre')");
        $stmtDispo->execute([$id_pres, $id_service, $debut, $fin]);

        $pdo->commit();
        header('Location: dashboardP.php?msg=service_ajoute#services');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: dashboardP.php?error=sql#services');
        exit();
    }
}

header('Location: dashboardP.php');
exit();
