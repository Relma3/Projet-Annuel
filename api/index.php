<?php

header("Content-Type: application/json");

// CORS : on autorise uniquement les origines connues
$allowed = ["http://51.210.12.40", "https://51.210.12.40", "http://localhost", "http://127.0.0.1"];
$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    //
    header("Access-Control-Allow-Origin: http://51.210.12.40");
}
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit;
}

require_once __DIR__ . "/admin.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/seniors.php";
require_once __DIR__ . "/paiements.php";
require_once __DIR__ . "/facturation.php";
require_once __DIR__ . "/rdv.php";
require_once __DIR__ . "/devis.php";
require_once __DIR__ . "/evaluations.php";

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

// Auth
if ($uri == "/api/login" && $method == "POST") { login(); exit; }
if ($uri == "/api/register/senior" && $method == "POST") { registerSenior(); exit; }
if ($uri == "/api/register/prestataire" && $method == "POST") { registerPrestataire(); exit; }
if ($uri == "/api/forgot-password" && $method == "POST") { forgotPassword(); exit; }

// Seniors
if ($uri == "/api/seniors/me" && $method == "GET") { get_profil_senior(); exit; }
if ($uri == "/api/seniors/me" && $method == "PUT") { modifier_profil_senior(); exit; }
if ($uri == "/api/seniors/conseils" && $method == "GET") { get_conseils_senior(); exit; }
if ($uri == "/api/seniors/reservations" && $method == "GET") { mes_reservations_senior(); exit; }
if ($uri == "/api/seniors/commandes" && $method == "GET") { mes_commandes_senior(); exit; }
if ($uri == "/api/seniors/abonnement" && $method == "GET") { mon_abonnement(); exit; }
if ($uri == "/api/seniors/notifications" && $method == "GET") { mes_notifications(); exit; }

// RDV
if ($uri == "/api/rdv/mes-rdv" && $method == "GET") { mes_rdv_medicaux(); exit; }
if ($uri == "/api/rdv/prendre" && $method == "POST") { prendre_rdv(); exit; }
if (preg_match("#^/api/rdv/([0-9]+)/annuler$#", $uri, $m) && $method == "PUT") { annuler_rdv($m[1]); exit; }

// Devis
if ($uri == "/api/senior/devis" && $method == "GET") { mes_devis_senior(); exit; }
if ($uri == "/api/prestataire/devis" && $method == "GET") { mes_devis_prestataire(); exit; }
if ($uri == "/api/devis/creer" && $method == "POST") { creer_devis(); exit; }
if (preg_match("#^/api/devis/([0-9]+)/accepter$#", $uri, $m) && $method == "PUT") { accepter_devis($m[1]); exit; }
if (preg_match("#^/api/devis/([0-9]+)/refuser$#", $uri, $m) && $method == "PUT") { refuser_devis($m[1]); exit; }

// Evaluations
if ($uri == "/api/evaluations/creer" && $method == "POST") { creer_evaluation(); exit; }
if (preg_match("#^/api/evaluations/prestataire/([0-9]+)$#", $uri, $m) && $method == "GET") { evaluations_prestataire($m[1]); exit; }
if ($uri == "/api/admin/evaluations" && $method == "GET") { admin_lister_evaluations(); exit; }
if ($uri == "/api/admin/evaluations/stats" && $method == "GET") { admin_stats_evaluations(); exit; }

// Paiements
if ($uri == "/api/paiements/creer" && $method == "POST") { creer_paiement(); exit; }
if ($uri == "/api/paiements/abonnement" && $method == "POST") { souscrire_abonnement(); exit; }
if ($uri == "/api/webhook" && $method == "POST") { stripe_webhook(); exit; }

// Facturation prestataire
if ($uri == "/api/prestataire/facturation/mes-factures" && $method == "GET") { mes_factures_prestataire(); exit; }
if ($uri == "/api/prestataire/facturation/telecharger" && $method == "GET") { telecharger_ma_facture(); exit; }

// Admin utilisateurs
if ($uri == "/api/admin/seniors" && $method == "GET") { lister_seniors(); exit; }
if ($uri == "/api/admin/seniors" && $method == "POST") { creer_senior(); exit; }
if ($uri == "/api/admin/prestataires" && $method == "GET") { lister_prestataires(); exit; }
if (preg_match("#^/api/admin/prestataires/([0-9]+)/valider$#", $uri, $m) && $method == "PUT") { valider_prestataire($m[1]); exit; }

// Admin contenu
if ($uri == "/api/admin/categories" && $method == "GET") { lister_categories(); exit; }
if ($uri == "/api/admin/categories" && $method == "POST") { creer_categorie(); exit; }
if (preg_match("#^/api/admin/categories/([0-9]+)$#", $uri, $m) && $method == "DELETE") { supprimer_categorie($m[1]); exit; }
if ($uri == "/api/admin/evenements" && $method == "GET") { lister_evenements(); exit; }
if ($uri == "/api/admin/evenements" && $method == "POST") { creer_evenement(); exit; }
if (preg_match("#^/api/admin/evenements/([0-9]+)$#", $uri, $m) && $method == "DELETE") { supprimer_evenement($m[1]); exit; }
if ($uri == "/api/admin/articles" && $method == "GET") { lister_articles(); exit; }
if ($uri == "/api/admin/articles" && $method == "POST") { creer_article(); exit; }
if (preg_match("#^/api/admin/articles/([0-9]+)$#", $uri, $m) && $method == "PUT") { modifier_article($m[1]); exit; }
if (preg_match("#^/api/admin/articles/([0-9]+)$#", $uri, $m) && $method == "DELETE") { supprimer_article($m[1]); exit; }
if ($uri == "/api/admin/conseils" && $method == "GET") { lister_conseils(); exit; }
if ($uri == "/api/admin/conseils" && $method == "POST") { creer_conseil(); exit; }
if (preg_match("#^/api/admin/conseils/([0-9]+)$#", $uri, $m) && $method == "DELETE") { supprimer_conseil($m[1]); exit; }

// Admin RDV
if ($uri == "/api/admin/rdv" && $method == "GET") { admin_lister_rdv_anonymises(); exit; }
if ($uri == "/api/admin/rdv/stats" && $method == "GET") { admin_stats_rdv(); exit; }

// Admin devis et facturation
if ($uri == "/api/admin/devis" && $method == "GET") { admin_lister_devis(); exit; }
if ($uri == "/api/admin/facturation/generer" && $method == "POST") { facturation_mensuelle_tous(); exit; }
if ($uri == "/api/admin/facturation/liste" && $method == "GET") { lister_factures_archivees(); exit; }
if (preg_match("#^/api/admin/facturation/prestataire/([0-9]+)$#", $uri, $m) && $method == "POST") { telecharger_facture($m[1]); exit; }

http_response_code(404);
echo json_encode(["message" => "Route non trouvée"]);