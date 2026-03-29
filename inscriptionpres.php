<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature Prestataire - Silver Happy</title>
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

        function validatePresForm() {
            const password = document.getElementsByName('password')[0].value;
            const confirm = document.getElementsByName('confirm_password')[0].value;
            const ageCheck = document.getElementsByName('age_confirmation')[0].checked;
            
            if (password !== confirm) {
                alert("Les mots de passe ne correspondent pas !");
                return false;
            }
            if (!ageCheck) {
                alert("Vous devez confirmer que vous avez plus de 18 ans.");
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
                <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Recrutement</span>
            </div>
        </div>
        <ul class="hidden md:flex gap-8 font-medium text-slate-600">
            <li><a href="index.php" class="hover:text-orange-corail transition-colors">Accueil</a></li>
            <li><a href="services.php" class="hover:text-orange-corail transition-colors">Services</a></li>
            <li><a href="contact.php" class="hover:text-orange-corail transition-colors">Contact</a></li>
        </ul>
        <div class="flex gap-3">
            <a href="connexionpres.php" class="text-emerald-700 font-bold px-4 py-2 hover:underline">Déjà inscrit ?</a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-5xl mx-auto">
        <div class="bg-white rounded-senior shadow-2xl overflow-hidden border border-slate-100">
            <div class="bg-vert-menthe p-10 text-slate-800 text-center">
                <h1 class="font-title text-3xl font-bold mb-2">Devenir Prestataire Partenaire</h1>
                <p class="text-lg opacity-90">Rejoignez notre réseau de professionnels qualifiés.</p>
            </div>

            <form action="traitement_inscription_pres.php" method="POST" onsubmit="return validatePresForm()" class="p-10 md:p-14 space-y-8">
                
                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b pb-2">
                        <i class="fa-solid fa-user"></i> Informations professionnelles
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="nom" placeholder="Nom complet ou Entreprise" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                        <input type="email" name="email" placeholder="Email professionnel" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                        <input type="text" name="siret" placeholder="Numéro SIRET" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                        <select name="categorie" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                            <option value="">Domaine d'activité</option>
                            <option value="sante">Santé & Thérapeute</option>
                            <option value="maison">Aide à domicile / Bricolage</option>
                            <option value="loisirs">Animation / Coach</option>
                            <option value="formation">Formateur</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b pb-2">
                        <i class="fa-solid fa-lock"></i> Sécurité
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="password" name="password" placeholder="Mot de passe" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                        <input type="password" name="confirm_password" placeholder="Confirmer mot de passe" required class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-senior focus:border-vert-menthe outline-none text-lg">
                    </div>
                </div>

                <div class="bg-emerald-50 p-6 rounded-senior border-2 border-emerald-100">
                    <label class="flex items-center gap-4 cursor-pointer">
                        <input type="checkbox" name="age_confirmation" required class="w-6 h-6 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-emerald-900 font-medium">
                            Je certifie sur l'honneur être majeur (plus de 18 ans) et être légalement autorisé à exercer une activité professionnelle.
                        </span>
                    </label>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-emerald-600 text-white py-5 rounded-senior font-bold text-xl shadow-lg hover:bg-emerald-700 transition-all">
                        Valider ma candidature
                    </button>
                    <p class="text-center text-slate-400 mt-6 text-sm italic">
                        Les justificatifs (Casier judiciaire, Diplômes) vous seront demandés ultérieurement pour la validation de votre profil.
                    </p>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
