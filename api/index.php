<?php
header("Content-Type: application/json");

require_once __DIR__ . "/admin.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/seniors.php";
require_once __DIR__ . "/paiements.php";

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if (strpos($uri, "/login") !== false && $method === "POST") { 
    login(); 
    exit; 
    }
if (strpos($uri, "/register/senior") !== false && $method === "POST") { 
    registerSenior(); 
    exit; 
    }
if (strpos($uri, "/register/prestataire") !== false && $method === "POST") { 
    registerPrestataire(); 
    exit; 
    }
if (strpos($uri, "/forgot-password") !== false && $method === "POST") { 
    forgotPassword(); 
    exit; 
    }
if (strpos($uri, "/admin/seniors") !== false && $method === "GET") { 
    lister_seniors(); 
    exit; 
    }
if (strpos($uri, "/admin/prestataires") !== false && $method === "GET") { 
    lister_prestataires(); 
    exit; 
    }

if (strpos($uri, "/seniors/me") !== false && $method === "GET") {
    get_profil_senior();
    exit;
}
if (strpos($uri, "/seniors/me") !== false && $method === "PUT") {
    modifier_profil_senior();
    exit;
}
if (strpos($uri, "/paiements/creer") !== false && $method === "POST") {
    creer_paiement();
    exit;
}

http_response_code(404);
echo json_encode(["message" => "Route non trouvée"]);
