<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    echo json_encode(['error' => 'Non autorisé']); exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db_connect.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$data   = json_decode(file_get_contents('php://input'), true);
$montant = (int)($data['montant'] ?? 0);
$type   = $data['type_abonnement'] ?? '';

if (!$montant || !in_array($type, ['mensuel', 'annuel'])) {
    echo json_encode(['error' => 'Données invalides']); exit();
}

$intent = \Stripe\PaymentIntent::create([
    'amount'   => $montant,
    'currency' => 'eur',
    'metadata' => [
        'type_objet'      => 'abonnement_presta',
        'type_abonnement' => $type,
        'id_prestataire'  => $_SESSION['id'],
    ],
]);

echo json_encode(['client_secret' => $intent->client_secret]);