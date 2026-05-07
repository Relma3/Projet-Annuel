<?php
// Mmina
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

if (!function_exists('getDB')) {
    require_once __DIR__ . "/config/database.php";
}
require_once __DIR__ . "/middleware.php";

function lister_seniors() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT id_utilisateur, email, type_utilisateur, created_at FROM utilisateur WHERE type_utilisateur = 'senior'");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function lister_prestataires() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT id_utilisateur, email, type_utilisateur, created_at FROM utilisateur WHERE type_utilisateur = 'prestataire'");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function lister_categories() {
    verifier_admin();
    $pdo = getDB();
    $result = $pdo->query("SELECT * FROM categories_prestations");
    echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
}

function creer_categorie() {
    verifier_admin();
    $pdo  = getDB();
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO categories_prestations (nom, description) VALUES (?, ?)");
    $stmt->execute([$data['nom'], $data['description'] ?? '']);
    echo json_encode(["message" => "Categorie creee"]);
}

function supprimer_categorie($id) {
    verifier_admin();
    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM categories_prestations WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["message" => "Categorie supprimee"]);
}

function lister_evenements() {
    verifier_admin();
    $pdo    = getDB();
    $result = $pdo->query("SELECT * FROM evenements");
    echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
}

function creer_evenement() {
    verifier_admin();
    $pdo  = getDB();
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, lieu, nombre_places) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['titre'], $data['date_debut'], $data['lieu'], $data['nombre_places']]);
    echo json_encode(["message" => "Evenement cree"]);
}

function supprimer_evenement($id) {
    verifier_admin();
    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["message" => "Evenement supprime"]);
}

function stats_financieres() {
    verifier_admin();
    $pdo = getDB();

    $ca          = $pdo->query("SELECT COALESCE(SUM(montant_cents)/100, 0) FROM paiements WHERE statut = 'reussi'")->fetchColumn();
    $commissions = $pdo->query("SELECT COALESCE(SUM(commission_sh), 0) FROM reservation WHERE commission_sh IS NOT NULL")->fetchColumn();
    $seniors     = $pdo->query("SELECT COUNT(*) FROM abonnement WHERE statut = 'actif'")->fetchColumn();
    $prestas     = $pdo->query("SELECT COUNT(*) FROM prestataire WHERE abonnement_statut = 'actif'")->fetchColumn();
    $paiements   = $pdo->query("
        SELECT p.*, COALESCE(CONCAT(s.prenom, ' ', s.nom), 'Inconnu') AS nom_payeur
        FROM paiements p
        LEFT JOIN senior s ON p.id_payeur = s.id_senior
        WHERE p.statut = 'reussi'
        ORDER BY p.date_paiement DESC
        LIMIT 20
    ")->fetchAll();

    echo json_encode([
        'ca_total'     => (float)$ca,
        'commissions'  => (float)$commissions,
        'seniors'      => (int)$seniors,
        'prestataires' => (int)$prestas,
        'paiements'    => $paiements,
    ]);
}

if (isset($_GET['action'])) {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    $id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

    switch ($action) {
        case 'seniors':
            lister_seniors();
            break;

        case 'prestataires':
            lister_prestataires();
            break;

        case 'categories':
            if ($method === 'GET')                   { lister_categories(); }
            elseif ($method === 'POST')              { creer_categorie(); }
            elseif ($method === 'DELETE' && $id)     { supprimer_categorie($id); }
            else { http_response_code(405); echo json_encode(['erreur' => 'Methode non autorisee']); }
            break;

        case 'evenements':
            if ($method === 'GET')                   { lister_evenements(); }
            elseif ($method === 'POST')              { creer_evenement(); }
            elseif ($method === 'DELETE' && $id)     { supprimer_evenement($id); }
            else { http_response_code(405); echo json_encode(['erreur' => 'Methode non autorisee']); }
            break;

        case 'stats':
            stats_financieres();
            break;

        default:
            http_response_code(404);
            echo json_encode(['erreur' => 'Action inconnue : ' . $action]);
    }
}