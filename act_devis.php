<?php
/** act_devis.php — Acceptation ou refus d'un devis par le senior */
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_devis = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id_senior = $_SESSION['id'];

if ($id_devis > 0 && in_array($action, ['accepter', 'refuser'])) {
    try {
        $pdo->beginTransaction();

        $stmtGet = $pdo->prepare("SELECT id_reservation FROM devis WHERE id_devis = ? AND id_senior = ?");
        $stmtGet->execute([$id_devis, $id_senior]);
        $devis = $stmtGet->fetch();

        if ($devis) {
            $id_res = $devis['id_reservation'];

            if ($action === 'accepter') {
                // Ne pas changer le statut ici, Stripe le fera via webhook
                header("Location: payer_devis.php?id=$id_devis");
                exit();
            }
            elseif ($action === 'refuser') {
              
                $pdo->prepare("UPDATE devis SET statut = 'refuse' WHERE id_devis = ?")->execute([$id_devis]);
                $pdo->prepare("UPDATE reservation SET statut = 'annule' WHERE id_reservation = ?")->execute([$id_res]);
                
           
                $pdo->prepare("UPDATE disponibilites SET type = 'libre', id_reservation = NULL WHERE id_reservation = ?")->execute([$id_res]);
                $msg = 'devis_refuse';
            }

            $pdo->commit();
            header("Location: dashboardS.php?msg=$msg#planning");
            exit();
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}

header('Location: dashboardS.php#planning');
exit();
