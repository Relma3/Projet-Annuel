<?php

define('CLE_JWT', 'silverhappy_cle_secrete');


function generer_token($id_utilisateur, $type_utilisateur) {
    $entete = base64_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload = base64_encode(json_encode([
        'id_utilisateur' => $id_utilisateur,
        'type_utilisateur' => $type_utilisateur,
        'expiration' => time() + 24 * 60 * 60 // 24h
    ]));

    $signature = base64_encode(
        hash_hmac('sha256', $entete . "." . $payload, CLE_JWT, true)
    );

    return $entete . "." . $payload . "." . $signature;
}


function verifier_token() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $parties = explode('.', $token);

    if (count($parties) !== 3) {
        http_response_code(401);
        echo json_encode(["message" => "Token invalide"]);
        exit;
    }

    $payload = json_decode(base64_decode($parties[1]), true);

    if ($payload['expiration'] < time()) {
        http_response_code(401);
        echo json_encode(["message" => "Token expiré"]);
        exit;
    }

    return $payload;
}


function verifier_admin() {
    $donnees = verifier_token();

    if ($donnees['type_utilisateur'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Accès refusé (admin uniquement)"]);
        exit;
    }

    return $donnees;
}