<?php

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: contact.php');
    exit;
}

$nom = isset($_POST['nom']) ? $_POST['nom'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

if (!$nom || !$email || !$message) {
    header('Location: contact.php?erreur=champs_manquants');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO message_contact (nom, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $email, $message]);
    header('Location: contact.php?succes=1');
} catch (Exception $e) {
    header('Location: contact.php?erreur=serveur');
}

exit;