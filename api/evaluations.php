<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function creer_evaluation() {
    $payload = verifier_senior();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["id_reservation"]) || empty($data["note"])) {
        http_response_code(400);
        echo json_encode(["message" => "Champs obligatoires"]);
        return;
    }

    $note = (int)$data["note"];

    if ($note < 1 || $note > 5) {
        http_response_code(400);
        echo json_encode(["message" => "Note invalide"]);
        return;
    }

    $check = $pdo->prepare("
        SELECT id_reservation, id_prestataire
        FROM reservation
        WHERE id_reservation = ? AND id_senior = ? AND statut = 'termine'
    ");
    $check->execute([$data["id_reservation"], $payload["id_utilisateur"]]);
    $res = $check->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        http_response_code(403);
        echo json_encode(["message" => "Reservation introuvable"]);
        return;
    }

    $existing = $pdo->prepare("SELECT id FROM evaluations WHERE id_reservation = ?");
    $existing->execute([$data["id_reservation"]]);

    if ($existing->fetch()) {
        http_response_code(409);
        echo json_encode(["message" => "Evaluation deja faite"]);
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO evaluations (id_reservation, id_senior, id_prestataire, note, commentaire)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data["id_reservation"],
        $payload["id_utilisateur"],
        $res["id_prestataire"],
        $note,
        $data["commentaire"] ?? null
    ]);

    http_response_code(201);
    echo json_encode(["message" => "Evaluation enregistree"]);
}

function evaluations_prestataire($id_pres) {
    verifier_token();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT e.note, e.commentaire, e.created_at, s.prenom AS auteur_prenom
        FROM evaluations e
        JOIN senior s ON s.id_senior = e.id_senior
        WHERE e.id_prestataire = ?
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$id_pres]);
    $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $moyenne = 0;
    if (count($evals) > 0) {
        $total = 0;
        foreach ($evals as $e) {
            $total += $e["note"];
        }
        $moyenne = round($total / count($evals), 1);
    }

    echo json_encode([
        "id_prestataire" => (int)$id_pres,
        "moyenne" => $moyenne,
        "nombre" => count($evals),
        "evaluations" => $evals
    ]);
}

function admin_lister_evaluations() {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT e.id, e.note, e.commentaire, e.created_at,
               p.nom AS pres_nom, p.prenom AS pres_prenom, p.categorie,
               s.nom AS senior_nom, s.prenom AS senior_prenom
        FROM evaluations e
        JOIN prestataire p ON p.id_prestataire = e.id_prestataire
        JOIN senior s ON s.id_senior = e.id_senior
        ORDER BY e.created_at DESC
        LIMIT 500
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function admin_stats_evaluations() {
    verifier_admin();
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT p.id_prestataire, p.nom, p.prenom, p.categorie,
               ROUND(AVG(e.note), 2) AS note_moyenne,
               COUNT(e.id) AS nb_evaluations
        FROM prestataire p
        LEFT JOIN evaluations e ON e.id_prestataire = p.id_prestataire
        WHERE p.statut = 'valide'
        GROUP BY p.id_prestataire
        ORDER BY note_moyenne DESC
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}