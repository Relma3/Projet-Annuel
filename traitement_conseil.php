<?php
require_once 'db_connect.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $categorie = $_POST['categorie'];
    $auteur = $_POST['auteur'];
$stmt = $pdo->prepare("
INSERT INTO conseil (titre, contenu, categorie, auteur, visible, created_at)
VALUES (?, ?, ?, ?, 1, NOW())
"); 
 $stmt->execute([$titre, $contenu, $categorie, $auteur]);

    header('Location: /frontend/admin/admin_conseils.php');
    exit();
}

if ($action === 'delete') {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM conseil WHERE id_conseil = ?");
    $stmt->execute([$id]);

    header('Location: /frontend/admin/admin_conseils.php');
    exit();
}