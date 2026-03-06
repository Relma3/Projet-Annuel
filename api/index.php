<?php
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

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

if (strpos($uri, "/admin/seniors") !== false && $method === "POST") {
    creer_senior();
    exit;
}

// categories
if (strpos($uri, "/admin/categories") !== false && $method === "GET") {
    lister_categories();
    exit;
}

if (strpos($uri, "/admin/categories") !== false && $method === "POST") {
    creer_categorie();
    exit;
}

if (preg_match("#/admin/categories/([0-9]+)#", $uri, $m) && $method === "DELETE") {
    supprimer_categorie($m[1]);
    exit;
}


// evenements
if (strpos($uri, "/admin/evenements") !== false && $method === "GET") {
    lister_evenements();
    exit;
}

if (strpos($uri, "/admin/evenements") !== false && $method === "POST") {
    creer_evenement();
    exit;
}

if (preg_match("#/admin/evenements/([0-9]+)#", $uri, $m) && $method === "DELETE") {
    supprimer_evenement($m[1]);
    exit;
}

http_response_code(404);
echo json_encode(["message" => "Route non trouvée"]);
