<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/middleware.php";
require_once __DIR__ . "/../vendor/autoload.php";

define("STRIPE_SECRET", getenv("STRIPE_SECRET"));

function creer_paiement() {

    $payload = verifier_token();
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["montant"])) {
        http_response_code(400);
        echo json_encode(["message" => "Montant requis"]);
        return;
    }

    \Stripe\Stripe::setApiKey(STRIPE_SECRET);

    try {
        $intent = \Stripe\PaymentIntent::create([
            "amount" => $data["montant"],
            "currency" => "eur",
            "metadata" => [
                "user_id" => $payload["id_utilisateur"]
            ]
        ]);

        echo json_encode([
            "client_secret" => $intent->client_secret
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "message" => $e->getMessage()
        ]);
    }
}