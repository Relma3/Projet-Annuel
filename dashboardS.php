<?php
session_start();
require_once 'db_connect.php';

// Vérification session EN PREMIER
if(!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

// Token généré APRÈS
require_once 'api/middleware.php';
$jwt_token = generer_token($_SESSION['id'], $_SESSION['type']);

$id_senior = $_SESSION['id'];
$prenom_user = $_SESSION['prenom'] ?? 'Senior';

try {
    $stmtPlan = $pdo->prepare("
        SELECT r.*, p.prenom as p_prenom, p.nom as p_nom, p.ville as p_ville
        FROM reservation r
        JOIN prestataire p ON r.id_prestataire = p.id_prestataire
        WHERE r.id_senior = ? AND r.statut IN ('en_attente', 'confirme')
        ORDER BY r.date_reservation ASC
    ");
    $stmtPlan->execute([$id_senior]);
    $planning = $stmtPlan->fetchAll();
    
    $stmtCmd = $pdo->prepare("
        SELECT * FROM commandes 
        WHERE id_senior = ? 
        ORDER BY created_at DESC
    ");
    $stmtCmd->execute([$id_senior]);
    $commandes = $stmtCmd->fetchAll();
    $stmtProfil = $pdo->prepare("SELECT * FROM senior WHERE id_senior = ?");
    $stmtProfil->execute([$id_senior]);
    $profil = $stmtProfil->fetch();
    $stmtConseils = $pdo->prepare("SELECT * FROM conseil WHERE visible = 1 ORDER BY created_at DESC LIMIT 6");
    $stmtConseils->execute();
    $conseils = $stmtConseils->fetchAll();
    $stmtRdv = $pdo->prepare("
    SELECT r.*, p.nom as medecin_nom, p.prenom as medecin_prenom, p.specialite
    FROM rdv_medical r
    JOIN prestataire p ON r.id_prestataire = p.id_prestataire
    WHERE r.id_senior = ? AND r.statut != 'annule'
    ORDER BY r.date_rdv ASC
    ");
    $stmtRdv->execute([$id_senior]);
    $rdv_medicaux = $stmtRdv->fetchAll();
    $stmtMedecins = $pdo->query("
        SELECT id_prestataire, nom, prenom, specialite 
        FROM prestataire 
        WHERE statut = 'valide' AND est_medecin = 1
    ");
    $medecins = $stmtMedecins->fetchAll();
    $stmtEvents = $pdo->prepare("
    SELECT e.id, e.titre, e.date_debut as date_event, e.lieu, 'evenement' as type_rdv
    FROM inscription_evenement ie
    JOIN evenements e ON ie.id_evenement = e.id
    WHERE ie.id_senior = ? AND e.date_debut >= NOW()
    ORDER BY e.date_debut ASC
    ");
    $stmtEvents->execute([$id_senior]);
    $evenements_inscrits = $stmtEvents->fetchAll();

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace Senior — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { 
            colors: { 'corail': '#E37A55', 'sable': '#F4EDDE', 'peche-pale': '#FDF0EB' },
            fontFamily: { 'sans': ['DM Sans', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
            borderRadius: { 'senior': '28px' }
        }}}
    </script>
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .nav-active { background: #E37A55 !important; color: white !important; box-shadow: 0 10px 15px -3px rgba(227, 122, 85, 0.2); }
    </style>

    <script>
    localStorage.setItem('token', '<?php echo $jwt_token; ?>');
</script>

</head>
<body class="bg-sable font-sans text-slate-800">
    <?php include 'accessibilite.php'; ?>

    <nav class="fixed w-full bg-white/95 backdrop-blur-md shadow-md z-50 px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-10">
            <a href="index.php" class="flex items-center gap-3">
                <span class="text-2xl font-bold text-corail font-title">Silver Happy</span>
            </a>
            
            <div class="hidden md:flex gap-8 text-sm font-bold text-slate-500 uppercase tracking-widest">
                <a href="index.php" class="hover:text-corail transition-colors">Accueil</a>
                <a href="boutique.php" class="hover:text-corail transition-colors">Boutique</a>
                <a href="services.php" class="hover:text-corail transition-colors">Services</a>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-slate-500 hidden sm:block">Bonjour, <strong><?php echo htmlspecialchars($prenom_user); ?></strong></span>
            <a href="logout.php" class="bg-peche-pale text-corail h-10 w-10 flex items-center justify-center rounded-full hover:bg-corail hover:text-white transition-all shadow-sm">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-senior p-6 shadow-sm sticky top-28 space-y-2 border border-slate-100">
                <button onclick="showTab('actu', this)" class="tab-btn nav-active w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold transition-all"><i class="fa-solid fa-house"></i> Vue d'ensemble</button>
                <button onclick="showTab('planning', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-calendar-check"></i> Mes RDV</button>
                <button onclick="showTab('commandes', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-box"></i> Mes Achats</button>
                <button onclick="showTab('messages', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-comment-dots"></i> Messagerie</button>
                <button onclick="showTab('abonnement', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-id-card"></i> Mon Abonnement</button>
                <button onclick="showTab('conseils', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-lightbulb"></i> Conseils
</button>
                <button onclick="showTab('profil', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-peche-pale hover:text-corail transition-all"><i class="fa-solid fa-user"></i> Mon Profil</button>
            </div>
        </aside>

        <section class="lg:col-span-3 space-y-6">

            <div id="actu" class="tab-content active space-y-6">
                <div class="bg-corail p-10 rounded-senior shadow-lg text-white">
                    <h1 class="text-3xl font-title font-bold">Ravi de vous voir, <?php echo htmlspecialchars($prenom_user); ?></h1>
                    <p class="opacity-80 mt-2 italic">Votre espace personnel Silver Happy est prêt.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-8 rounded-senior shadow-sm border-l-8 border-corail">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Prochain RDV</p>
                        <p class="text-lg font-bold">
                            <?php 
                            $prochain = null;
                            if (!empty($planning)) $prochain = $planning[0]['date_reservation'];
                            if (!empty($rdv_medicaux) && (!$prochain || $rdv_medicaux[0]['date_rdv'] < $prochain))
                                $prochain = $rdv_medicaux[0]['date_rdv'];
                            if (!empty($evenements_inscrits) && (!$prochain || $evenements_inscrits[0]['date_event'] < $prochain))
                                $prochain = $evenements_inscrits[0]['date_event'];
                            echo $prochain ? date('H:i - d/m', strtotime($prochain)) : 'Aucun rendez-vous à venir';
                            ?>
                        </p>
                    </div>
                </div>
            </div>

    <div id="planning" class="tab-content space-y-6">
    <h2 class="text-2xl font-title font-bold text-corail">Mes Rendez-vous</h2>

    <!-- RDV Prestataires -->
    <h3 class="font-bold text-slate-600">Services réservés</h3>
    <div class="bg-white rounded-senior shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <tr><th class="p-6">Date</th><th class="p-6">Prestataire</th><th class="p-6">Statut</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($planning)): ?>
                <tr><td colspan="3" class="p-6 text-center text-slate-300 italic">Aucun service réservé</td></tr>
                <?php else: foreach($planning as $p): ?>
                <tr>
                    <td class="p-6 font-bold"><?php echo date('d/m/Y à H:i', strtotime($p['date_reservation'])); ?></td>
                    <td class="p-6"><?php echo htmlspecialchars($p['p_prenom'] . ' ' . $p['p_nom']); ?></td>
                    <td class="p-6">
                        <span class="text-[10px] font-bold uppercase px-3 py-1 rounded-full <?php echo $p['statut'] == 'confirme' ? 'bg-emerald-100 text-emerald-600' : 'bg-orange-100 text-orange-600'; ?>">
                            <?php echo $p['statut']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RDV Médicaux -->
    <h3 class="font-bold text-slate-600 mt-8">Télé-rendez-vous médicaux</h3>
    <div class="bg-white rounded-senior shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <tr><th class="p-6">Date</th><th class="p-6">Médecin</th><th class="p-6">Spécialité</th><th class="p-6">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($rdv_medicaux)): ?>
                <tr><td colspan="4" class="p-6 text-center text-slate-300 italic">Aucun RDV médical planifié</td></tr>
                <?php else: foreach($rdv_medicaux as $r): ?>
                <tr>
                    <td class="p-6 font-bold"><?php echo date('d/m/Y à H:i', strtotime($r['date_rdv'])); ?></td>
                    <td class="p-6">Dr <?php echo htmlspecialchars($r['medecin_prenom'] . ' ' . $r['medecin_nom']); ?></td>
                    <td class="p-6 italic text-slate-400"><?php echo htmlspecialchars($r['specialite'] ?? '—'); ?></td>
                    <td class="p-6">
                        <button onclick="annulerRdv(<?php echo $r['id_rdv']; ?>)" 
                            class="text-xs text-red-400 hover:text-red-600 font-bold">
                            Annuler
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Événements inscrits -->
<h3 class="font-bold text-slate-600 mt-8">Mes événements</h3>
<div class="bg-white rounded-senior shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            <tr><th class="p-6">Date</th><th class="p-6">Événement</th><th class="p-6">Lieu</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($evenements_inscrits)): ?>
            <tr><td colspan="3" class="p-6 text-center text-slate-300 italic">Aucun événement à venir</td></tr>
            <?php else: foreach($evenements_inscrits as $e): ?>
            <tr>
                <td class="p-6 font-bold"><?php echo date('d/m/Y à H:i', strtotime($e['date_event'])); ?></td>
                <td class="p-6"><?php echo htmlspecialchars($e['titre']); ?></td>
                <td class="p-6 italic text-slate-400"><?php echo htmlspecialchars($e['lieu'] ?? '—'); ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

    <!-- Formulaire prendre un RDV -->
    <h3 class="font-bold text-slate-600 mt-8">Prendre un télé-rendez-vous</h3>
    <div class="bg-white p-8 rounded-senior shadow-sm space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase">Médecin</label>
                <select id="select-medecin" class="w-full bg-peche-pale/50 p-4 rounded-2xl font-bold text-corail outline-none">
                    <option value="">-- Choisir un médecin --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?php echo $m['id_prestataire']; ?>">
                        Dr <?php echo htmlspecialchars($m['prenom'] . ' ' . $m['nom']); ?> — <?php echo htmlspecialchars($m['specialite'] ?? ''); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase">Date et heure</label>
                <input type="datetime-local" id="input-date-rdv" 
                    class="w-full bg-peche-pale/50 p-4 rounded-2xl font-bold text-corail outline-none">
            </div>
        </div>
        <button onclick="prendreRdv()" 
            class="bg-corail text-white px-10 py-4 rounded-2xl font-bold shadow-xl hover:scale-105 transition-transform">
            Confirmer le rendez-vous
        </button>
        <p id="rdv-message" class="text-sm font-bold hidden"></p>
    </div>
</div>

            <div id="commandes" class="tab-content space-y-6">
                <h2 class="text-2xl font-title font-bold text-corail">Mes Commandes Boutique</h2>
                <div class="bg-white rounded-senior shadow-sm overflow-hidden text-sm">
                    <?php if(empty($commandes)): ?>
                        <p class="p-10 text-center text-slate-300 italic">Vous n'avez pas encore passé de commande.</p>
                    <?php else: ?>
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-400 font-bold uppercase">
                                <tr><th class="p-6">Article</th><th class="p-6">Prix</th><th class="p-6">Statut</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($commandes as $c): ?>
                                <tr>
                                    <td class="p-6 font-medium"><?php echo htmlspecialchars($c['nom_article']); ?></td>
                                    <td class="p-6 font-bold"><?php echo number_format($c['prix'], 2); ?> €</td>
                                    <td class="p-6 italic"><?php echo $c['statut']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div id="messages" class="tab-content space-y-6">
                <h2 class="text-2xl font-title font-bold text-corail">Ma Messagerie</h2>
                <div class="bg-white p-20 rounded-senior shadow-sm text-center text-slate-300 italic border border-slate-50">
                    <i class="fa-solid fa-comment-dots text-4xl mb-4 block text-slate-200"></i>
                    Bientôt disponible pour échanger avec vos prestataires.
                </div>
            </div>

            <div id="abonnement" class="tab-content space-y-6">
                <h2 class="text-2xl font-title font-bold text-corail">Mon Adhésion</h2>
                <div class="bg-white p-10 rounded-senior shadow-sm border-l-8 border-orange-400">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-slate-500 uppercase font-bold">Statut du compte</p>
                            <p class="text-2xl font-bold text-slate-800">Membre Silver Happy</p>
                            <p class="text-emerald-500 font-bold mt-1"><i class="fa-solid fa-check"></i> Adhésion 2026 active</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Prochain renouvellement</p>
                            <p class="font-bold">01/01/2027</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="profil" class="tab-content space-y-6">
                <h2 class="text-2xl font-title font-bold text-corail">Mes Informations</h2>
                <form id="form-profil" class="bg-white p-10 rounded-senior shadow-sm space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Téléphone</label>
                            <input type="text" name="telephone" value="<?php echo htmlspecialchars($profil['telephone'] ?? ''); ?>" class="w-full bg-peche-pale/50 p-4 rounded-2xl border-none font-bold text-corail focus:ring-2 focus:ring-corail outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Adresse</label>
                                <input type="text" name="adresse" value="<?php echo htmlspecialchars($profil['adresse'] ?? ''); ?>" class="w-full bg-peche-pale/50 p-4 rounded-2xl border-none font-bold text-corail focus:ring-2 focus:ring-corail outline-none">
                        </div>
                    </div>
                    <button type="submit" class="bg-corail text-white px-10 py-4 rounded-2xl font-bold shadow-xl hover:scale-105 transition-transform">
                        Enregistrer les modifications
                    </button>
                </form>
            </div>

            <div id="conseils" class="tab-content space-y-6">
    <h2 class="text-2xl font-title font-bold text-corail">Espace Conseils</h2>
    <?php if (empty($conseils)): ?>
        <div class="bg-white p-10 rounded-senior shadow-sm text-center text-slate-300 italic">
            Aucun conseil disponible pour le moment.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach($conseils as $c): ?>
            <div class="bg-white p-8 rounded-senior shadow-sm border-l-8 border-corail">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <?php echo htmlspecialchars($c['categorie'] ?? 'Général'); ?>
                </span>
                <h3 class="text-xl font-bold mt-2 mb-3"><?php echo htmlspecialchars($c['titre']); ?></h3>
                <p class="text-slate-500 leading-relaxed"><?php echo nl2br(htmlspecialchars($c['contenu'])); ?></p>
                <?php if ($c['auteur']): ?>
                    <p class="text-xs text-slate-400 mt-4 italic">Par <?php echo htmlspecialchars($c['auteur']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

        </section>
    </main>

    <script>
        function showTab(id, btn) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('nav-active');
                b.classList.add('text-slate-400');
            });
            document.getElementById(id).classList.add('active');
            btn.classList.add('nav-active');
            btn.classList.remove('text-slate-400');
        }
    </script>

    <script>
    const tutorielDejavu = <?php echo $profil['tutoriel_vu'] ? 'true' : 'false'; ?>;
</script>

<script src="frontend/tutoriel.js"></script>

<script>
document.getElementById('form-profil').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = await fetch('/api/seniors/me', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
            telephone: document.querySelector('[name=telephone]').value,
            adresse: document.querySelector('[name=adresse]').value
        })
    });
    if (res.ok) {
        window.location.href = 'dashboardS.php?msg=profil_mis_a_jour#profil';
    } else {
        alert('Erreur lors de la mise à jour du profil.');
    }
});
</script>

<script>
const token = localStorage.getItem('token');

async function prendreRdv() {
    const id_prestataire = document.getElementById('select-medecin').value;
    const date_rdv = document.getElementById('input-date-rdv').value;
    const msg = document.getElementById('rdv-message');

    if (!id_prestataire || !date_rdv) {
        msg.textContent = 'Veuillez choisir un médecin et une date.';
        msg.className = 'text-sm font-bold text-red-500';
        msg.classList.remove('hidden');
        return;
    }

    const res = await fetch('/api/rdv', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
        body: JSON.stringify({ id_prestataire, date_rdv })
    });

    if (res.ok) {
        msg.textContent = 'RDV confirmé ! Rechargement...';
        msg.className = 'text-sm font-bold text-emerald-500';
        msg.classList.remove('hidden');
        setTimeout(() => location.reload(), 1500);
    } else {
        msg.textContent = 'Erreur lors de la prise de RDV.';
        msg.className = 'text-sm font-bold text-red-500';
        msg.classList.remove('hidden');
    }
}

async function annulerRdv(id_rdv) {
    if (!confirm('Confirmer l\'annulation ?')) return;
    const res = await fetch('/api/rdv/' + id_rdv + '/annuler', {
        method: 'PUT',
        headers: { 'Authorization': 'Bearer ' + token }
    });
    if (res.ok) location.reload();
}
</script>

</body>
</html>
