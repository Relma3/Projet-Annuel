<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Silver Happy</title>
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
            <a href="inscription.php" class="bg-peche-pastel text-orange-corail px-6 py-2 rounded-senior font-bold hover:bg-orange-corail hover:text-white transition-all">S'inscrire</a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 min-h-screen flex items-center justify-center relative overflow-hidden">
        
        <div class="absolute top-20 -right-20 w-80 h-80 bg-orange-corail/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 -left-20 w-80 h-80 bg-peche-pastel/20 rounded-full blur-3xl"></div>

        <div class="bg-white w-full max-w-4xl rounded-senior shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 border border-slate-100">
            
            <div class="md:w-5/12 bg-orange-corail p-10 flex flex-col justify-between text-white">
                <div>
                    <h1 class="font-title text-3xl font-bold mb-4">Ravi de vous revoir</h1>
                    <p class="text-orange-100 text-lg">Connectez-vous à votre espace personnel pour gérer vos activités.</p>
                </div>
                
                <div class="bg-white/20 p-6 rounded-senior backdrop-blur-sm">
                    <p class="text-sm italic text-white">"Grâce à Silver Happy, je reste connecté avec mes amis et je trouve facilement de l'aide."</p>
                    <p class="text-xs font-bold mt-2 text-orange-200">— Un adhérent heureux</p>
                </div>

                <div class="text-xs text-orange-200 opacity-70">
                    Connexion sécurisée — Adhérents
                </div>
            </div>

            <div class="md:w-7/12 p-10 lg:p-14">
                <form action="traitement_connexion.php" method="POST" class="space-y-8">
                    <div>
                        <h2 class="font-title text-3xl font-bold text-slate-900">Espace Adhérent</h2>
                        <p class="text-slate-500">Heureux de vous retrouver parmi nous.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Adresse Email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg"
                                   placeholder="votre@email.fr">
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2 px-2 text-slate-700">
                                <label class="text-sm font-bold">Mot de passe</label>
                                <a href="#" class="text-xs text-orange-corail font-bold hover:underline">Oublié ?</a>
                            </div>
                            <div class="relative">
                                <input type="password" name="password" required 
                                       class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-orange-corail focus:bg-white outline-none transition-all text-lg">
                                <i class="fa-solid fa-eye absolute right-6 top-5 text-slate-400 cursor-pointer"></i>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-orange-corail text-white py-5 rounded-senior font-bold text-xl shadow-lg shadow-orange-corail/30 hover:scale-[1.02] transition-all">
                            Se connecter
                        </button>
                    </div>

                    <div class="text-center space-y-4">
                        <p class="text-slate-500">
                            Pas encore membre ? <a href="inscription.php" class="text-orange-corail font-bold hover:underline">S'inscrire ici</a>
                        </p>
                    </div>
                    <input type="hidden" name="source" value="senior">
                </form>
            </div>
        </div>
    </main>

</body>
</html>