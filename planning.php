<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}
header('Location: dashboardS.php#planning');
exit();