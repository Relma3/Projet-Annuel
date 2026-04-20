<?php
require_once 'check_session.php';
$target_link = getDashboardLink();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silver Happy</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Roboto, sans-serif;
            background: #F4EDDE;
        }
    </style>
</head>
<body class="text-slate-800">

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <img src="logo.png" alt="Silver Happy" class="h-12">
        <div>
            <span class="text-2xl font-bold text-orange-400 block">Silver Happy</span>
            <span class="text-xs text-slate-400">Bien vivre après 60 ans</span>
        </div>
    </div>

    <ul class="hidden md:flex gap-6">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>

    <div class="flex gap-3 items-center">
        <a href="<?php echo $target_link; ?>" class="bg-orange-100 text-orange-500 px-4 py-2 rounded-full font-bold text-sm">
            <?php
            if (isset($_SESSION['id']) && $_SESSION['type'] == 'senior') {
                echo 'Mon Espace';
            } else {
                echo 'Espace Adherent';
            }
            ?>
        </a>

        <a href="connexionpres.php" class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-bold text-sm">
            Espace Prestataire
        </a>

        <select id="lang-selector" class="text-sm">
            <option value="fr">FR</option>
            <option value="en">EN</option>
        </select>
    </div>
</nav>

<header class="py-20 px-6 max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-10">
    <div class="md:w-1/2">
        <h1 class="text-5xl font-bold mb-6" data-i18n="welcome_title">
            Bienvenue dans votre nouvelle vie.
        </h1>

        <p class="text-xl text-slate-600 mb-6" data-i18n="welcome_sub">
            Rejoignez notre communauté et profitez de services personnalisés.
        </p>

        <div class="flex gap-4">
            <?php if (!$is_connected) { ?>
                <a href="inscription.php" class="bg-orange-400 text-white px-6 py-3 rounded-full font-bold" data-i18n="join_btn">
                    Rejoindre le Club
                </a>
            <?php } else { ?>
                <a href="dashboardS.php" class="bg-orange-400 text-white px-6 py-3 rounded-full font-bold">
                    Voir mon planning
                </a>
            <?php } ?>

            <a href="#services" class="border px-6 py-3 rounded-full font-bold" data-i18n="our_services">
                Nos Services
            </a>
        </div>
    </div>

    <div class="md:w-1/2">
        <img src="arbre.png" alt="Arbre de vie" class="w-full max-w-md mx-auto">
    </div>
</header>

<section id="services" class="bg-white py-20 px-6">
    <div class="max-w-6xl mx-auto text-center">
        <h2 class="text-4xl font-bold mb-4" data-i18n="services_title">Des services pensés pour vous</h2>
        <p class="text-slate-500 text-lg mb-12" data-i18n="services_sub">Tout ce dont vous avez besoin.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div onclick="window.location.href='services.php'" class="bg-orange-50 p-6 rounded-2xl cursor-pointer">
                <h3 class="text-xl font-bold mb-2">Soins a domicile</h3>
                <p class="text-slate-600">Menage, repas et courses.</p>
            </div>

            <div onclick="window.location.href='services.php'" class="bg-orange-50 p-6 rounded-2xl cursor-pointer">
                <h3 class="text-xl font-bold mb-2">Loisirs et Culture</h3>
                <p class="text-slate-600">Sorties et ateliers.</p>
            </div>

            <div onclick="window.location.href='services.php'" class="bg-orange-50 p-6 rounded-2xl cursor-pointer">
                <h3 class="text-xl font-bold mb-2">Sante et Bien-etre</h3>
                <p class="text-slate-600">RDV medicaux et conseils.</p>
            </div>

            <div onclick="window.location.href='services.php'" class="bg-orange-50 p-6 rounded-2xl cursor-pointer">
                <h3 class="text-xl font-bold mb-2">Boutique adaptee</h3>
                <p class="text-slate-600">Produits de confort.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20 px-6 max-w-6xl mx-auto">
    <div class="bg-green-100 rounded-2xl p-10">
        <h2 class="text-3xl font-bold mb-4" data-i18n="benefits_title">Avantages réservés aux membres</h2>
        <p class="text-lg text-slate-700 mb-6" data-i18n="benefits_sub">
            Privileges exclusifs et communaute bienveillante.
        </p>

        <a href="<?php echo $is_connected ? 'dashboardS.php' : 'inscription.php'; ?>" class="inline-block bg-orange-400 text-white px-6 py-3 rounded-full font-bold" data-i18n="benefits_btn">
            <?php
            if ($is_connected) {
                echo 'Mon Tableau de bord';
            } else {
                echo 'Devenir membre';
            }
            ?>
        </a>
    </div>
</section>

<footer class="bg-slate-900 text-white py-12 px-6">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6 text-center">
        <div>
            <h3 class="text-2xl font-bold">Silver Happy</h3>
            <p class="text-slate-400">© 2026 - Tous droits réservés</p>
        </div>

        <div class="flex gap-4">
            <a href="inscriptionpres.php" class="border border-slate-700 px-5 py-3 rounded-full font-bold">Proposer mes services</a>
            <a href="contact.php" class="bg-orange-400 px-5 py-3 rounded-full font-bold">Trouver de l aide</a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto mt-8 text-center">
        <a href="connexion_admin.php" class="text-slate-500">Espace administration</a>
    </div>
</footer>

<script>
const LANG_KEY = "sh_lang";

async function loadLang(lang) {
    try {
        const res = await fetch("lang" + lang.toUpperCase() + ".json");
        const data = await res.json();

        document.querySelectorAll("[data-i18n]").forEach(function(el) {
            const key = el.getAttribute("data-i18n");
            if (data[key]) {
                el.textContent = data[key];
            }
        });

        localStorage.setItem(LANG_KEY, lang);
        document.getElementById("lang-selector").value = lang;
    } catch (e) {
    }
}

let savedLang = localStorage.getItem(LANG_KEY);
if (!savedLang) {
    savedLang = "fr";
}

if (savedLang != "fr") {
    loadLang(savedLang);
}

document.getElementById("lang-selector").addEventListener("change", function() {
    loadLang(this.value);
});
</script>

<script src="frontend/tutoriel.js"></script>
<script src="onesignal.js"></script>
</body>
</html>