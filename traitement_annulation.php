<?php
require_once 'check_session.php';
require_once 'db_connect.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

$id_reservation = $_POST['id_reservation'];

if ($id_reservation) {
    $db = getDB();

    $stmt = $db->prepare("UPDATE reservation SET statut = 'annule' WHERE id_reservation = ? AND id_senior = ?");
    $stmt->execute([$id_reservation, $_SESSION['id']]);
}

header('Location: planning.php?annule=1');
exit;