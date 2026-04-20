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

$contenu = isset($_POST['contenu']) ? $_POST['contenu'] : '';

if ($contenu == '') {
    header('Location: messagerie.php?erreur=message_vide');
    exit;
}

require_once 'db_connect.php';

$db = getDB();

$stmtAdmin = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE type_utilisateur = 'admin' LIMIT 1");
$stmtAdmin->execute();
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

$id_admin = 1;
if ($admin) {
    $id_admin = $admin['id_utilisateur'];
}

$stmt = $db->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, lu) VALUES (?, ?, ?, 0)");
$stmt->execute([$_SESSION['id'], $id_admin, $contenu]);

header('Location: messagerie.php?envoye=1');
exit;