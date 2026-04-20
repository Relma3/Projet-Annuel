<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";

function get_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT u.id_utilisateur, u.email, u.type_utilisateur, u.created_at,
               s.nom, s.prenom, s.telephone, s.date_naissance, s.adresse
        FROM utilisateur u
        LEFT JOIN senior s ON s.id_senior = u.id_utilisateur
        WHERE u.id_utilisateur = ?
    ");
    $stmt->execute([$payload["id_utilisateur"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "Utilisateur introuvable"]);
        return;
    }

    echo json_encode($user);
}

function modifier_profil_senior() {
    $payload = verifier_token();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $payload["id_utilisateur"];

    if (!empty($data["email"])) {
        $stmt = $pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_utilisateur = ?");
        $stmt->execute([$data["email"], $id]);
    }

    $stmt = $pdo->prepare("UPDATE senior SET nom = ?, prenom = ?, telephone = ?, adresse = ?, date_naissance = ? WHERE id_senior = ?");
    $stmt->execute([
        $data["nom"] ?? null,
        $data["prenom"] ?? null,
        $data["telephone"] ?? null,
        $data["adresse"] ?? null,
        $data["date_naissance"] ?? null,
        $id
    ]);

    if (!empty($data["nouveau_mot_de_passe"])) {
        $hash = password_hash($data["nouveau_mot_de_passe"], PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?")
            ->execute([$hash, $id]);
    }

    echo json_encode(["message" => "Profil mis a jour"]);
}

function mes_reservations_senior() {
    $payload = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT r.id_reservation, r.date_reservation, r.statut, r.description,
               p.nom AS pres_nom, p.prenom AS pres_prenom, p.categorie, p.tarif_horaire,
               ev.note AS evaluation_note
        FROM reservation r
        JOIN prestataire p ON p.id_prestataire = r.id_prestataire
        LEFT JOIN evaluations ev ON ev.id_reservation = r.id_reservation
        WHERE r.id_senior = ?
        ORDER BY r.date_reservation DESC
    ");
    $stmt->execute([$payload["id_utilisateur"]]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function mes_commandes_senior() {
    $payload = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT c.id_commande, c.nom_article, c.prix, c.statut, c.created_at,
               a.description, a.categorie, a.photo
        FROM commandes c
        LEFT JOIN article a ON a.id_article = c.id_article
        WHERE c.id_senior = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$payload["id_utilisateur"]]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function mon_abonnement() {
    $payload = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT * FROM abonnements
        WHERE id_senior = ? AND statut = 'actif'
        ORDER BY fin DESC
        LIMIT 1
    ");
    $stmt->execute([$payload["id_utilisateur"]]);
    $abo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$abo) {
        echo json_encode([
            "statut" => "aucun",
            "tarifs" => [
                "mensuel" => ["prix" => 4],
                "annuel" => ["prix" => 40]
            ],
            "renouvellement" => [
                "mensuel" => ["prix" => 3],
                "annuel" => ["prix" => 35]
            ]
        ]);
        return;
    }

    $abo["jours_restants"] = (int)((strtotime($abo["fin"]) - time()) / 86400);
    if ($abo["jours_restants"] < 0) {
        $abo["jours_restants"] = 0;
    }

    echo json_encode($abo);
}

function mes_notifications() {
    $payload = verifier_senior();
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT id, titre, message, lu, created_at
        FROM notifications
        WHERE id_senior = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$payload["id_utilisateur"]]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->prepare("UPDATE notifications SET lu = 1 WHERE id_senior = ?")
        ->execute([$payload["id_utilisateur"]]);

    echo json_encode($notifs);
}

function get_conseils_senior() {
    verifier_senior();
    $pdo = getDB();

    if (!empty($_GET["categorie"])) {
        $stmt = $pdo->prepare("SELECT * FROM conseils WHERE categorie = ? ORDER BY created_at DESC");
        $stmt->execute([$_GET["categorie"]]);
    } else {
        $stmt = $pdo->query("SELECT * FROM conseils ORDER BY created_at DESC");
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}