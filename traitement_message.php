<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: messagerie.php');
    exit;
}

$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

if ($contenu == '') {
    header('Location: messagerie.php?erreur=message_vide');
    exit;
}

require_once 'db_connect.php';

$db = getDB();

$stmt = $db->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, lu) VALUES (?, 4, ?, 0)");
$stmt->execute([$_SESSION['id'], $contenu]);

header('Location: messagerie.php?envoye=1');
exit;