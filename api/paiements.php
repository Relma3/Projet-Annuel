<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";
require_once __DIR__ . "/../vendor/autoload.php";

define("STRIPE_SK", getenv("STRIPE_SECRET_KEY") ?: getenv("STRIPE_SECRET") ?: "");

function creer_paiement() {
    $payload = verifier_senior();
    $pdo = getDB();
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["montant"]) || !is_numeric($data["montant"]) || $data["montant"] <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Montant invalide"]);
        return;
    }

    if (empty(STRIPE_SK)) {
        http_response_code(500);
        echo json_encode(["message" => "Cle Stripe non configuree"]);
        return;
    }

    \Stripe\Stripe::setApiKey(STRIPE_SK);

    try {
        $intent = \Stripe\PaymentIntent::create([
            "amount" => (int)$data["montant"],
            "currency" => "eur",
            "metadata" => [
                "id_utilisateur" => $payload["id_utilisateur"],
                "type" => $data["type"] ?? "achat",
                "id_article" => $data["id_article"] ?? ""
            ]
        ]);

        if (!empty($data["id_article"])) {
            $stmt = $pdo->prepare("SELECT nom, prix FROM article WHERE id_article = ?");
            $stmt->execute([$data["id_article"]]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($article) {
                $pdo->prepare("INSERT INTO commandes (id_senior, id_article, nom_article, prix, statut, stripe_payment_intent) VALUES (?, ?, ?, ?, 'en_attente', ?)")
                    ->execute([
                        $payload["id_utilisateur"],
                        $data["id_article"],
                        $article["nom"],
                        $article["prix"],
                        $intent->id
                    ]);
            }
        }

        echo json_encode([
            "client_secret" => $intent->client_secret,
            "payment_intent_id" => $intent->id
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur Stripe"]);
    }
}

function souscrire_abonnement() {
    $payload = verifier_senior();
    $id_senior = $payload["id_utilisateur"];
    $data = json_decode(file_get_contents("php://input"), true);

    $type = $data["type"] ?? "annuel";

    $tarifs = [
        "mensuel" => 400,
        "annuel" => 4000,
        "renouvellement_mensuel" => 300,
        "renouvellement_annuel" => 3500
    ];

    if (!isset($tarifs[$type])) {
        http_response_code(400);
        echo json_encode(["message" => "Type d abonnement invalide"]);
        return;
    }

    if (empty(STRIPE_SK)) {
        http_response_code(500);
        echo json_encode(["message" => "Cle Stripe non configuree"]);
        return;
    }

    \Stripe\Stripe::setApiKey(STRIPE_SK);

    try {
        $intent = \Stripe\PaymentIntent::create([
            "amount" => $tarifs[$type],
            "currency" => "eur",
            "metadata" => [
                "id_senior" => $id_senior,
                "type_abo" => $type,
                "action" => "abonnement"
            ]
        ]);

        echo json_encode([
            "client_secret" => $intent->client_secret,
            "payment_intent_id" => $intent->id,
            "montant_euros" => number_format($tarifs[$type] / 100, 2)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur Stripe"]);
    }
}

function stripe_webhook() {
    $payload = file_get_contents("php://input");
    $sig = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? "";
    $secret = getenv("STRIPE_WEBHOOK_SECRET") ?? "";

    \Stripe\Stripe::setApiKey(STRIPE_SK);

    try {
        if (!empty($secret) && !empty($sig)) {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
        } else {
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["message" => "Erreur webhook"]);
        exit;
    }

    $pdo = getDB();

    if ($event->type == "payment_intent.succeeded") {
        $intent = $event->data->object;
        $meta = $intent->metadata;

        $pdo->prepare("UPDATE commandes SET statut = 'expediee' WHERE stripe_payment_intent = ?")
            ->execute([$intent->id]);

        if (($meta->action ?? "") == "abonnement") {
            $id_senior = $meta->id_senior ?? null;
            $type_abo = $meta->type_abo ?? "annuel";

            if ($id_senior) {
                $jours = 30;
                if (strpos($type_abo, "annuel") !== false) {
                    $jours = 365;
                }

                $debut = date("Y-m-d");
                $fin = date("Y-m-d", strtotime("+$jours days"));
                $prix = $intent->amount / 100;

                $pdo->prepare("UPDATE abonnements SET statut = 'expire' WHERE id_senior = ? AND statut = 'actif'")
                    ->execute([$id_senior]);

                $pdo->prepare("INSERT INTO abonnements (id_senior, type, prix, debut, fin, statut) VALUES (?, ?, ?, ?, ?, 'actif')")
                    ->execute([$id_senior, $type_abo, $prix, $debut, $fin]);
            }
        }
    }

    if ($event->type == "payment_intent.payment_failed") {
        $intent = $event->data->object;

        $pdo->prepare("UPDATE commandes SET statut = 'annulee' WHERE stripe_payment_intent = ?")
            ->execute([$intent->id]);
    }

    http_response_code(200);
    echo json_encode(["received" => true]);
}