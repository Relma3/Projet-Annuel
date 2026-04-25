<?php
header("Content-Type: application/json");

// CORS (utile si tu fais du frontend après)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Gestion preflight (important pour POST/PUT en JS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getArticles($pdo);
        break;

    case 'POST':
        addArticle($pdo);
        break;

    case 'PUT':
        updateArticle($pdo);
        break;

    case 'DELETE':
        deleteArticle($pdo);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}

// 🔹 GET - récupérer tous les articles
function getArticles($pdo) {
    $stmt = $pdo->query("SELECT * FROM article ORDER BY id_article DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}

// 🔹 POST - ajouter un article
function addArticle($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['nom']) || empty($data['description'])) {
        http_response_code(400);
        echo json_encode(["error" => "Champs requis : nom, description"]);
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO article (nom, description, prix, categorie, photo, disponible)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['nom'],
        $data['description'],
        $data['prix'] ?? 0,
        $data['categorie'] ?? null,
        $data['photo'] ?? null,
        $data['disponible'] ?? 1
    ]);

    http_response_code(201);
    echo json_encode(["success" => "Article ajouté"]);
}

// 🔹 PUT - modifier un article
function updateArticle($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id_article'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID requis"]);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE article 
        SET nom = ?, description = ?, prix = ?, categorie = ?, photo = ?, disponible = ?
        WHERE id_article = ?
    ");

    $stmt->execute([
        $data['nom'],
        $data['description'],
        $data['prix'] ?? 0,
        $data['categorie'] ?? null,
        $data['photo'] ?? null,
        $data['disponible'] ?? 1,
        $data['id_article']
    ]);

    echo json_encode(["success" => "Article modifié"]);
}

// 🔹 DELETE - supprimer un article
function deleteArticle($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id_article'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID requis"]);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM article WHERE id_article = ?");
    $stmt->execute([$data['id_article']]);

    echo json_encode(["success" => "Article supprimé"]);
}
?>