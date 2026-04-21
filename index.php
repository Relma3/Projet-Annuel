<?php
require_once('check_session.php');
$target_link = getDashboardLink();?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silver Happy</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="accessibilite.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F4EDDE; }
        h1, h2, h3 { font-family: 'Quicksand', sans-serif; }
    </style>
</head>
<body class="text-slate-800">
    <nav class="w-full bg-white shadow px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="logo.png" alt="Silver Happy" class="h-12">
            <div>
                <span class="text-2xl font-bold text-orange-400 block">Silver Happy</span>
                <span class="text-xs text-slate-400">Bien vivre après 60 ans</span>
            </div>
        </div>
        <ul class="hidden md:flex gap-6">
            <li><a href="index.php" data-i18n="nav_home">Accueil</a></li>
            <li><a href="services.php" data-i18n="nav_services">Services</a></li>
            <li><a href="contact.php" data-i18n="nav_contact">Contact</a></li>
        </ul>
        <div class="flex gap-3 items-center">
            <a href="<?php echo $target_link; ?>" class="bg-orange-100 text-orange-500 px-4 py-2 rounded-full font-bold text-sm"
               data-i18n="<?php echo (isset($_SESSION['id']) && $_SESSION['type'] === 'senior') ? 'nav_my_space' : 'nav_member_space'; ?>">
                <?php echo (isset($_SESSION['id']) && $_SESSION['type'] === 'senior') ? 'Mon Espace' : 'Espace Adhérent'; ?>
            </a>
            <a href="connexionpres.php" class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-bold text-sm" data-i18n="nav_provider_space">
                Espace Prestataire
            </a>
            <select id="lang-selector" class="text-sm border rounded px-2 py-1" aria-label="Langue">
                <option value="fr">FR</option>
                <option value="en">EN</option>
            </select>
        </div>
    </nav>
 
    <header class="py-20 px-6 max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-10">
        <div class="md:w-1/2">
            <h1 class="text-5xl font-bold mb-6" data-i18n="welcome_title">Bienvenue dans votre nouvelle vie.</h1>
            <p class="text-xl text-slate-600 mb-6" data-i18n="welcome_sub">
                Rejoignez notre communauté et profitez de services personnalisés.
            </p>
            <div class="flex gap-4 flex-wrap">
                <?php if (!$is_connected) { ?>
                    <a href="inscription.php" class="bg-orange-400 text-white px-6 py-3 rounded-full font-bold" data-i18n="join_btn">
                        Rejoindre le Club
                    </a>
                <?php } else { ?>
                    <a href="dashboardS.php" class="bg-orange-400 text-white px-6 py-3 rounded-full font-bold" data-i18n="see_planning">
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
                <a href="services.php" class="block bg-orange-50 p-6 rounded-3xl hover:shadow-lg transition-shadow">
                    <i class="fa-solid fa-house-chimney text-2xl text-orange-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2" data-i18n="card_domicile_title">Soins à domicile</h3>
                    <p class="text-slate-600" data-i18n="card_domicile_sub">Ménage, repas et courses.</p>
                </a>
                <a href="services.php" class="block bg-orange-50 p-6 rounded-3xl hover:shadow-lg transition-shadow">
                    <i class="fa-solid fa-palette text-2xl text-orange-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2" data-i18n="card_loisirs_title">Loisirs et Culture</h3>
                    <p class="text-slate-600" data-i18n="card_loisirs_sub">Sorties et ateliers.</p>
                </a>
                <a href="services.php" class="block bg-orange-50 p-6 rounded-3xl hover:shadow-lg transition-shadow">
                    <i class="fa-solid fa-heart-pulse text-2xl text-orange-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2" data-i18n="card_sante_title">Santé et Bien-être</h3>
                    <p class="text-slate-600" data-i18n="card_sante_sub">RDV médicaux et conseils.</p>
                </a>
                <a href="services.php" class="block bg-orange-50 p-6 rounded-3xl hover:shadow-lg transition-shadow">
                    <i class="fa-solid fa-basket-shopping text-2xl text-orange-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2" data-i18n="card_boutique_title">Boutique adaptée</h3>
                    <p class="text-slate-600" data-i18n="card_boutique_sub">Produits de confort.</p>
                </a>
            </div>
        </div>
    </section>
 
    <section class="py-20 px-6 max-w-6xl mx-auto">
        <div class="bg-green-100 rounded-3xl p-10">
            <h2 class="text-3xl font-bold mb-4" data-i18n="benefits_title">Avantages réservés aux membres</h2>
            <p class="text-lg text-slate-700 mb-6" data-i18n="benefits_sub">
                Privilèges exclusifs et communauté bienveillante.
            </p>
            <a href="<?php echo $is_connected ? 'dashboardS.php' : 'inscription.php'; ?>" class="inline-block bg-orange-400 text-white px-6 py-3 rounded-full font-bold"
               data-i18n="<?php echo $is_connected ? 'benefits_btn_member' : 'benefits_btn_join'; ?>">
                <?php echo $is_connected ? 'Mon Tableau de bord' : 'Devenir membre'; ?>
            </a>
        </div>
    </section>
 
    <footer class="bg-slate-900 text-white py-12 px-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6 text-center">
            <div>
                <h3 class="text-2xl font-bold">Silver Happy</h3>
                <p class="text-slate-400" data-i18n="footer_copy">© 2026 - Tous droits réservés</p>
            </div>
            <div class="flex gap-4">
                <a href="inscriptionpres.php" class="border border-slate-700 px-5 py-3 rounded-full font-bold" data-i18n="footer_provide">Proposer mes services</a>
                <a href="contact.php" class="bg-orange-400 px-5 py-3 rounded-full font-bold" data-i18n="footer_help">Trouver de l'aide</a>
            </div>
        </div>
        <div class="max-w-6xl mx-auto mt-8 text-center">
            <a href="connexion_admin.php" class="text-slate-500" data-i18n="footer_admin">
                Espace administration
            </a>
        </div>
    </footer>
 
    <script src="i18n.js"></script>
    <script src="accessibilite.js"></script>
    <script src="frontend/tutoriel.js"></script>
</body>
</html>
