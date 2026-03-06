<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function lister_seniors() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT id, email, type_utilisateur, created_at FROM utilisateurs WHERE type_utilisateur = 'senior'");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function lister_prestataires() {
    verifier_admin();
    $pdo = getDB();
    $req = $pdo->query("SELECT id, email, type_utilisateur, created_at FROM utilisateurs WHERE type_utilisateur = 'prestataire'");
    echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
}

function creer_senior() {
    verifier_admin();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'];
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, type_utilisateur) VALUES (?, 'senior')");
    $stmt->execute([$email]);
    echo json_encode(["message" => "ok"]);
}

function lister_categories() {
    verifier_admin();
    $pdo = getDB();

    $result = $pdo->query("SELECT * FROM categories_prestations");
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($categories);
}

function creer_categorie() {
    verifier_admin();
    $pdo = getDB();

    $data = json_decode(file_get_contents("php://input"), true);

    $nom = $data["nom"];
    $description = $data["description"];

    $stmt = $pdo->prepare("INSERT INTO categories_prestations (nom, description) VALUES (?, ?)");
    $stmt->execute([$nom, $description]);

    echo json_encode(["message" => "Categorie creee"]);
}

function supprimer_categorie($id) {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->prepare("DELETE FROM categories_prestations WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["message" => "Categorie supprimee"]);
}


function lister_evenements() {
    verifier_admin();
    $pdo = getDB();

    $result = $pdo->query("SELECT * FROM evenements");
    $evenements = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($evenements);
}

function creer_evenement() {
    verifier_admin();
    $pdo = getDB();

    $data = json_decode(file_get_contents("php://input"), true);

    $titre = $data["titre"];
    $date = $data["date_debut"];
    $lieu = $data["lieu"];
    $places = $data["nombre_places"];

    $stmt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, lieu, nombre_places) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $date, $lieu, $places]);

    echo json_encode(["message" => "Evenement cree"]);
}

function supprimer_evenement($id) {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["message" => "Evenement supprime"]);
}