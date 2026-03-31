<?php

if (file_exists(__DIR__ . '/../.env.php')) {
    require_once __DIR__ . '/../.env.php';
}

define('CLE_JWT', getenv('JWT_SECRET') ?: 'silverhappy_cle_secrete');

function generer_token($id_utilisateur, $type_utilisateur) {
    $entete = base64_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload = base64_encode(json_encode([
        'id_utilisateur' => $id_utilisateur,
        'type_utilisateur' => $type_utilisateur,
        'expiration' => time() + 86400
    ]));

    $signature = base64_encode(hash_hmac('sha256', $entete . "." . $payload, CLE_JWT, true));

    return $entete . "." . $payload . "." . $signature;
}

function verifier_token() {
    $headers = getallheaders();
    $auth = "";

    if (isset($headers["Authorization"])) {
        $auth = $headers["Authorization"];
    }

    if (!$auth && isset($headers["authorization"])) {
        $auth = $headers["authorization"];
    }

    if (!$auth && isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $auth = $_SERVER["HTTP_AUTHORIZATION"];
    }

    if (!$auth) {
        http_response_code(401);
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $auth);
    $parties = explode(".", $token);

    if (count($parties) != 3) {
        http_response_code(401);
        echo json_encode(["message" => "Token invalide"]);
        exit;
    }

    $payload = json_decode(base64_decode($parties[1]), true);

    if ($payload["expiration"] < time()) {
        http_response_code(401);
        echo json_encode(["message" => "Token expire"]);
        exit;
    }

    return $payload;
}

function verifier_admin() {
    $donnees = verifier_token();

    if ($donnees["type_utilisateur"] != "admin") {
        http_response_code(403);
        echo json_encode(["message" => "Acces refuse"]);
        exit;
    }

    return $donnees;
}
