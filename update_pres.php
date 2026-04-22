<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $nom = htmlspecialchars($_POST['nom']);
    $ville = htmlspecialchars($_POST['ville']);
    $desc = htmlspecialchars($_POST['description']);
    $id_pres = $_SESSION['id'];

    try {
        $stmt = $pdo->prepare("UPDATE prestataire SET nom = ?, ville = ?, description = ? WHERE id_prestataire = ?");
        $stmt->execute([$nom, $ville, $desc, $id_pres]);
    } catch (PDOException $e) {
        
    }
}

header('Location: dashboardP.php#profil');
exit();
