<?php
require_once '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $categorie = $_POST['categorie'];
    $auteur = $_POST['auteur'];

    $stmt = $pdo->prepare("
        INSERT INTO conseil (titre, contenu, categorie, auteur, visible)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([$titre, $contenu, $categorie, $auteur]);

    header("Location: admin_conseils.php");
}
?>

<h1>Ajouter un conseil</h1>

<form method="POST">
    <input type="text" name="titre" placeholder="Titre"><br><br>
    <textarea name="contenu" placeholder="Contenu"></textarea><br><br>
    <input type="text" name="categorie" placeholder="Catégorie"><br><br>
    <input type="text" name="auteur" placeholder="Auteur"><br><br>

    <button type="submit">Publier</button>
</form>