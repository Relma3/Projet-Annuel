<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

$id_res = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['a']) ? $_GET['a'] : '';

if ($id_res > 0 && !empty($action)) {
    $nouveau_statut = '';

    if ($action === 'accepter') {
        $nouveau_statut = 'confirme';
    } elseif ($action === 'refuser') {
        $nouveau_statut = 'annule';
    } elseif ($action === 'terminer') {
        $nouveau_statut = 'termine';
    }

    if (!empty($nouveau_statut)) {
        try {
            $pdo->beginTransaction();

            $stmtGet = $pdo->prepare("SELECT * FROM reservation WHERE id_reservation = ? AND id_prestataire = ?");
            $stmtGet->execute([$id_res, $_SESSION['id']]);
            $reservation = $stmtGet->fetch();

            if ($reservation) {
                $pdo->prepare("UPDATE reservation SET statut = ? WHERE id_reservation = ?")->execute([$nouveau_statut, $id_res]);

                if ($nouveau_statut === 'confirme' && $reservation['id_disponibilite']) {
                    $stmtDispo = $pdo->prepare("SELECT * FROM disponibilites WHERE id_disponibilite = ?");
                    $stmtDispo->execute([$reservation['id_disponibilite']]);
                    $dispo = $stmtDispo->fetch();

                    if ($dispo) {
                        $pdo->prepare("UPDATE disponibilites SET type = 'reserve', id_reservation = ? WHERE id_disponibilite = ?")->execute([$id_res, $dispo['id_disponibilite']]);

                        if ($dispo['date_debut'] < $reservation['date_reservation']) {
                            $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")->execute([$reservation['id_prestataire'], $dispo['date_debut'], $reservation['date_reservation']]);
                        }

                        if ($dispo['date_fin'] > $reservation['date_fin']) {
                            $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")->execute([$reservation['id_prestataire'], $reservation['date_fin'], $dispo['date_fin']]);
                        }
                    }
                }

                if ($nouveau_statut === 'annule' && $reservation['id_disponibilite']) {
                    $pdo->prepare("UPDATE disponibilites SET type = 'libre', id_reservation = NULL WHERE id_reservation = ? AND type = 'reserve'")->execute([$id_res]);
                }
            }

            $pdo->commit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            die("Erreur : " . $e->getMessage());
        }
    }
}

header('Location: dashboardP.php#planning');
exit();
