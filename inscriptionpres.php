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
            const confirm  = document.getElementsByName('confirm_password')[0].value;
            const dob      = document.getElementsByName('date_naissance')[0].value;

            if (password !== confirm) {
                alert("Les mots de passe ne correspondent pas !");
                return false;
            }
            if (password.length < 8) {
                alert("Le mot de passe doit contenir au moins 8 caractères.");
                return false;
            }
            if (dob) {
                const age = Math.floor((new Date() - new Date(dob)) / (365.25 * 24 * 3600 * 1000));
                if (age < 18) {
                    alert("Vous devez avoir au moins 18 ans pour vous inscrire.");
                    return false;
                }
            }
            return true;
        }

        function previewFile(input, previewId) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            if (file) {
                preview.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(0) + ' Ko)';
                preview.classList.remove('hidden');
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
        <a href="connexionpres.php" class="text-emerald-700 font-bold px-4 py-2 hover:underline">Déjà inscrit ?</a>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-5xl mx-auto">
        <div class="bg-white rounded-senior shadow-2xl overflow-hidden border border-slate-100">

            <div class="bg-vert-menthe p-10 text-slate-800 text-center">
                <h1 class="font-title text-3xl font-bold mb-2">Devenir Prestataire Partenaire</h1>
                <p class="text-lg opacity-90">Rejoignez notre réseau de professionnels qualifiés.</p>
            </div>

            <form action="traitement_inscription_pres.php" method="POST" enctype="multipart/form-data"
                  onsubmit="return validatePresForm()" class="p-10 md:p-14 space-y-10">

                <!-- SECTION 1 : Identité -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b border-emerald-100 pb-3">
                        <i class="fa-solid fa-user"></i> Identité personnelle
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Prénom *</label>
                            <input type="text" name="prenom" placeholder="Votre prénom" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nom *</label>
                            <input type="text" name="nom" placeholder="Votre nom" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Date de naissance *</label>
                            <input type="date" name="date_naissance" required
                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Téléphone *</label>
                            <input type="tel" name="telephone" placeholder="06 XX XX XX XX" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Adresse *</label>
                            <input type="text" name="adresse" placeholder="Numéro et nom de rue" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Ville *</label>
                            <input type="text" name="ville" placeholder="Ville d'exercice" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Email professionnel *</label>
                            <input type="email" name="email" placeholder="votre@email.com" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2 : Informations professionnelles -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b border-emerald-100 pb-3">
                        <i class="fa-solid fa-briefcase"></i> Informations professionnelles
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Numéro SIRET *</label>
                            <input type="text" name="siret" placeholder="14 chiffres" required maxlength="14"
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Raison sociale</label>
                            <input type="text" name="raison_sociale" placeholder="Nom de votre entreprise"
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Domaine d'activité *</label>
                            <select name="categorie" required
                                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                                <option value="">Choisir un domaine</option>
                                <option value="domicile">Services à domicile</option>
                                <option value="sante">Santé & Bien-être</option>
                                <option value="loisirs">Loisirs & Animation</option>
                                <option value="formations">Formation</option>
                                <option value="maison">Maison & Habitat</option>
                                <option value="boutique">Boutique / Produits</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Tarif horaire (€)</label>
                            <input type="number" name="tarif_horaire" placeholder="Ex: 25" min="1" step="0.5"
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Présentation / Bio</label>
                            <textarea name="bio" placeholder="Décrivez votre parcours, vos compétences et ce qui vous distingue..." rows="4"
                                      class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3 : Documents -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b border-emerald-100 pb-3">
                        <i class="fa-solid fa-file-shield"></i> Documents justificatifs
                    </h2>
                    <p class="text-slate-500 text-sm bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i>
                        Ces documents sont obligatoires pour la validation de votre profil. Formats acceptés : PDF, JPG, PNG (max 5 Mo chacun).
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 flex items-center gap-2">
                                <i class="fa-solid fa-scale-balanced text-emerald-600"></i>
                                Casier judiciaire (bulletin n°3) *
                            </label>
                            <div class="relative">
                                <input type="file" name="casier_judiciaire" accept=".pdf,.jpg,.jpeg,.png" required
                                       onchange="previewFile(this, 'prev_casier')"
                                       class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:border-emerald-400 outline-none cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:border-emerald-300 transition-all">
                            </div>
                            <p id="prev_casier" class="hidden text-xs text-emerald-600 font-bold ml-2"></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 flex items-center gap-2">
                                <i class="fa-solid fa-graduation-cap text-emerald-600"></i>
                                Diplôme(s) ou certification(s) *
                            </label>
                            <div class="relative">
                                <input type="file" name="diplome" accept=".pdf,.jpg,.jpeg,.png" required
                                       onchange="previewFile(this, 'prev_diplome')"
                                       class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:border-emerald-400 outline-none cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:border-emerald-300 transition-all">
                            </div>
                            <p id="prev_diplome" class="hidden text-xs text-emerald-600 font-bold ml-2"></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 flex items-center gap-2">
                                <i class="fa-solid fa-envelope-open-text text-emerald-600"></i>
                                Lettre(s) de recommandation
                                <span class="text-slate-400 font-normal text-xs">(optionnel)</span>
                            </label>
                            <div class="relative">
                                <input type="file" name="lettre_reco" accept=".pdf,.jpg,.jpeg,.png"
                                       onchange="previewFile(this, 'prev_reco')"
                                       class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:border-emerald-400 outline-none cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:border-emerald-300 transition-all">
                            </div>
                            <p id="prev_reco" class="hidden text-xs text-emerald-600 font-bold ml-2"></p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-600 flex items-center gap-2">
                                <i class="fa-solid fa-id-card text-emerald-600"></i>
                                Pièce d'identité *
                            </label>
                            <div class="relative">
                                <input type="file" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png" required
                                       onchange="previewFile(this, 'prev_id')"
                                       class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl focus:border-emerald-400 outline-none cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold hover:border-emerald-300 transition-all">
                            </div>
                            <p id="prev_id" class="hidden text-xs text-emerald-600 font-bold ml-2"></p>
                        </div>

                    </div>
                </div>

                <!-- SECTION 4 : Sécurité -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-emerald-800 border-b border-emerald-100 pb-3">
                        <i class="fa-solid fa-lock"></i> Sécurité du compte
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Mot de passe *</label>
                            <input type="password" name="password" placeholder="Minimum 8 caractères" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Confirmer le mot de passe *</label>
                            <input type="password" name="confirm_password" placeholder="Répétez le mot de passe" required
                                   class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-400 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Checkbox confirmation -->
                <div class="bg-emerald-50 p-6 rounded-2xl border-2 border-emerald-100">
                    <label class="flex items-start gap-4 cursor-pointer">
                        <input type="checkbox" name="age_confirmation" required
                               class="w-6 h-6 mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 flex-shrink-0">
                        <span class="text-emerald-900 font-medium text-sm leading-relaxed">
                            Je certifie sur l'honneur être majeur (plus de 18 ans), être légalement autorisé à exercer une activité professionnelle en France, et que tous les documents fournis sont authentiques. J'accepte les conditions générales de Silver Happy.
                        </span>
                    </label>
                </div>

                <!-- Bouton -->
                <div class="pt-4">
                    <button type="submit"
                            class="w-full bg-emerald-600 text-white py-5 rounded-senior font-bold text-xl shadow-lg hover:bg-emerald-700 transition-all">
                        <i class="fa-solid fa-paper-plane mr-2"></i>Soumettre ma candidature
                    </button>
                    <p class="text-center text-slate-400 mt-4 text-sm italic">
                        Votre dossier sera examiné par notre équipe. Vous recevrez une réponse par email.
                    </p>
                </div>

            </form>
        </div>
    </main>

</body>
</html>
