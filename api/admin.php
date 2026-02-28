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
