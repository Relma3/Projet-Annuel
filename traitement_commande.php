<?php
/** traitement_commande.php — Traitement des commandes boutique */
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: boutique.php');
    exit;
}

require_once 'db_connect.php';
$db = getDB();

$id_article  = isset($_POST['id_article']) ? (int)$_POST['id_article'] : null;
$nom_article = isset($_POST['nom_article']) ? trim($_POST['nom_article']) : null;
$prix        = isset($_POST['prix']) ? (float)$_POST['prix'] : 0;


if ($id_article) {
    $stmt = $db->prepare("SELECT nom, prix FROM article WHERE id_article = ? AND disponible = 1");
    $stmt->execute([$id_article]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        header('Location: boutique.php?erreur=article_introuvable');
        exit;
    }

    $nom_article = $article['nom'];
    $prix        = (float)$article['prix'];
}

// Vérif
if (!$nom_article || $prix <= 0) {
    header('Location: boutique.php?erreur=donnees_invalides');
    exit;
}

// INSERT en BDD
$stmt = $db->prepare("
    INSERT INTO commandes (id_senior, id_article, nom_article, prix, statut)
    VALUES (?, ?, ?, ?, 'en_attente')
");

$stmt->execute([
    $_SESSION['id'],   
    $id_article,       
    $nom_article,
    $prix
]);

header('Location: boutique.php?commande=1');
exit;