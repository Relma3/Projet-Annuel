<?php require_once('check_session.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Silver Happy</title>
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
            <h1 class="font-title text-4xl md:text-5xl font-bold text-slate-900 mb-4">Une question ? Nous sommes là.</h1>
            <p class="text-xl text-slate-600">Notre équipe administrative basée à Paris est à votre écoute.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
            
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-senior shadow-md border border-slate-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-orange-corail/10 p-3 rounded-xl text-orange-corail">
                            <i class="fa-solid fa-location-dot fa-xl"></i>
                        </div>
                        <h3 class="font-bold text-xl">Siège Social</h3>
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        244, rue du Faubourg Saint Antoine<br>
                        75011 Paris, France
                    </p>
                </div>

                <div class="bg-white p-8 rounded-senior shadow-md border border-slate-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-vert-menthe/20 p-3 rounded-xl text-emerald-700">
                            <i class="fa-solid fa-phone fa-xl"></i>
                        </div>
                        <h3 class="font-bold text-xl">Téléphone</h3>
                    </div>
                    <p class="text-slate-600">Permanence téléphonique dédiée aux seniors.</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">01 4X XX XX XX</p>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white p-10 md:p-14 rounded-senior shadow-2xl border border-slate-100">
                <form action="traitement_contact.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Votre Nom</label>
                            <input type="text" name="nom" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Votre Email</label>
                            <input type="email" name="email" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Sujet de votre demande</label>
                        <select name="sujet" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg appearance-none cursor-pointer">
                            <option value="inscription">Aide à l'inscription</option>
                            <option value="service">Question sur une prestation</option>
                            <option value="technique">Problème technique sur le site</option>
                            <option value="autre">Autre demande</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Message</label>
                        <textarea name="message" rows="5" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg" placeholder="Comment pouvons-nous vous aider ?"></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-orange-corail text-white py-5 rounded-senior font-bold text-xl shadow-lg shadow-orange-corail/30 hover:scale-[1.01] transition-all">
                            Envoyer mon message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-slate-900 text-white py-12 px-6 mt-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-slate-400 font-medium">Silver Happy — Plateforme dédiée à la Silver Économie.</p>
            <div class="flex gap-4">
                <i class="fa-brands fa-facebook fa-xl cursor-pointer hover:text-orange-corail transition-colors"></i>
                <i class="fa-brands fa-instagram fa-xl cursor-pointer hover:text-orange-corail transition-colors"></i>
            </div>
        </div>
    </footer>

</body>
</html>