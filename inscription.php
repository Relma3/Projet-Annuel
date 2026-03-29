<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Silver Happy</title>
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

        // Fonction pour vérifier si les mots de passe correspondent
        function validateForm() {
            const password = document.getElementsByName('password')[0].value;
            const confirm = document.getElementsByName('confirm_password')[0].value;
            
            if (password !== confirm) {
                alert("Les mots de passe ne correspondent pas !");
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="bg-sable-doux text-slate-800 font-sans">

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

        <div class="flex gap-4 items-center">
            <a href="connexion.php" class="text-slate-600 font-bold hover:text-orange-corail transition-all">Connexion</a>
            <a href="connexion.php" class="bg-peche-pastel text-orange-corail px-6 py-2 rounded-senior font-bold hover:bg-orange-corail hover:text-white transition-all">Espace Adhérent</a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden">
        
        <div class="absolute top-40 -left-20 w-64 h-64 bg-orange-corail/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 -right-20 w-96 h-96 bg-vert-menthe/10 rounded-full blur-3xl"></div>

        <div class="bg-white w-full max-w-4xl rounded-senior shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 border border-slate-100">
            
            <div class="md:w-5/12 bg-orange-corail p-10 text-white flex flex-col justify-between">
                <div>
                    <h1 class="font-title text-3xl font-bold mb-4">Bienvenue au Club</h1>
                    <p class="text-orange-100 text-lg">Rejoignez Silver Happy pour accéder à des services adaptés et une communauté dynamique.</p>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-orange-200"></i>
                        <span class="text-sm">Aide à domicile </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-orange-200"></i>
                        <span class="text-sm">Loisirs & Sorties </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-orange-200"></i>
                        <span class="text-sm">Conseils de vie </span>
                    </div>
                </div>

                <div class="opacity-50 text-xs">
                    © 2026 Silver Happy - Bien vivre après 60 ans 
                </div>
            </div>

            <div class="md:w-7/12 p-10 lg:p-14">
                <form action="traitement_inscription.php" method="POST" onsubmit="return validateForm()" class="space-y-5">
                    <div class="mb-4">
                        <h2 class="font-title text-3xl font-bold text-slate-900">Créer mon compte</h2>
                        <p class="text-slate-500">L'adhésion est de seulement 40€/an.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1 ml-2 text-slate-700">Prénom</label>
                            <input type="text" name="prenom" required class="w-full px-6 py-3 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1 ml-2 text-slate-700">Nom</label>
                            <input type="text" name="nom" required class="w-full px-6 py-3 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1 ml-2 text-slate-700">Adresse Email</label>
                        <input type="email" name="email" required class="w-full px-6 py-3 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg" placeholder="ex: jean.dupont@mail.fr">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1 ml-2 text-slate-700">Mot de passe</label>
                            <input type="password" name="password" required class="w-full px-6 py-3 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1 ml-2 text-slate-700">Confirmation</label>
                            <input type="password" name="confirm_password" required class="w-full px-6 py-3 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-orange-corail text-white py-4 rounded-senior font-bold text-xl shadow-lg shadow-orange-corail/30 hover:scale-[1.02] transition-all">
                            Valider mon inscription
                        </button>
                    </div>

                    <p class="text-center text-slate-500 text-sm">
                        Déjà inscrit ? <a href="connexion.php" class="text-orange-corail font-bold hover:underline">Connectez-vous ici</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
