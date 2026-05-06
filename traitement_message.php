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

$stmt = $db->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, lu) VALUES (?, 2, ?, 0)");
$stmt->execute([$_SESSION['id'], $contenu]);

require_once __DIR__ . '/includes/send_notification.php';
envoyerNotification($pdo, 2, 'Nouveau message', 'Vous avez reçu un nouveau message sur Silver Happy.', 'info');

header('Location: messagerie.php?envoye=1');
exit;