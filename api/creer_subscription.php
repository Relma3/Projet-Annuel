<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';

if (!in_array($type, ['mensuel', 'annuel'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Type invalide']);
    exit();
}

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET'));

$price_id = $type === 'mensuel'
    ? getenv('STRIPE_PRICE_MENSUEL')
    : getenv('STRIPE_PRICE_ANNUEL');

$id_senior = $_SESSION['id'];

try {
    // Récupère le stripe_customer_id existant ou en crée un nouveau
    $stmt = $pdo->prepare("
    SELECT s.id_senior, u.email, s.prenom, s.nom, a.stripe_customer_id
    FROM senior s
    JOIN utilisateur u ON u.id_utilisateur = s.id_senior
    LEFT JOIN abonnement a ON a.id_senior = s.id_senior
    WHERE s.id_senior = ?
    ORDER BY a.created_at DESC LIMIT 1
");
$stmt->execute([$id_senior]);
$senior = $stmt->fetch();

    // Crée un customer Stripe si pas encore existant
    if (empty($senior['stripe_customer_id'])) {
        $customer = \Stripe\Customer::create([
            'email' => $senior['email'],
            'name'  => $senior['prenom'] . ' ' . $senior['nom'],
            'metadata' => ['id_senior' => $id_senior]
        ]);
        $stripe_customer_id = $customer->id;
    } else {
        $stripe_customer_id = $senior['stripe_customer_id'];
    }

    // Crée la session Stripe Checkout en mode subscription
    $session = \Stripe\Checkout\Session::create([
        'customer'            => $stripe_customer_id,
        'payment_method_types' => ['card'],
        'mode'                => 'subscription',
        'line_items'          => [[
            'price'    => $price_id,
            'quantity' => 1,
        ]],
        'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/abonnement.php?succes=1',
        'cancel_url'  => 'http://' . $_SERVER['HTTP_HOST'] . '/abonnement.php?annule=1',
        'metadata'    => [
            'id_senior' => $id_senior,
            'type'      => $type
        ]
    ]);

    echo json_encode(['url' => $session->url]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}