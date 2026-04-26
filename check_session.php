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
        'admin'       => 'dashboardAdmin.php',
        default       => 'connexion.php'
    };
}
 
$is_connected = isset($_SESSION['id']);
 
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
 
