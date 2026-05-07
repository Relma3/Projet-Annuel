<?php
session_start();
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php'); exit();
}

$id_presta = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT abonnement_statut, abonnement_expiration FROM prestataire WHERE id_prestataire = ?");
$stmt->execute([$id_presta]);
$presta = $stmt->fetch();

$stripe_public_key = getenv('STRIPE_PUBLIC_KEY');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Abonnement — Silver Happy PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
<div class="max-w-lg mx-auto">
    <a href="dashboardP.php" class="text-emerald-600 font-bold mb-6 inline-block">← Retour</a>
    <h1 class="text-2xl font-bold text-emerald-600 mb-2">Mon Abonnement Prestataire</h1>

    <!-- Statut actuel -->
    <div class="bg-white rounded-2xl shadow p-6 mb-6">
        <p class="text-sm text-gray-500 mb-1">Statut actuel</p>
        <?php if ($presta['abonnement_statut'] === 'actif'): ?>
            <p class="text-emerald-600 font-bold text-lg">✅ Actif</p>
            <p class="text-gray-400 text-sm">Expire le : <?= date('d/m/Y', strtotime($presta['abonnement_expiration'])) ?></p>
        <?php else: ?>
            <p class="text-red-500 font-bold text-lg">❌ Inactif</p>
            <p class="text-gray-400 text-sm">Souscrivez un abonnement pour être référencé sur Silver Happy.</p>
        <?php endif; ?>
    </div>

    <!-- Offres -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow p-5 border-2 border-transparent hover:border-emerald-400 cursor-pointer" onclick="choisir('mensuel', this)">
            <p class="font-bold text-lg">Mensuel</p>
            <p class="text-3xl font-bold text-emerald-600 mt-1">29€</p>
            <p class="text-gray-400 text-sm">/mois</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5 border-2 border-transparent hover:border-emerald-400 cursor-pointer" onclick="choisir('annuel', this)">
            <p class="font-bold text-lg">Annuel</p>
            <p class="text-3xl font-bold text-emerald-600 mt-1">290€</p>
            <p class="text-gray-400 text-sm">/an <span class="text-emerald-500 text-xs font-bold">-17%</span></p>
        </div>
    </div>

    <div id="paiement-form" class="hidden bg-white rounded-2xl shadow p-6">
        <p class="font-bold mb-4">Informations de paiement</p>
        <div id="card-element" class="border border-gray-300 rounded-xl p-4 mb-4"></div>
        <div id="card-errors" class="text-red-500 text-sm mb-4"></div>
        <button id="btn-payer" class="w-full bg-emerald-500 text-white py-4 rounded-xl font-bold text-lg hover:bg-emerald-600 transition-all">
            Payer
        </button>
    </div>
</div>

<script>
const stripe   = Stripe('<?= $stripe_public_key ?>');
const elements = stripe.elements();
const card     = elements.create('card');
let   montant  = 0;
let   type     = '';

function choisir(t, el) {
    document.querySelectorAll('.cursor-pointer').forEach(e => e.classList.remove('border-emerald-400'));
    el.classList.add('border-emerald-400');
    type    = t;
    montant = t === 'mensuel' ? 2900 : 29000;
    document.getElementById('paiement-form').classList.remove('hidden');
    document.getElementById('btn-payer').textContent = 'Payer ' + (montant/100) + ' €';
    card.mount('#card-element');
}

card.on('change', e => {
    document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
});

document.getElementById('btn-payer').addEventListener('click', async () => {
    const btn = document.getElementById('btn-payer');
    btn.disabled = true; btn.textContent = 'Traitement…';

    // Créer le PaymentIntent
    const resp = await fetch('/api/creer_paiement_presta.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ type_abonnement: type, montant })
    });
    const { client_secret, error } = await resp.json();
    if (error) { document.getElementById('card-errors').textContent = error; btn.disabled = false; return; }

    const { error: stripeError } = await stripe.confirmCardPayment(client_secret, {
        payment_method: { card }
    });

    if (stripeError) {
        document.getElementById('card-errors').textContent = stripeError.message;
        btn.disabled = false;
        btn.textContent = 'Réessayer';
    } else {
        window.location.href = 'dashboardP.php?msg=abonnement_ok';
    }
});
</script>
</body>
</html>