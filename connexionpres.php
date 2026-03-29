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
                    colors: { 'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B', 'vert-menthe': '#A0E8AF', 'peche-pastel': '#FFD9CA' },
                    fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
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
            <a href="inscriptionpres.php" class="text-emerald-700 font-bold px-4 py-2 hover:underline">Pas de Compte ?</a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 min-h-screen flex items-center justify-center relative">
        <div class="bg-white w-full max-w-4xl rounded-senior shadow-2xl overflow-hidden flex flex-col md:flex-row border border-slate-100">
            
            <div class="md:w-5/12 bg-vert-menthe p-10 flex flex-col justify-between text-slate-800">
                <div>
                    <h1 class="font-title text-3xl font-bold mb-4">Espace Partenaire</h1>
                    <p class="text-slate-700 text-lg">Gérez vos prestations et votre planning Silver Happy.</p>
                </div>
                <div class="text-xs text-emerald-800 opacity-70 italic">Accès sécurisé — Prestataires validés</div>
            </div>

            <div class="md:w-7/12 p-10 lg:p-14">
                <form action="traitement_connexion.php" method="POST" class="space-y-8">
                    <div>
                        <h2 class="font-title text-3xl font-bold text-slate-900">Connexion Pro</h2>
                        <?php if(isset($_GET['error'])): ?>
                            <p class="text-red-500 font-bold text-sm mt-2">Identifiants incorrects ou compte non activé.</p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Email Professionnel</label>
                            <input type="email" name="email" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe focus:bg-white outline-none transition-all text-lg" placeholder="nom@entreprise.fr">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 ml-2 text-slate-700">Mot de passe</label>
                            <input type="password" name="password" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe focus:bg-white outline-none transition-all text-lg">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-emerald-600 text-white py-5 rounded-senior font-bold text-xl shadow-lg hover:bg-emerald-700 transition-all">Accéder à mon espace</button>
                    </div>
                    <input type="hidden" name="source" value="prestataire">
                </form>
            </div>
        </div>
    </main>

</body>
</html>
