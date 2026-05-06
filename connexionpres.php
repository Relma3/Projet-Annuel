<?php?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Prestataire - Silver Happy</title>
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
                    borderRadius: { 'senior': '28px' }
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
                <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Recrutement</span>
            </div>
        </div>
        <ul class="hidden md:flex gap-8 font-medium text-slate-600">
            <li><a href="index.php" class="hover:text-orange-corail transition-colors">Accueil</a></li>
            <li><a href="services.php" class="hover:text-orange-corail transition-colors">Services</a></li>
            <li><a href="contact.php" class="hover:text-orange-corail transition-colors">Contact</a></li>
        </ul>
        <div class="flex gap-3">
            <a href="inscriptionpres.php" class="text-emerald-700 font-bold px-4 py-2 hover:underline">Pas de compte ?</a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 min-h-screen flex items-center justify-center relative">

        <div class="absolute top-20 -right-20 w-80 h-80 bg-vert-menthe/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 -left-20 w-80 h-80 bg-vert-menthe/10 rounded-full blur-3xl"></div>

        <div class="bg-white w-full max-w-4xl rounded-senior shadow-2xl overflow-hidden flex flex-col md:flex-row border border-slate-100 relative z-10">

            <div class="md:w-5/12 bg-vert-menthe p-10 flex flex-col justify-between text-slate-800">
                <div>
                    <h1 class="font-title text-3xl font-bold mb-4">Espace Partenaire</h1>
                    <p class="text-slate-700 text-lg">Gérez vos prestations et votre planning Silver Happy.</p>
                </div>
                <div class="bg-white/40 p-6 rounded-senior backdrop-blur-sm">
                    <p class="text-sm italic text-slate-700">"Silver Happy m'a permis de développer mon activité auprès des seniors de mon quartier."</p>
                    <p class="text-xs font-bold mt-2 text-emerald-800">— Un prestataire partenaire</p>
                </div>
                <div class="text-xs text-emerald-800 opacity-70 italic">
                    Accès sécurisé — Prestataires validés
                </div>
            </div>

            <div class="md:w-7/12 p-10 lg:p-14">
                <form action="traitement_connexion.php" method="POST" class="space-y-8">

                    <div>
                        <h2 class="font-title text-3xl font-bold text-slate-900">Connexion Pro</h2>
                        <p class="text-slate-500">Bienvenue dans votre espace professionnel.</p>

                        <?php if(isset($_GET['error'])): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 mt-3 text-sm font-bold flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                Identifiants incorrects. Vérifiez votre email et mot de passe.
                            </div>
                        <?php elseif(isset($_GET['pending'])): ?>
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 mt-3 text-sm font-bold flex items-center gap-2">
                                <i class="fa-solid fa-clock"></i>
                                Votre compte est en cours de validation par notre équipe.
                            </div>
                        <?php elseif(isset($_GET['inscrit'])): ?>
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl p-4 mt-3 text-sm font-bold flex items-center gap-2">
                                <i class="fa-solid fa-circle-check"></i>
                                Inscription envoyée ! Votre dossier est en cours d'examen.
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                        <p class="text-red-500 font-bold text-sm mt-3 bg-red-50 border border-red-100 px-4 py-3 rounded-xl">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                            <?php echo match($_GET['error']) {
                                '1'              => 'Identifiants incorrects ou compte non activé.',
                                'mauvais_compte' => 'Ce compte n\'existe pas dans cet espace. Utilisez l\'Espace Adhérent pour vous connecter.',
                                '500'            => 'Erreur serveur, veuillez réessayer.',
                                default          => 'Une erreur est survenue.'
                            }; ?>
                        </p>
                        <?php endif; ?>

                        <?php if (isset($_GET['pending'])): ?>
                        <p class="text-amber-600 font-bold text-sm mt-3 bg-amber-50 border border-amber-100 px-4 py-3 rounded-xl">
                            <i class="fa-solid fa-hourglass-half mr-2"></i>
                            Votre compte est en attente de validation par l'équipe Silver Happy.
                        </p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Email Professionnel</label>
                            <input type="email" name="email" required
                                   value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>"
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe focus:bg-white outline-none transition-all text-lg"
                                   placeholder="nom@entreprise.fr">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Mot de passe</label>
                            <div class="relative">
                                <input type="password" name="password" required id="pwd-input"
                                       class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe focus:bg-white outline-none transition-all text-lg">
                                <i class="fa-solid fa-eye absolute right-6 top-5 text-slate-400 cursor-pointer" onclick="togglePwd()"></i>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                                class="w-full bg-emerald-600 text-white py-5 rounded-senior font-bold text-xl shadow-lg hover:bg-emerald-700 transition-all">
                            Accéder à mon espace
                        </button>
                    </div>

                    <div class="text-center space-y-2">
                        <p class="text-slate-500">
                            Pas encore partenaire ? <a href="inscriptionpres.php" class="text-emerald-600 font-bold hover:underline">Candidater ici</a>
                        </p>
                        <p class="text-slate-400 text-sm">
                            Vous êtes senior ? <a href="connexion.php" class="text-orange-corail font-bold hover:underline">Espace Adhérent</a>
                        </p>
                    </div>

                    <input type="hidden" name="source" value="prestataire">
                </form>
            </div>
        </div>
    </main>

    <script>
    function togglePwd() {
        const input = document.getElementById('pwd-input');
        const icon  = document.querySelector('.fa-eye, .fa-eye-slash');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>
