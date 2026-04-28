<?php
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../PdfGenerator.php';

\Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET"));

$payload = file_get_contents("php://input");
$sig     = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? "";
$secret  = getenv("STRIPE_WEBHOOK_SECRET");

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
} catch (Exception $e) {
    http_response_code(400);
    exit;
}

if ($event->type === "payment_intent.succeeded") {
    $intent   = $event->data->object;
    $id_senior = $intent->metadata->user_id;
    $montant  = $intent->amount;

    $type      = $montant <= 400 ? "mensuel" : "annuel";
    $date_debut = date("Y-m-d");
    $date_fin  = $type === "mensuel"
        ? date("Y-m-d", strtotime("+1 month"))
        : date("Y-m-d", strtotime("+1 year"));

    $pdo = getDB();

    // Mise à jour abonnement
    $pdo->prepare("
        INSERT INTO abonnement (id_senior, type, statut, date_debut, date_fin, montant)
        VALUES (?, ?, 'actif', ?, ?, ?)
        ON DUPLICATE KEY UPDATE statut='actif', date_fin=?, montant=?
    ")->execute([
        $id_senior, $type, $date_debut, $date_fin, $montant / 100,
        $date_fin, $montant / 100
    ]);

    // Enregistrement du paiement et récupération de l'id
    $pdo->prepare("
        INSERT INTO paiements (type_objet, objet_id, id_payeur, montant_cents,
                               stripe_payment_intent_id, statut, date_paiement)
        VALUES ('abonnement', 0, ?, ?, ?, 'reussi', NOW())
    ")->execute([$id_senior, $montant, $intent->id]);

    $id_paiement = (int) $pdo->lastInsertId(); // ← c'était ça qui manquait

    // Génération de la facture PDF
    $pdf = new PdfGenerator();
    try {
        $pdf->genererFactureSenior($pdo, $id_paiement);
    } catch (Exception $e) {
        error_log('Erreur PDF senior: ' . $e->getMessage());
    }
}

// Abonnement activé avec succès
if ($event->type === 'checkout.session.completed') {
    $session    = $event->data->object;
    $id_senior  = $session->metadata->id_senior;
    $type       = $session->metadata->type;
    $sub_id     = $session->subscription;
    $customer_id = $session->customer;

    $date_debut = date('Y-m-d');
    $date_fin   = $type === 'mensuel'
        ? date('Y-m-d', strtotime('+1 month'))
        : date('Y-m-d', strtotime('+1 year'));
    $montant    = $type === 'mensuel' ? 4.00 : 40.00;

    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO abonnement 
            (id_senior, type, statut, date_debut, date_fin, montant, stripe_subscription_id, stripe_customer_id)
        VALUES (?, ?, 'actif', ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            statut='actif', date_fin=?, stripe_subscription_id=?, stripe_customer_id=?
    ")->execute([
        $id_senior, $type, $date_debut, $date_fin, $montant, $sub_id, $customer_id,
        $date_fin, $sub_id, $customer_id
    ]);
}

// Renouvellement automatique mensuel/annuel
if ($event->type === 'invoice.payment_succeeded') {
    $invoice   = $event->data->object;
    $sub_id    = $invoice->subscription;
    if (!$sub_id) { http_response_code(200); exit; }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM abonnement WHERE stripe_subscription_id = ?");
    $stmt->execute([$sub_id]);
    $abo = $stmt->fetch();
    if ($abo) {
        $date_fin = $abo['type'] === 'mensuel'
            ? date('Y-m-d', strtotime('+1 month'))
            : date('Y-m-d', strtotime('+1 year'));
        $pdo->prepare("
            UPDATE abonnement SET statut='actif', date_fin=? 
            WHERE stripe_subscription_id=?
        ")->execute([$date_fin, $sub_id]);
    }
}

// Abonnement annulé ou expiré
if ($event->type === 'customer.subscription.deleted') {
    $sub    = $event->data->object;
    $sub_id = $sub->id;
    $pdo    = getDB();
    $pdo->prepare("
        UPDATE abonnement SET statut='annule' WHERE stripe_subscription_id=?
    ")->execute([$sub_id]);
}

http_response_code(200);
echo json_encode(["ok" => true]);