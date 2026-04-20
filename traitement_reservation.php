<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: services.php');
    exit;
}

$id_prestataire = isset($_POST['id_prestataire']) ? (int)$_POST['id_prestataire'] : 0;
$date_rdv = $_POST['date_rdv'] ?? '';
$heure_rdv = $_POST['heure_rdv'] ?? '';
$commentaire = $_POST['commentaire'] ?? '';

if (!$id_prestataire || !$date_rdv || !$heure_rdv) {
    header('Location: reservation.php?id=' . $id_prestataire . '&erreur=champs_manquants');
    exit;
}


$check = $pdo->prepare("SELECT id_prestataire FROM prestataire WHERE id_prestataire = ? AND statut = 'valide'");
$check->execute([$id_prestataire]);

if (!$check->fetch()) {
    header('Location: services.php?erreur=prestataire_invalide');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut) VALUES (?, ?, ?, ?, 'en_attente')");
    $stmt->execute([
        $_SESSION['id'],
        $id_prestataire,
        $date_rdv . ' ' . $heure_rdv . ':00',
        $commentaire
    ]);

    @file_get_contents(
        'http://go-api:8080/api/notifications',
        false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode([
                    'message' => 'Votre reservation a bien ete envoyee',
                    'id_senior' => $_SESSION['id']
                ])
            ]
        ])
    );

    header('Location: planning.php?reservation=1');
    exit;
} catch (PDOException $e) {
    header('Location: reservation.php?id=' . $id_prestataire . '&erreur=erreur_serveur');
    exit;
}