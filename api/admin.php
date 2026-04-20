<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://51.210.12.40');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function lister_seniors() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT id_utilisateur, email, type_utilisateur, created_at FROM utilisateur WHERE type_utilisateur = 'senior' ORDER BY created_at DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_senior() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['email']) || empty($data['mot_de_passe'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Email et mot de passe obligatoires']);
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Email invalide']);
        return;
    }

    $hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
    $stmt->execute([$data['email'], $hash]);

    $id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom) VALUES (?, ?, ?)");
    $stmt2->execute([
        $id,
        isset($data['nom']) ? $data['nom'] : '',
        isset($data['prenom']) ? $data['prenom'] : ''
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'Senior cree', 'id' => $id]);
}

function lister_prestataires() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT u.id_utilisateur, u.email, u.created_at, p.nom, p.prenom, p.statut, p.categorie, p.tarif_horaire FROM utilisateur u JOIN prestataire p ON p.id_prestataire = u.id_utilisateur ORDER BY u.created_at DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function valider_prestataire($id) {
    verifier_admin();
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE prestataire SET statut = 'valide' WHERE id_prestataire = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Prestataire valide']);
}

function lister_categories() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT * FROM categories_prestations ORDER BY nom");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_categorie() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['nom'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Nom obligatoire']);
        return;
    }

    $nom = strip_tags($data['nom']);
    $description = isset($data['description']) ? strip_tags($data['description']) : '';

    $stmt = $pdo->prepare("INSERT INTO categories_prestations (nom, description) VALUES (?, ?)");
    $stmt->execute([$nom, $description]);

    echo json_encode(['message' => 'Categorie creee']);
}

function supprimer_categorie($id) {
    verifier_admin();
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM categories_prestations WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Categorie supprimee']);
}

function lister_evenements() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT * FROM evenements ORDER BY date_debut DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_evenement() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['titre']) || empty($data['date_debut'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Titre et date obligatoires']);
        return;
    }

    $titre = strip_tags($data['titre']);
    $lieu = isset($data['lieu']) ? strip_tags($data['lieu']) : '';
    $places = isset($data['nombre_places']) ? $data['nombre_places'] : 20;

    $stmt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, lieu, nombre_places) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $data['date_debut'], $lieu, $places]);

    echo json_encode(['message' => 'Evenement cree']);
}

function supprimer_evenement($id) {
    verifier_admin();
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Evenement supprime']);
}

function lister_articles() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT * FROM article ORDER BY created_at DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_article() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['nom']) || !isset($data['prix'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Nom et prix obligatoires']);
        return;
    }

    if (!is_numeric($data['prix']) || $data['prix'] < 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Prix invalide']);
        return;
    }

    $nom = strip_tags($data['nom']);
    $description = isset($data['description']) ? strip_tags($data['description']) : '';
    $categorie = isset($data['categorie']) ? strip_tags($data['categorie']) : '';
    $disponible = isset($data['disponible']) ? $data['disponible'] : 1;

    $stmt = $pdo->prepare("INSERT INTO article (nom, description, prix, categorie, disponible) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $description, $data['prix'], $categorie, $disponible]);

    http_response_code(201);
    echo json_encode(['message' => 'Article cree', 'id' => $pdo->lastInsertId()]);
}

function modifier_article($id) {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    $nom = isset($data['nom']) ? strip_tags($data['nom']) : '';
    $description = isset($data['description']) ? strip_tags($data['description']) : '';
    $categorie = isset($data['categorie']) ? strip_tags($data['categorie']) : '';
    $prix = isset($data['prix']) ? $data['prix'] : 0;
    $disponible = isset($data['disponible']) ? $data['disponible'] : 1;

    $stmt = $pdo->prepare("UPDATE article SET nom = ?, description = ?, prix = ?, categorie = ?, disponible = ? WHERE id_article = ?");
    $stmt->execute([$nom, $description, $prix, $categorie, $disponible, $id]);

    echo json_encode(['message' => 'Article modifie']);
}

function supprimer_article($id) {
    verifier_admin();
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM article WHERE id_article = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Article supprime']);
}

function lister_conseils() {
    verifier_token();
    $pdo = getDB();
    $req = $pdo->query("SELECT * FROM conseils ORDER BY created_at DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function get_conseils_senior() {
    verifier_senior();
    $pdo = getDB();
    $req = $pdo->query("SELECT * FROM conseils ORDER BY created_at DESC");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_conseil() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['titre']) || empty($data['contenu'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Titre et contenu obligatoires']);
        return;
    }

    $titre = strip_tags($data['titre']);
    $contenu = strip_tags($data['contenu']);
    $categorie = isset($data['categorie']) ? strip_tags($data['categorie']) : 'general';

    $stmt = $pdo->prepare("INSERT INTO conseils (titre, contenu, categorie) VALUES (?, ?, ?)");
    $stmt->execute([$titre, $contenu, $categorie]);

    echo json_encode(['message' => 'Conseil cree', 'id' => $pdo->lastInsertId()]);
}

function supprimer_conseil($id) {
    verifier_admin();
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM conseils WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Conseil supprime']);
}