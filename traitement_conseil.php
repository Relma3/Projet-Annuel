<?php
require_once 'db_connect.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];

$stmt = $pdo->prepare("
    INSERT INTO conseil (titre, contenu, visible, created_at)
    VALUES (?, ?, 1, NOW())
");    $stmt->execute([$titre, $contenu]);

    header('Location: admin_conseils.php');
    exit();
}

if ($action === 'delete') {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM conseil WHERE id_conseil = ?");
    $stmt->execute([$id]);

    header('Location: admin_conseils.php');
    exit();
}