<?php
session_start();
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
                <button onclick="showTab('dashboard', this)" class="tab-btn nav-active w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold transition-all"><i class="fa-solid fa-chart-line"></i> Dashboard</button>
                <button onclick="showTab('services', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 transition-all"><i class="fa-solid fa-briefcase"></i> Mes Offres</button>
                <button onclick="showTab('planning', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 transition-all"><i class="fa-solid fa-calendar-days"></i> Planning & Dispos</button>
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
                    <h2 class="text-2xl font-title font-bold text-emerald-800">Mes Services</h2>
                    <button onclick="toggleModal('modalService')" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-md">+ Nouveau</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach($mes_services as $srv): ?>
                        <div class="bg-white p-6 rounded-senior shadow-sm border border-emerald-50">
                            <h3 class="font-bold text-lg text-emerald-700"><?php echo htmlspecialchars($srv['nom_service']); ?></h3>
                            <p class="text-xs text-emerald-500 font-bold mb-2"><?php echo htmlspecialchars($srv['ville']); ?></p>
                            <p class="text-slate-500 text-sm"><?php echo htmlspecialchars($srv['description']); ?></p>
                            <div class="mt-4 flex justify-between items-center font-bold">
                                <span><?php echo $srv['prix']; ?>€/h</span>
                                <a href="delete_service.php?id=<?php echo $srv['id_service']; ?>" class="text-red-400"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="planning" class="tab-content space-y-6">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Disponibilités</h2>
                    <form id="form-dispo" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 bg-emerald-50 p-6 rounded-2xl">
                        <input type="datetime-local" id="dispo-debut" required class="p-4 rounded-xl border-none outline-none">
                        <input type="datetime-local" id="dispo-fin" required class="p-4 rounded-xl border-none outline-none">
                        <button type="button" onclick="ajouterDispo()" class="bg-emerald-600 text-white py-4 rounded-xl font-bold">Ajouter</button>
                    </form>
                    <div id="liste-dispos" class="space-y-3">Chargement...</div>
                </div>
            </div>

            <div id="modalService" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-6">
                <div class="bg-white w-full max-w-md rounded-senior shadow-2xl overflow-hidden">
                    <div class="bg-emerald-600 p-6 text-white flex justify-between items-center font-bold">
                        <h3>Nouvelle Offre</h3>
                        <button onclick="toggleModal('modalService')">&times;</button>
                    </div>
                    <form action="add_service.php" method="POST" class="p-8 space-y-4">
                        <input type="text" name="nom_service" value="<?php echo htmlspecialchars($categorie_pres); ?>" readonly class="w-full p-4 bg-slate-100 rounded-2xl font-bold outline-none">
                        <input type="text" name="ville" placeholder="Ville" required class="w-full p-4 bg-slate-50 rounded-2xl outline-none">
                        <input type="number" name="prix" placeholder="Prix/h" min="1" required class="w-full p-4 bg-slate-50 rounded-2xl outline-none">
                        <textarea name="description" placeholder="Description" rows="3" class="w-full p-4 bg-slate-50 rounded-2xl outline-none"></textarea>
                        <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-bold">Publier</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        const PRESTA_ID = <?php echo $id_pres; ?>;

        function showTab(id, btn) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('nav-active'));
            document.getElementById(id).classList.add('active');
            btn.classList.add('nav-active');
        }

        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        async function chargerDispos() {
            const res = await fetch(`api/dispos.php?id_prestataire=${PRESTA_ID}`);
            const data = await res.json();
            const container = document.getElementById('liste-dispos');
            container.innerHTML = data.length === 0 ? 'Aucun créneau' : data.map(d => `
                <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                    <span class="text-sm">Du ${new Date(d.date_debut).toLocaleString()} au ${new Date(d.date_fin).toLocaleString()}</span>
                    <button onclick="supprimerDispo(${d.id_disponibilite})" class="text-red-400"><i class="fa-solid fa-trash"></i></button>
                </div>
            `).join('');
        }

        async function ajouterDispo() {
            const debut = document.getElementById('dispo-debut').value;
            const fin = document.getElementById('dispo-fin').value;
            const res = await fetch('api/dispos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_p: PRESTA_ID, debut, fin })
            });
            if (res.ok) { document.getElementById('form-dispo').reset(); chargerDispos(); }
            else { const err = await res.json(); alert(err.error); }
        }

        async function supprimerDispo(id) {
            if (confirm("Supprimer ?")) {
                await fetch(`api/dispos.php?id=${id}`, { method: 'DELETE' });
                chargerDispos();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const now = new Date().toISOString().slice(0, 16);
            document.getElementById('dispo-debut').min = now;
            document.getElementById('dispo-fin').min = now;
            chargerDispos();
        });
    </script>
</body>
</html>
