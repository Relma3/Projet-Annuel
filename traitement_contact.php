<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

if (!$nom || !$email || !$message) {
    header('Location: contact.php?erreur=champs_manquants');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO message_contact (nom, email, message, date_envoi) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$nom, $email, $message]);
    header('Location: contact.php?succes=1');
} catch (Exception $e) {
    header('Location: contact.php?erreur=serveur');
}
exit;