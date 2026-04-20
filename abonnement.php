<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Abonnement - Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50 min-h-screen" style="font-size:18px;">

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <span class="text-orange-500 font-bold text-2xl">Silver Happy</span>
    <div class="flex gap-4">
        <a href="dashboardS.php" class="text-gray-600">Tableau de bord</a>
        <a href="logout.php" class="text-red-400">Déconnexion</a>
    </div>
</nav>

<div class="max-w-3xl mx-auto mt-10 px-4">
    <h1 class="text-3xl font-bold text-orange-500 mb-2">Mon Abonnement</h1>
    <p class="text-gray-500 mb-8">Gerez votre abonnement</p>

    <?php if (isset($_GET['succes'])) { ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 text-lg">
            Paiement effectue
        </div>
    <?php } ?>

    <div class="bg-white rounded-2xl shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Statut actuel</h2>
        <p class="text-green-700 font-semibold">Membre actif</p>
    </div>

    <h2 class="text-2xl font-bold mb-4">Choisir un abonnement</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow p-8 border">
            <h3 class="text-xl font-bold mb-2">Mensuel</h3>
            <p class="text-4xl font-bold text-orange-500 mb-4">4€ / mois</p>
            <button onclick="payer(400, 'mensuel')" class="w-full bg-orange-500 text-white text-lg font-bold py-4 rounded-xl">
                Choisir
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow p-8 border">
            <h3 class="text-xl font-bold mb-2">Annuel</h3>
            <p class="text-4xl font-bold text-orange-500 mb-4">40€ / an</p>
            <button onclick="payer(4000, 'annuel')" class="w-full bg-orange-500 text-white text-lg font-bold py-4 rounded-xl">
                Choisir
            </button>
        </div>
    </div>

    <div id="zone-paiement" class="hidden bg-white rounded-2xl shadow p-6">
        <h3 class="text-xl font-bold mb-4">Paiement</h3>
        <div id="card-element" class="border border-gray-300 rounded-xl p-4 mb-4"></div>
        <div id="card-errors" class="text-red-500 mb-4"></div>
        <button id="btn-payer" class="w-full bg-green-500 text-white text-xl font-bold py-4 rounded-xl">
            Confirmer
        </button>
    </div>
</div>

<script>
const stripe = Stripe("<?php echo getenv('STRIPE_PUBLIC_KEY') ?: 'pk_test_votre_cle_publique'; ?>");
const elements = stripe.elements();
const card = elements.create("card");

let montantChoisi = 0;
let typeChoisi = "";

function payer(montant, type) {
    montantChoisi = montant;
    typeChoisi = type;
    document.getElementById("zone-paiement").classList.remove("hidden");
    card.mount("#card-element");
}

async function lancerPaiement() {
    const btn = document.getElementById("btn-payer");
    btn.textContent = "Traitement...";
    btn.disabled = true;

    const res = await fetch("/api/paiements/abonnement", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            montant: montantChoisi,
            type: typeChoisi
        })
    });

    const data = await res.json();

    if (!data.clientSecret) {
        document.getElementById("card-errors").textContent = "Erreur paiement";
        btn.textContent = "Confirmer";
        btn.disabled = false;
        return;
    }

    const result = await stripe.confirmCardPayment(data.clientSecret, {
        payment_method: {
            card: card
        }
    });

    if (result.error) {
        document.getElementById("card-errors").textContent = result.error.message;
        btn.textContent = "Confirmer";
        btn.disabled = false;
    } else {
        window.location.href = "abonnement.php?succes=1";
    }
}

document.getElementById("btn-payer").addEventListener("click", lancerPaiement);
</script>

<script src="onesignal.js"></script>
</body>
</html>