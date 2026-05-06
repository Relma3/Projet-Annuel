<?php
session_start();
header("Content-Type: application/json");


// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/log.php';

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

    case 'PATCH':
        toggleArticle($pdo);
        break;

    case 'DELETE':
        deleteArticle($pdo);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}

//  GET (liste ou un seul article)
function getArticles($pdo) {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM article WHERE id_article = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                http_response_code(404);
                echo json_encode(["error" => "Article non trouvé"]);
                return;
            }

        } else {
            $stmt = $pdo->query("SELECT * FROM article ORDER BY id_article DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($data);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur serveur"]);
    }
}


//  POST (ajouter)
function addArticle($pdo) {
    try {
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
            htmlspecialchars($data['nom']),
            htmlspecialchars($data['description']),
            $data['prix'] ?? 0,
            $data['categorie'] ?? null,
            $data['photo'] ?? null,
            $data['disponible'] ?? 1
        ]);

      $email = $_SESSION['email'] ?? 'inconnu';

addLog($email, "Ajout article : " . $data['nom']);

        http_response_code(201);
        echo json_encode(["success" => "Article ajouté"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de l'ajout"]);
    }
}

// PUT (modifier)
function updateArticle($pdo) {
    try {
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
            htmlspecialchars($data['nom']),
            htmlspecialchars($data['description']),
            $data['prix'] ?? 0,
            $data['categorie'] ?? null,
            $data['photo'] ?? null,
            $data['disponible'] ?? 1,
            $data['id_article']
        ]);

        echo json_encode(["success" => "Article modifié"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur de modification"]);
    }
}

// PATCH (toggle disponible)
function toggleArticle($pdo) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id_article'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID requis"]);
            return;
        }

        $stmt = $pdo->prepare("
            UPDATE article 
            SET disponible = NOT disponible 
            WHERE id_article = ?
        ");

        $stmt->execute([$data['id_article']]);

        echo json_encode(["success" => "Statut modifié"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur toggle"]);
    }
}


//  DELETE
function deleteArticle($pdo) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id_article'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID requis"]);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM article WHERE id_article = ?");
        $stmt->execute([$data['id_article']]);

        echo json_encode(["success" => "Article supprimé"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur suppression"]);
    }
}
?>