<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Silver Happy</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'orange-corail': '#FF885B',
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
<body class="bg-slate-900 text-white font-sans min-h-screen flex items-center justify-center relative overflow-hidden">

    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-orange-corail/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-slate-700/30 rounded-full blur-3xl"></div>

    <a href="index.php" class="absolute top-6 left-6 flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors z-10">
        <i class="fa-solid fa-arrow-left"></i>
        Retour au site
    </a>

    <div class="relative z-10 w-full max-w-md px-6">

        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-corail/10 border border-orange-corail/20 rounded-2xl mb-4">
                <i class="fa-solid fa-shield-halved text-2xl text-orange-corail"></i>
            </div>
            <h1 class="font-title text-2xl font-bold text-white">Espace Administration</h1>
            <p class="text-slate-400 text-sm mt-1">Accès réservé à l'équipe Silver Happy</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-5 py-4 mb-6 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php if ($_GET['error'] == '1'): ?>
                    Identifiants incorrects. Vérifiez votre email et mot de passe.
                <?php elseif ($_GET['error'] == '403'): ?>
                    Accès refusé. Ce compte n'a pas les droits administrateur.
                <?php else: ?>
                    Une erreur est survenue. Veuillez réessayer.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-8">
            <form action="traitement_connexion.php" method="POST" class="space-y-6">

                <div>
                    <label class="block text-sm font-bold mb-2 text-slate-300">
                        <i class="fa-solid fa-envelope mr-2 text-slate-400"></i>Adresse email
                    </label>
                    <input type="email" name="email" required autofocus
                           class="w-full px-5 py-3.5 bg-slate-900/60 border border-slate-600 rounded-xl focus:border-orange-corail focus:outline-none transition-colors text-white placeholder-slate-500 text-base"
                           placeholder="admin@silverhappy.fr">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-slate-300">
                        <i class="fa-solid fa-lock mr-2 text-slate-400"></i>Mot de passe
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="pwd" required
                               class="w-full px-5 py-3.5 bg-slate-900/60 border border-slate-600 rounded-xl focus:border-orange-corail focus:outline-none transition-colors text-white placeholder-slate-500 text-base pr-12"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePwd()" class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-200 transition-colors">
                            <i class="fa-solid fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-orange-corail text-white py-4 rounded-xl font-bold text-base hover:brightness-110 transition-all shadow-lg shadow-orange-corail/20 mt-2">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Accéder au panneau d'administration
                </button>

                <input type="hidden" name="source" value="admin">
            </form>
        </div>

        <div class="text-center mt-8 space-y-2">
            <p class="text-slate-500 text-xs">
                <i class="fa-solid fa-lock text-slate-600 mr-1"></i>
                Connexion chiffrée — Accès journalisé
            </p>
            <p class="text-slate-600 text-xs">Silver Happy © 2026 — Panneau d'administration</p>
        </div>

    </div>

    <script>
    function togglePwd() {
        const pwd = document.getElementById('pwd');
        const icon = document.getElementById('eye-icon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }
    </script>

</body>
</html>