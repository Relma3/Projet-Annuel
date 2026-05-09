<?php
/** api/evenements.php — Inscription et désinscription aux événements (senior) */
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: /connexion.php');
    exit();
}

$id_senior    = (int)$_SESSION['id'];
$id_evenement = (int)($_GET['id'] ?? 0);
$action       = $_GET['action'] ?? '';

if (!$id_evenement || !in_array($action, ['inscrire', 'annuler'])) {
    header('Location: /evenements.php');
    exit();
}

if ($action === 'inscrire') {
    // Vérifier places restantes
    $places = $pdo->prepare("SELECT nombre_places - COUNT(ie.id_inscription) AS restantes
        FROM evenements e
        LEFT JOIN inscription_evenement ie ON ie.id_evenement = e.id
        WHERE e.id = ? AND e.date_debut >= NOW()
        GROUP BY e.id");
    $places->execute([$id_evenement]);
    $row = $places->fetch();

    if (!$row || $row['restantes'] <= 0) {
        header('Location: /evenements.php?msg=Événement complet ou introuvable');
        exit();
    }

    // Vérifier doublon
    $check = $pdo->prepare("SELECT id_inscription FROM inscription_evenement WHERE id_senior = ? AND id_evenement = ?");
    $check->execute([$id_senior, $id_evenement]);
    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO inscription_evenement (id_senior, id_evenement) VALUES (?, ?)")
            ->execute([$id_senior, $id_evenement]);
    }

    header('Location: /evenements.php?msg=Inscription confirmée !');

} else {
    $pdo->prepare("DELETE FROM inscription_evenement WHERE id_senior = ? AND id_evenement = ?")
        ->execute([$id_senior, $id_evenement]);

    header('Location: /evenements.php?msg=Désinscription effectuée.');
}
exit();