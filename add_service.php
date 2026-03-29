<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'prestataire') {
    $id_pres = $_SESSION['id'];
    $nom_service = htmlspecialchars($_POST['nom_service'] ?? ''); 
    $prix = floatval($_POST['prix'] ?? 0);
    $ville = htmlspecialchars($_POST['ville'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO services (id_prestataire, nom_service, prix, ville, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_pres, $nom_service, $prix, $ville, $description]);
        
        header('Location: dashboardP.php?msg=service_ajoute#services');
        exit();
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}

header('Location: dashboardP.php');
exit();
