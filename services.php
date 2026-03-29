<?php require_once('check_session.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Services - Silver Happy</title>
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
</head>
<body class="bg-sable-doux text-slate-800 font-sans">

    <nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="logo.png" alt="Silver Happy" class="h-12">
            <div class="hidden sm:block">
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
            <a href="<?php echo getDashboardLink(); ?>" class="bg-peche-pastel text-orange-corail px-5 py-2 rounded-senior font-bold hover:bg-orange-corail hover:text-white transition-all text-sm">
                <i class="fa-solid fa-user-circle mr-2"></i>
                <?php echo (isset($_SESSION['id']) && $_SESSION['type'] === 'senior') ? 'Mon Espace' : 'Espace Adhérent'; ?>
            </a>
            
            <a href="connexionpres.php" class="bg-vert-menthe/20 text-emerald-700 border border-vert-menthe px-5 py-2 rounded-senior font-bold hover:bg-vert-menthe hover:text-slate-800 transition-all text-sm">
                Espace Prestataire
            </a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto">
        
        <div class="text-center mb-16">
            <h1 class="font-title text-4xl md:text-5xl font-bold text-slate-900 mb-4">Des services pensés pour vous</h1>
            <p class="text-slate-500 text-xl max-w-2xl mx-auto">
                Tout ce dont vous avez besoin pour un quotidien serein et une vie sociale épanouie.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <a href="prestation_details.php?cat=domicile" class="group bg-white p-8 rounded-senior hover:bg-peche-pastel transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-peche-pastel w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-house-chimney text-3xl text-orange-corail"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Services à domicile</h3>
                <p class="text-slate-600 text-lg mb-4">Ménage, repassage, portage de repas et aide aux courses.</p>
                <span class="text-orange-corail font-bold flex items-center gap-2">
                    Voir les détails <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

            <a href="prestation_details.php?cat=loisirs" class="group bg-white p-8 rounded-senior hover:bg-vert-menthe/20 transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-vert-menthe/30 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-palette text-3xl text-emerald-700"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Loisirs & Culture</h3>
                <p class="text-slate-600 text-lg mb-4">Événements, soirées de rencontres, visites et conférences.</p>
                <span class="text-emerald-700 font-bold flex items-center gap-2">
                    Voir les détails <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

            <a href="prestation_details.php?cat=sante" class="group bg-white p-8 rounded-senior hover:bg-peche-pastel transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-peche-pastel w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-heart-pulse text-3xl text-orange-corail"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Santé & Bien-être</h3>
                <p class="text-slate-600 text-lg mb-4">Télérendez-vous médicaux et conseils de vie adaptés.</p>
                <span class="text-orange-corail font-bold flex items-center gap-2">
                    Voir les détails <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

            <a href="prestation_details.php?cat=habitat" class="group bg-white p-8 rounded-senior hover:bg-vert-menthe/20 transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-vert-menthe/30 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-screwdriver-wrench text-3xl text-emerald-700"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Maison & Habitat</h3>
                <p class="text-slate-600 text-lg mb-4">Petit bricolage, jardinage et amélioration de l'habitat.</p>
                <span class="text-emerald-700 font-bold flex items-center gap-2">
                    Voir les détails <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

            <a href="prestation_details.php?cat=formation" class="group bg-white p-8 rounded-senior hover:bg-peche-pastel transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-peche-pastel w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-graduation-cap text-3xl text-orange-corail"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Formations</h3>
                <p class="text-slate-600 text-lg mb-4">Cours d'informatique, de langues et ateliers thématiques.</p>
                <span class="text-orange-corail font-bold flex items-center gap-2">
                    Voir les détails <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

            <a href="boutique.php" class="group bg-white p-8 rounded-senior hover:bg-vert-menthe/20 transition-all shadow-sm hover:shadow-xl border border-slate-100">
                <div class="bg-vert-menthe/30 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-basket-shopping text-3xl text-emerald-700"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Boutique adaptée</h3>
                <p class="text-slate-600 text-lg mb-4">Articles de confort et équipements spécialisés.</p>
                <span class="text-emerald-700 font-bold flex items-center gap-2">
                    Accéder à la boutique <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </span>
            </a>

        </div>

        <div class="mt-20 bg-orange-corail rounded-senior p-10 text-white flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="md:w-2/3">
                <h2 class="text-3xl font-bold mb-4 font-title">Besoin d'un service spécifique ?</h2>
                <p class="text-xl opacity-90">Nos prestations évoluent chaque année en fonction de vos demandes. Contactez-nous pour une offre sur-mesure.</p>
            </div>
            <a href="contact.php" class="bg-white text-orange-corail px-8 py-4 rounded-senior font-bold text-lg hover:scale-105 transition-transform">
                Nous contacter
            </a>
        </div>

    </main>

    <footer class="bg-slate-900 text-white py-12 px-6 mt-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-slate-400">© 2026 Silver Happy — Bien vivre après 60 ans</p>
            <div class="flex gap-8">
                <a href="#" class="hover:text-orange-corail">Mentions légales</a>
                <a href="contact.php" class="hover:text-orange-corail">Aide</a>
            </div>
        </div>
    </footer>

</body>
</html>