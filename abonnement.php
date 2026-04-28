<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

require_once 'db_connect.php';

$db = getDB();

$stmt = $db->prepare("
    SELECT *
    FROM senior
    WHERE id_utilisateur = ?
");
$stmt->execute([$_SESSION['id']]);

$senior = $stmt->fetch(PDO::FETCH_ASSOC);

$stripe_public_key = getenv("STRIPE_PUBLIC_KEY") ?: "pk_test_votre_cle_publique";
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
    <?php include 'accessibilite.php'; ?>

    <!-- Navigation -->
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <span class="text-orange-500 font-bold text-2xl">
            Silver Happy
        </span>

        <div class="flex gap-4">
            <a href="dashboardS.php" class="text-gray-600">
                Tableau de bord
            </a>

            <a href="logout.php" class="text-red-400">
                Déconnexion
            </a>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="max-w-3xl mx-auto mt-10 px-4">
        <h1 class="text-3xl font-bold text-orange-500 mb-2">
            Mon Abonnement
        </h1>

        <p class="text-gray-500 mb-8">
            Gérez votre adhésion Silver Happy
        </p>

        <?php if (isset($_GET['succes'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 text-lg">
                Paiement effectué ! Votre abonnement est actif.
            </div>
        <?php endif; ?>

        <section class="bg-white rounded-2xl shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">
                Statut actuel
            </h2>

            <div class="flex items-center gap-4">
                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-semibold text-lg">
                    Membre actif
                </span>

                <span class="text-gray-500">
                    Compte Silver Happy
                </span>
            </div>
        </section>

        <!-- Offres d'abonnement -->
        <section>
            <h2 class="text-2xl font-bold mb-4">
                Renouveler votre abonnement
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <article class="bg-white rounded-2xl shadow p-8 border-2 border-gray-200">
                    <h3 class="text-xl font-bold mb-2">
                        Mensuel
                    </h3>

                    <p class="text-4xl font-bold text-orange-500 mb-2">
                        4€
                        <span class="text-lg text-gray-400 font-normal">
                            / mois
                        </span>
                    </p>

                    <ul class="text-gray-600 space-y-2 mb-6 text-base">
                        <li>Accès à tous les services</li>
                        <li>Résiliable à tout moment</li>
                        <li>Support prioritaire</li>
                    </ul>

                    <button onclick="payer('mensuel')" class="w-full bg-orange-500 text-white text-lg font-bold py-4 rounded-xl">
                        Choisir Mensuel
                    </button>
                </article>

                <article class="bg-orange-50 rounded-2xl shadow p-8 border-2 border-orange-400">
                    <h3 class="text-xl font-bold mb-2">
                        Annuel
                    </h3>

                    <p class="text-4xl font-bold text-orange-500 mb-2">
                        40€
                        <span class="text-lg text-gray-400 font-normal">
                            / an
                        </span>
                    </p>

                    <ul class="text-gray-600 space-y-2 mb-6 text-base">
                        <li>2 mois offerts</li>
                        <li>Accès à tous les services</li>
                        <li>Support prioritaire</li>
                    </ul>

                    <button onclick="payer('annuel')" class="w-full bg-orange-500 text-white text-lg font-bold py-4 rounded-xl">
                        Choisir Annuel
                    </button>
                </article>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script>
    async function payer(type) {
        const btn = document.querySelector(`button[onclick="payer('${type}')"]`);
        btn.textContent = 'Redirection...';
        btn.disabled = true;

        const res = await fetch('/api/creer_subscription.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type })
        });

        const data = await res.json();

        if (data.url) {
            window.location.href = data.url;
        } else {
            alert('Erreur : ' + (data.error || 'Impossible de créer l\'abonnement'));
            btn.textContent = type === 'mensuel' ? 'Choisir Mensuel' : 'Choisir Annuel';
            btn.disabled = false;
        }
    }
</script>
</body>
</html>