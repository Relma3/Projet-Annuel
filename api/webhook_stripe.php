<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/../vendor/autoload.php";

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET"));

$payload = file_get_contents("php://input");
$sig = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? "";
$secret = getenv("STRIPE_WEBHOOK_SECRET");

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
} catch (Exception $e) {
    http_response_code(400);
    exit;
}

if ($event->type === "payment_intent.succeeded") {
    $intent = $event->data->object;
    $id_senior = $intent->metadata->user_id;
    $montant = $intent->amount;

    $type = $montant <= 400 ? "mensuel" : "annuel";
    $date_debut = date("Y-m-d");
    $date_fin = $type === "mensuel"
        ? date("Y-m-d", strtotime("+1 month"))
        : date("Y-m-d", strtotime("+1 year"));

    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO abonnement (id_senior, type, statut, date_debut, date_fin, montant)
        VALUES (?, ?, 'actif', ?, ?, ?)
        ON DUPLICATE KEY UPDATE statut='actif', date_fin=?, montant=?
    ")->execute([
        $id_senior, $type, $date_debut, $date_fin, $montant / 100,
        $date_fin, $montant / 100
    ]);
}

http_response_code(200);
echo json_encode(["ok" => true]);