<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function getDashboardLink() {
    if (isset($_SESSION['id']) && isset($_SESSION['type']) && $_SESSION['type'] == 'senior') {
        return 'dashboardS.php';
    }

    return 'connexion.php';
}

$is_connected = isset($_SESSION['id']);

function callAPI($method, $route, $data = []) {
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api';
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

    $result = json_decode($response, true);

    if ($result) {
        return $result;
    }

    return [];
}