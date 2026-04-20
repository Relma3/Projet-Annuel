<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['id']) || $_SESSION['type'] != 'senior') {
    header('Location: dashboardS.php');
    exit();
}

$id_senior = $_SESSION['id'];
$telephone = $_POST['telephone'] ?? '';
$adresse = $_POST['adresse'] ?? '';
$prenom = $_POST['prenom'] ?? null;
$nom = $_POST['nom'] ?? null;

try {
    $stmt = $pdo->prepare("
        UPDATE senior
        SET telephone = ?, adresse = ?, prenom = COALESCE(?, prenom), nom = COALESCE(?, nom)
        WHERE id_senior = ?
    ");
    $stmt->execute([$telephone, $adresse, $prenom, $nom, $id_senior]);

    header('Location: dashboardS.php?msg=profil_mis_a_jour#profil');
} catch (PDOException $e) {
    header('Location: dashboardS.php?err=update_failed#profil');
}

exit();