<?php
session_start();
require_once 'db_connect.php';
require_once 'check_session.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_reservation = isset($_GET['id_res']) ? intval($_GET['id_res']) : 0;
if ($id_reservation === 0) {
    header('Location: dashboardS.php');
    exit();
}

// Récupère le devis accepté lié à cette réservation
$stmt = $pdo->prepare("
    SELECT d.*, 
           p.prenom AS p_prenom, p.nom AS p_nom,
           s.nom_service
    FROM devis d
    JOIN prestataire p ON d.id_prestataire = p.id_prestataire
    LEFT JOIN reservation r ON d.id_reservation = r.id_reservation
    LEFT JOIN disponibilites di ON r.id_disponibilite = di.id_disponibilite
    LEFT JOIN services s ON di.id_service = s.id_service
    WHERE d.id_reservation = ?
      AND d.id_senior = ?
      AND d.statut = 'accepte'
    LIMIT 1
");
$stmt->execute([$id_reservation, $_SESSION['id']]);
$devis = $stmt->fetch();

if (!$devis) {
    header('Location: dashboardS.php?err=devis_introuvable#planning');
    exit();
}

$stripe_public_key = getenv('STRIPE_PUBLIC_KEY');
$montant_centimes  = (int) round($devis['montant_ttc'] * 100);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement – Silver Happy</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: 'DM Sans', sans-serif; font-size: 18px; background: #F4EDDE; }
        #card-element {
            border: 2px solid #e2e8f0; border-radius: 12px;
            padding: 16px; background: white; font-size: 18px;
        }
        #card-element.StripeElement--focus { border-color: #E37A55; }
        #card-element.StripeElement--invalid { border-color: #ef4444; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-3xl shadow-md p-8 w-full max-w-lg">

        <div class="text-center mb-8">
            <p class="text-2xl font-bold text-[#E37A55] font-title mb-1">Silver Happy</p>
            <h1 class="text-xl font-bold text-slate-800">Paiement de la prestation</h1>
        </div>

        <!-- Récapitulatif -->
        <div class="bg-slate-50 rounded-2xl p-5 mb-7 border border-slate-100">
            <p class="text-sm text-slate-500 uppercase font-bold tracking-wide mb-3">Récapitulatif</p>
            <p class="font-bold text-slate-800 text-lg">
                <?= htmlspecialchars($devis['s.nom_service'] ?? $devis['titre']) ?>
            </p>
            <p class="text-slate-500 mt-1">
                Avec <?= htmlspecialchars($devis['p_prenom'] . ' ' . $devis['p_nom']) ?>
            </p>
            <div class="mt-4 pt-4 border-t border-slate-200 flex justify-between items-center">
                <span class="text-slate-500">Total TTC</span>
                <span class="text-2xl font-bold text-[#E37A55]">
                    <?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €
                </span>
            </div>
        </div>

        <!-- Formulaire Stripe -->
        <form id="payment-form">
            <label class="block text-sm font-bold text-slate-600 mb-2">
                Informations de carte bancaire
            </label>
            <div id="card-element"></div>
            <div id="card-errors" class="text-red-500 text-sm mt-2 min-h-[20px]"></div>

            <button id="submit-btn" type="submit"
                class="mt-6 w-full bg-[#E37A55] text-white py-4 rounded-2xl font-bold text-lg hover:bg-[#c96642] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                <i class="fa-solid fa-lock"></i>
                Payer <?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €
            </button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-5">
            Paiement sécurisé via Stripe. Vos données bancaires ne sont jamais stockées.
        </p>

        <div class="text-center mt-4">
            <a href="dashboardS.php#planning" class="text-sm text-slate-400 hover:text-slate-600">
                ← Annuler et revenir
            </a>
        </div>
    </div>

<script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
<script>
const stripe = Stripe('<?= $stripe_public_key ?>');
const elements = stripe.elements();
const cardEl   = elements.create('card', {
    style: { base: { fontSize: '18px', fontFamily: '"DM Sans", sans-serif', color: '#1e293b' } }
});
cardEl.mount('#card-element');
cardEl.on('change', e => {
    document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
});

document.getElementById('payment-form').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Traitement...';

    // Crée le PaymentIntent côté serveur
    const res = await fetch('/api/paiements/creer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            montant:        <?= $montant_centimes ?>,
            id_devis:       <?= $devis['id_devis'] ?>,
            id_reservation: <?= $id_reservation ?>
        })
    });
    const { client_secret, error: serverError } = await res.json();

    if (serverError) {
        document.getElementById('card-errors').textContent = serverError;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock"></i> Payer <?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €';
        return;
    }

    // Confirme le paiement avec Stripe
    const { error } = await stripe.confirmCardPayment(client_secret, {
        payment_method: { card: cardEl }
    });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock"></i> Payer <?= number_format($devis['montant_ttc'], 2, ',', ' ') ?> €';
    } else {
        window.location.href = 'dashboardS.php?msg=paiement_ok#planning';
    }
});
</script>
</body>
</html>