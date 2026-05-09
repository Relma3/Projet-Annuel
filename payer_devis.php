<?php
/** payer_devis.php — Paiement d'un devis accepté via Stripe */
session_start();
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php'); exit();
}

$id_devis  = (int)($_GET['id'] ?? 0);
$id_senior = $_SESSION['id'];

// Récupérer le devis
$stmt = $pdo->prepare("
    SELECT d.*, s.nom, s.prenom
    FROM devis d
    JOIN senior s ON s.id_senior = d.id_senior
    WHERE d.id_devis = ? AND d.id_senior = ? AND d.statut = 'envoye'
");
$stmt->execute([$id_devis, $id_senior]);
$devis = $stmt->fetch();

if (!$devis) { http_response_code(404); die('Devis introuvable ou déjà traité.'); }

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$intent = \Stripe\PaymentIntent::create([
    'amount'   => (int)round($devis['montant_ttc'] * 100),
    'currency' => 'eur',
    'metadata' => [
        'type_objet'     => 'devis',
        'id_devis'       => $id_devis,
        'id_reservation' => $devis['id_reservation'],
        'user_id'        => $id_senior,
    ],
]);

$stripe_public_key = getenv('STRIPE_PUBLIC_KEY');?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Payer le devis — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
<div class="bg-white rounded-2xl shadow p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-orange-500 mb-2">Paiement du devis</h1>
    <p class="text-gray-500 mb-1">N° <?= htmlspecialchars($devis['numero_devis']) ?></p>
    <p class="text-3xl font-bold text-gray-800 mb-6"><?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €</p>

    <div id="card-element" class="border border-gray-300 rounded-xl p-4 mb-4"></div>
    <div id="card-errors" class="text-red-500 text-sm mb-4"></div>

    <button id="btn-payer"
        class="w-full bg-orange-500 text-white py-4 rounded-xl font-bold text-lg hover:bg-orange-600 transition-all">
        Payer <?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €
    </button>

    <a href="dashboardS.php" class="block text-center text-gray-400 text-sm mt-4 hover:underline">Annuler</a>
</div>

<script>
const stripe  = Stripe('<?= $stripe_public_key ?>');
const elements = stripe.elements();
const card    = elements.create('card');
card.mount('#card-element');

card.on('change', e => {
    document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
});

document.getElementById('btn-payer').addEventListener('click', async () => {
    const btn = document.getElementById('btn-payer');
    btn.disabled = true;
    btn.textContent = 'Traitement…';

    const { error } = await stripe.confirmCardPayment('<?= $intent->client_secret ?>', {
        payment_method: { card }
    });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
        btn.disabled = false;
        btn.textContent = 'Réessayer';
    } else {
        window.location.href = 'dashboardS.php?msg=paiement_ok#planning';
    }
});
</script>
</body>
</html>