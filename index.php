<?php 
  require_once('check_session.php'); 
  $target_link = getDashboardLink(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silver Happy - Bien vivre après 60 ans</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sable-doux': '#F4EDDE',
                        'orange-corail': '#FF885B',
                        'vert-menthe': '#A0E8AF',
                        'peche-pastel': '#FFD9CA',
                    },
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                        'title': ['Quicksand', 'sans-serif'],
                    },
                    borderRadius: {
                        'senior': '28px',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F4EDDE; }
        h1, h2, h3 { font-family: 'Quicksand', sans-serif; }
    </style>
</head>
<body class="bg-sable-doux text-slate-800">

    <nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="logo.png" alt="Silver Happy" class="h-12">
            <div>
                <span class="text-2xl font-bold text-orange-corail block leading-none">Silver Happy</span>
                <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Bien vivre après 60 ans</span>
            </div>
        </div>
        
        <ul class="hidden md:flex gap-8 font-medium">
            <li><a href="index.php" class="hover:text-orange-corail transition-colors">Accueil</a></li>
            <li><a href="services.php" class="hover:text-orange-corail transition-colors">Services</a></li>
            <li><a href="contact.php" class="hover:text-orange-corail transition-colors">Contact</a></li>
        </ul>

        <div class="flex gap-3 items-center">
            <a href="<?php echo $target_link; ?>" class="bg-peche-pastel text-orange-corail px-5 py-2 rounded-senior font-bold hover:bg-orange-corail hover:text-white transition-all text-sm shadow-sm">
                <i class="fa-solid fa-user-circle mr-2"></i>
                <?php echo (isset($_SESSION['id']) && $_SESSION['type'] === 'senior') ? 'Mon Espace' : 'Espace Adhérent'; ?>
            </a>
            
            <a href="connexionpres.php" class="bg-vert-menthe/20 text-emerald-700 border border-vert-menthe px-5 py-2 rounded-senior font-bold hover:bg-vert-menthe hover:text-slate-800 transition-all text-sm">
                Espace Prestataire
            </a>

            <select id="lang-selector" class="bg-transparent border-none text-sm font-bold cursor-pointer ml-2 outline-none">
                <option value="fr">FR</option>
                <option value="en">EN</option>
            </select>
        </div>
    </nav>

<header class="pt-40 pb-20 px-6 max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
    <div class="md:w-1/2 space-y-6">
        <h1 class="text-5xl md:text-7xl font-bold text-slate-900 leading-tight">
            <span data-i18n="welcome_title">Bienvenue dans votre nouvelle vie.</span>
        </h1>
        <p class="text-xl text-slate-600 leading-relaxed" data-i18n="welcome_sub">
            Rejoignez notre communauté et profitez pleinement de vos années dorées grâce à des services personnalisés.
        </p>
        <div class="flex gap-4">
            <?php if(!$is_connected): ?>
                <a href="inscription.php" class="bg-orange-corail text-white px-8 py-4 rounded-senior text-lg font-bold shadow-lg shadow-orange-corail/30 hover:scale-105 transition-transform"
                   data-i18n="join_btn">
                    Rejoindre le Club
                </a>
            <?php else: ?>
                <a href="dashboardS.php" class="bg-orange-corail text-white px-8 py-4 rounded-senior text-lg font-bold shadow-lg shadow-orange-corail/30 hover:scale-105 transition-transform">
                    Voir mon planning
                </a>
            <?php endif; ?>
            <a href="#services" class="border-2 border-slate-200 px-8 py-4 rounded-senior text-lg font-bold hover:bg-slate-50 transition-colors"
               data-i18n="our_services">
                Nos Services12
            </a>
        </div>
    </div>
    <div class="md:w-1/2 relative">
        <div class="bg-orange-corail/10 absolute inset-0 rounded-full blur-3xl"></div>
        <img src="arbre.png" alt="Arbre de vie" class="relative z-10 w-full max-w-md mx-auto">
    </div>
</header>

    <section id="services" class="bg-white py-24 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <div class="mb-16">
                <h2 class="text-4xl font-bold mb-4" data-i18n="services_title">Des services pensés pour vous</h2>
                <p class="text-slate-500 text-lg" data-i18n="services_sub">Tout ce dont vous avez besoin pour un quotidien serein.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div onclick="window.location.href='services.php'" class="group bg-sable-doux p-8 rounded-senior hover:bg-vert-menthe transition-all cursor-pointer shadow-sm">
                    <div class="bg-white w-16 h-16 rounded-2xl flex items-center justify-center mb-6 shadow-sm mx-auto">
                        <i class="fa-solid fa-house-chimney text-2xl text-orange-corail"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Soins à domicile</h3>
                    <p class="text-slate-600">Ménage, repas et courses.</p>
                </div>

                <div onclick="window.location.href='services.php'" class="group bg-sable-doux p-8 rounded-senior hover:bg-peche-pastel transition-all cursor-pointer shadow-sm">
                    <div class="bg-white w-16 h-16 rounded-2xl flex items-center justify-center mb-6 shadow-sm mx-auto">
                        <i class="fa-solid fa-palette text-2xl text-orange-corail"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Loisirs & Culture</h3>
                    <p class="text-slate-600">Sorties et ateliers.</p>
                </div>

                <div onclick="window.location.href='services.php'" class="group bg-sable-doux p-8 rounded-senior hover:bg-vert-menthe transition-all cursor-pointer shadow-sm">
                    <div class="bg-white w-16 h-16 rounded-2xl flex items-center justify-center mb-6 shadow-sm mx-auto">
                        <i class="fa-solid fa-heart-pulse text-2xl text-orange-corail"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Santé & Bien-être</h3>
                    <p class="text-slate-600">RDV médicaux et conseils.</p>
                </div>

                <div onclick="window.location.href='services.php'" class="group bg-sable-doux p-8 rounded-senior hover:bg-peche-pastel transition-all cursor-pointer shadow-sm">
                    <div class="bg-white w-16 h-16 rounded-2xl flex items-center justify-center mb-6 shadow-sm mx-auto">
                        <i class="fa-solid fa-basket-shopping text-2xl text-orange-corail"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Boutique adaptée</h3>
                    <p class="text-slate-600">Produits de confort.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 px-6 max-w-7xl mx-auto">
        <div class="bg-vert-menthe/20 rounded-senior p-10 md:p-16 border-2 border-vert-menthe">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                <div class="lg:w-1/2 space-y-6 text-left">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900" data-i18n="benefits_title">
                        Avantages réservés aux membres
                    </h2>
                    <p class="text-lg text-slate-700" data-i18n="benefits_sub">
                        Privilèges exclusifs et communauté bienveillante.
                    </p>
                    <a href="<?php echo $is_connected ? 'dashboardS.php' : 'inscription.php'; ?>"
                       class="inline-block bg-orange-corail text-white px-8 py-4 rounded-senior font-bold shadow-lg hover:brightness-110 transition-all"
                       data-i18n="benefits_btn">
                        <?php echo $is_connected ? 'Mon Tableau de bord' : 'Devenir membre'; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-white py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-center md:text-left">
            <div>
                <h3 class="text-2xl font-bold">Silver Happy</h3>
                <p class="text-slate-400">© 2026 - Tous droits réservés</p>
            </div>
            <div class="flex gap-6">
                <a href="inscriptionpres.php" class="border border-slate-700 px-6 py-3 rounded-senior hover:bg-white hover:text-slate-900 transition-all font-bold">Proposer mes services</a>
                <a href="contact.php" class="bg-orange-corail px-6 py-3 rounded-senior font-bold hover:brightness-110 transition-all">Trouver de l'aide</a>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-8 pt-6 border-t border-slate-800 text-center">
            <a href="connexion_admin.php" class="text-slate-600 ...">
                <i class="fa-solid fa-shield-halved mr-1"></i> Espace administration
            </a>
        </div>
    </footer>

    <!-- Mmina -->
    <script>
const LANG_KEY = 'sh_lang';

async function loadLang(lang) {
    try {
        const res  = await fetch(`lang${lang.toUpperCase()}.json`);
        const data = await res.json();
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (data[key]) el.textContent = data[key];
        });
        localStorage.setItem(LANG_KEY, lang);
        document.getElementById('lang-selector').value = lang;
    } catch (e) {
        console.warn('Langue non disponible :', lang);
    }
}

const savedLang = localStorage.getItem(LANG_KEY) || 'fr';
if (savedLang !== 'fr') loadLang(savedLang);

document.getElementById('lang-selector').addEventListener('change', function () {
    loadLang(this.value);
});
</script>

</body>
</html>