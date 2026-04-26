<?php
require_once 'check_session.php';
require_once 'db_connect.php';
 
if(!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}
 
$id_pres = $_SESSION['id'];
$nom_pres = $_SESSION['nom'] ?? 'Prestataire';
 
try {
    $stmtProfil = $pdo->prepare("SELECT * FROM prestataire WHERE id_prestataire = ?");
    $stmtProfil->execute([$id_pres]);
    $profil = $stmtProfil->fetch();
    $categorie_pres = $profil['categorie'] ?? 'Service';
 
    $stmtSrv = $pdo->prepare("SELECT * FROM services WHERE id_prestataire = ?");
    $stmtSrv->execute([$id_pres]);
    $mes_services = $stmtSrv->fetchAll();
 
    $stmtRes = $pdo->prepare("
        SELECT r.*, s.prenom, s.nom, s.adresse 
        FROM reservation r 
        JOIN senior s ON r.id_senior = s.id_senior 
        WHERE r.id_prestataire = ? 
        ORDER BY r.date_reservation ASC
    ");
    $stmtRes->execute([$id_pres]);
    $reservations = $stmtRes->fetchAll();
 
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Pro — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { 
            colors: { 'emerald-pro': '#059669', 'menthe-claire': '#A0E8AF', 'fond-pro': '#F0F9F4' },
            fontFamily: { 'sans': ['DM Sans', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
            borderRadius: { 'senior': '28px' }
        }}}
    </script>
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .nav-active { background: #059669 !important; color: white !important; }
    </style>
</head>
<body class="bg-[#F0F9F4] font-sans text-slate-800 min-h-screen">
 
    <nav class="fixed w-full bg-white/95 backdrop-blur-md shadow-sm z-50 px-8 py-4 flex justify-between items-center border-b border-emerald-100">
        <div class="flex items-center gap-10">
            <a href="index.php" class="flex items-center gap-2">
                <span class="text-2xl font-bold text-emerald-pro font-title">Silver Happy <span class="text-slate-400 font-light">PRO</span></span>
            </a>
            <div class="hidden md:flex gap-8 text-sm font-bold text-slate-500 uppercase">
                <a href="index.php" class="hover:text-emerald-pro transition-colors">Accueil</a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium">Expert : <strong><?php echo htmlspecialchars($nom_pres); ?></strong></span>
            <a href="logout.php" class="bg-emerald-100 text-emerald-700 h-10 w-10 flex items-center justify-center rounded-full hover:bg-emerald-600 hover:text-white transition-all">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </nav>
 
    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-senior p-6 shadow-sm sticky top-28 space-y-2 border border-emerald-50">
                <button onclick="showTab('dashboard', this)" class="tab-btn nav-active w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold transition-all"><i class="fa-solid fa-chart-line"></i> Tableau de bord</button>
                <button onclick="showTab('services', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-briefcase"></i> Mes Services / Offres</button>
                <button onclick="showTab('planning', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-calendar-days"></i> Réservations & Planning</button>
                <button onclick="showTab('messages', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-comment-dots"></i> Messages</button>
                <button onclick="showTab('profil', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-id-card"></i> Mon Entreprise</button>
            </div>
        </aside>
 
        <section class="lg:col-span-3 space-y-6">
 
            <div id="dashboard" class="tab-content active space-y-6">
                <div class="bg-emerald-600 p-10 rounded-senior shadow-lg text-white">
                    <h1 class="text-3xl font-title font-bold">Bonjour, <?php echo htmlspecialchars($nom_pres); ?></h1>
                    <p class="opacity-90 mt-2">Vous avez <?php echo count($reservations); ?> mission(s) prévue(s).</p>
                </div>
            </div>
 
            <div id="services" class="tab-content space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-title font-bold text-emerald-800">Mes Offres de Services</h2>
                    <button onclick="toggleModal('modalService')" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-md">
                        + Ajouter un service
                    </button>
                </div>
 
                <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-4 font-bold text-sm">
                    <?php
                    echo match($_GET['error']) {
                        'desc_courte'   => 'La description doit faire au moins 10 caractères.',
                        'prix_invalide' => 'Le prix doit être supérieur à 0.',
                        'sql'           => 'Erreur base de données.',
                        default         => 'Une erreur est survenue.'
                    };
                    ?>
                </div>
                <?php endif; ?>
 
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'service_ajoute'): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-4 font-bold text-sm">
                    <i class="fa-solid fa-check mr-2"></i>Service publié avec succès !
                </div>
                <?php endif; ?>
 
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if(empty($mes_services)): ?>
                        <div class="col-span-2 bg-white p-10 rounded-senior text-center border-2 border-dashed border-emerald-200">
                            <p class="text-slate-400 italic">Vous n'avez pas encore publié d'offres.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($mes_services as $srv): ?>
                            <div class="bg-white p-6 rounded-senior shadow-sm border border-emerald-50 relative group">
                                <h3 class="font-bold text-lg text-emerald-700"><?php echo htmlspecialchars($srv['nom_service']); ?></h3>
                                <p class="text-xs text-emerald-500 font-bold mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($srv['ville'] ?? 'Non précisée'); ?></p>
                                <p class="text-slate-500 text-sm mt-1"><?php echo htmlspecialchars($srv['description']); ?></p>
                                <div class="mt-4 flex justify-between items-center font-bold">
                                    <span class="text-xl"><?php echo $srv['prix']; ?>€ <small class="text-xs text-slate-400">/heure</small></span>
                                    <a href="delete_service.php?id=<?php echo $srv['id_service']; ?>" class="text-red-400 hover:text-red-600 transition-colors"><i class="fa-solid fa-trash-can"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
 
            <div id="planning" class="tab-content space-y-6">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Gérer mes disponibilités</h2>
                    
                    <form id="form-dispo" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 bg-emerald-50/50 p-6 rounded-2xl">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-2">Début</label>
                            <input type="datetime-local" id="dispo-debut" required class="w-full p-4 rounded-xl border-none focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase ml-2">Fin</label>
                            <input type="datetime-local" id="dispo-fin" required class="w-full p-4 rounded-xl border-none focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="ajouterDispo()" class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg">
                                Ajouter
                            </button>
                        </div>
                    </form>
 
                    <div class="space-y-4">
                        <h3 class="font-bold text-slate-600 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-emerald-500"></i> Vos créneaux
                        </h3>
                        <div id="liste-dispos" class="grid grid-cols-1 gap-3 text-sm">
                            <p class="text-slate-400 italic">Chargement...</p>
                        </div>
                    </div>
                </div>
            </div>
 
            <div id="modalService" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-6">
                <div class="bg-white w-full max-w-md rounded-senior shadow-2xl overflow-hidden">
                    <div class="bg-emerald-600 p-6 text-white flex justify-between items-center font-bold">
                        <h3 class="text-xl">Nouvelle Offre</h3>
                        <button onclick="toggleModal('modalService')" class="text-2xl">&times;</button>
                    </div>
                    <form action="add_service.php" method="POST" class="p-8 space-y-4">
                        <input type="text" name="nom_service" value="<?php echo htmlspecialchars($categorie_pres); ?>" readonly class="w-full p-4 bg-slate-100 border-none rounded-2xl text-slate-500 font-bold cursor-not-allowed outline-none">
                        <input type="text" name="ville" placeholder="Ville d'exercice" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        <input type="number" name="prix" placeholder="Prix par heure (€)" min="1" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        <textarea name="description" placeholder="Description courte (10 caractères min.)" rows="3" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                        <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-emerald-700 transition-all">Publier l'offre</button>
                    </form>
                </div>
            </div>
 
        </section>
    </main>
 
    <script>
        const PRESTA_ID = <?php echo $id_pres; ?>;
 
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
 
        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }
 
        async function chargerDispos() {
            try {
                const res = await fetch(`api/dispos.php?id_prestataire=${PRESTA_ID}`);
                const data = await res.json();
                const container = document.getElementById('liste-dispos');
                container.innerHTML = data.length === 0 ? 'Aucun créneau.' : data.map(d => `
                    <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span>Du ${new Date(d.date_debut).toLocaleString('fr-FR')} au ${new Date(d.date_fin).toLocaleString('fr-FR')}</span>
                        <button onclick="supprimerDispo(${d.id_disponibilite})" class="text-red-400"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `).join('');
            } catch (e) { console.error(e); }
        }
 
        async function ajouterDispo() {
            const debut = document.getElementById('dispo-debut').value;
            const fin = document.getElementById('dispo-fin').value;
            if(!debut || !fin) return alert("Dates requises");
 
            const res = await fetch('api/dispos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_p: PRESTA_ID, debut, fin })
            });
 
            if(res.ok) { document.getElementById('form-dispo').reset(); chargerDispos(); }
            else { const err = await res.json(); alert(err.error); }
        }
 
        async function supprimerDispo(id) {
            if(!confirm("Supprimer ce créneau ?")) return;
            await fetch(`api/dispos.php?id=${id}`, { method: 'DELETE' });
            chargerDispos();
        }
 
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const btn = document.querySelector(`.tab-btn[onclick*="'${hash}'"]`);
                if (btn) showTab(hash, btn);
            }
 
            const now = new Date().toISOString().slice(0, 16);
            document.getElementById('dispo-debut').min = now;
            document.getElementById('dispo-fin').min = now;
            chargerDispos();
        });
    </script>
</body>
</html>
