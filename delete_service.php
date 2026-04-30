<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

$id_service = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_prestataire = $_SESSION['id'];

if ($id_service > 0) {
    try {
        $pdo->beginTransaction();

        $stmtDispos = $pdo->prepare("DELETE FROM disponibilites WHERE id_service = ? AND id_prestataire = ?");
        $stmtDispos->execute([$id_service, $id_prestataire]);

        $stmtService = $pdo->prepare("DELETE FROM services WHERE id_service = ? AND id_prestataire = ?");
        $stmtService->execute([$id_service, $id_prestataire]);

        $pdo->commit();
        header('Location: dashboardP.php?msg=service_supprime#services');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: dashboardP.php?error=sql#services');
        exit();
    }
}

header('Location: dashboardP.php#services');
exit();
