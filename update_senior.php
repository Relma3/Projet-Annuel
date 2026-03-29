<?php
session_start();
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && $_SESSION['type'] === 'senior') {
    $id_senior = $_SESSION['id'];
    $telephone = htmlspecialchars($_POST['telephone']);
    $ville = htmlspecialchars($_POST['ville']);
    $adresse = htmlspecialchars($_POST['adresse']);

    try {
        $stmt = $pdo->prepare("UPDATE senior SET telephone = ?, ville = ?, adresse = ? WHERE id_senior = ?");
        $stmt->execute([$telephone, $ville, $adresse, $id_senior]);

        header('Location: dashboardS.php?msg=profil_mis_a_jour#profil');
    } catch (PDOException $e) {

        header('Location: dashboardS.php?err=update_failed#profil');
    }
} else {

    header('Location: dashboardS.php');
}
exit();
