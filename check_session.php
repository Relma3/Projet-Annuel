<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDashboardLink(): string {
    if (!isset($_SESSION['id'], $_SESSION['type'])) {
        return 'connexion.php';
    }
    return match($_SESSION['type']) {
        'senior'      => 'dashboardS.php',
        'prestataire' => 'dashboardP.php',
        'admin'       => 'frontend/admin/dashboard.php',
        default       => 'connexion.php'
    };
}

$is_connected = false;

if (isset($_SESSION['id'])) {
    // Vérifie que l'utilisateur existe encore en BDD et est actif
    require_once __DIR__ . '/db_connect.php';
    $stmt = $pdo->prepare("
        SELECT id_utilisateur, est_actif, type_utilisateur 
        FROM utilisateur 
        WHERE id_utilisateur = ?
    ");
    $stmt->execute([$_SESSION['id']]);
    $user = $stmt->fetch();

    if (!$user || $user['est_actif'] == 0) {
        // Utilisateur supprimé ou désactivé → on détruit la session
        session_unset();
        session_destroy();
        header('Location: connexion.php?session=expire');
        exit();
    }

    $is_connected = true;
}

function callAPI(string $method, string $route, array $data = []): array {
    $baseUrl = 'http://localhost/PA_2EME_ANNEE/api';
    $ch = curl_init($baseUrl . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $headers = ['Content-Type: application/json'];
    if (isset($_SESSION['jwt_token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['jwt_token'];
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}