<?php

if (file_exists(__DIR__ . '/../.env.php')) require_once __DIR__ . '/../.env.php';
define('CLE_JWT', getenv('JWT_SECRET') ?: 'silverhappy_cle_secrete_fallback');


function generer_token($id_utilisateur, $type_utilisateur) {
    $entete = base64_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload = base64_encode(json_encode([
        'id_utilisateur' => $id_utilisateur,
        'type_utilisateur' => $type_utilisateur,
        'expiration' => time() + 24 * 60 * 60
    ]));

    $signature = base64_encode(
        hash_hmac('sha256', $entete . "." . $payload, CLE_JWT, true)
    );

    return $entete . "." . $payload . "." . $signature;
}


function verifier_token() {
    $headers = getallheaders();
    $auth = "";

    if (isset($headers["Authorization"])) {
        $auth = $headers["Authorization"];
    } elseif (isset($headers["authorization"])) {
        $auth = $headers["authorization"];
    } elseif (isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $auth = $_SERVER["HTTP_AUTHORIZATION"];
    } elseif (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
    $auth = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
    }

    if (!$auth) {
        http_response_code(401);
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $auth);
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