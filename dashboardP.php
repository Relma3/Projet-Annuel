<?php
require_once 'check_session.php';
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

$id_pres = $_SESSION['id'];
$nom_pres = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));

if (empty($nom_pres)) {
    $nom_pres = 'Prestataire';
}

try {
    $stmtProfil = $pdo->prepare("
        SELECT p.*, u.email 
        FROM prestataire p
        JOIN utilisateur u ON p.id_prestataire = u.id_utilisateur
        WHERE p.id_prestataire = ?
    ");
    $stmtProfil->execute([$id_pres]);
    $profil = $stmtProfil->fetch();

    $categorie_pres = $profil['categorie'] ?? 'Service';

    $stmtSrv = $pdo->prepare("
        SELECT s.*, d.date_debut, d.date_fin 
        FROM services s
        LEFT JOIN disponibilites d ON s.id_service = d.id_service
        WHERE s.id_prestataire = ?
        ORDER BY d.date_debut ASC
    ");
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

    $stmtFact = $pdo->prepare("SELECT * FROM factures WHERE id_prestataire = ? ORDER BY annee DESC, mois DESC");
    $stmtFact->execute([$id_pres]);
    $factures_presta = $stmtFact->fetchAll();

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
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'emerald-pro': '#059669', 'menthe-claire': '#A0E8AF', 'fond-pro': '#F0F9F4' },
                    fontFamily: { sans: ['DM Sans', 'sans-serif'], title: ['Quicksand', 'sans-serif'] },
                    borderRadius: { senior: '28px' }
                }
            }
        };
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

    <main class="pt-32 pb-20 px-4 max-w-screen-xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-6">

        <aside class="lg:col-span-1">
            <div class="bg-white rounded-senior p-6 shadow-sm sticky top-28 space-y-2 border border-emerald-50">
                <button onclick="showTab('dashboard', this)" class="tab-btn nav-active w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold transition-all"><i class="fa-solid fa-chart-line"></i> Tableau de bord</button>
                <button onclick="showTab('services', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-briefcase"></i> Mes Offres & Dispos</button>
                <button onclick="showTab('planning', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-calendar-days"></i> Réservations reçues</button>
                <button onclick="showTab('messages', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-comment-dots"></i> Messages</button>
                <button onclick="showTab('profil', this)" class="tab-btn w-full flex items-center gap-4 p-4 rounded-2xl text-left font-bold text-slate-400 hover:bg-emerald-50 hover:text-emerald-700 transition-all"><i class="fa-solid fa-id-card"></i> Mon Entreprise</button>
            </div>
        </aside>

        <section class="lg:col-span-3 space-y-6 w-full">

            <div id="dashboard" class="tab-content active space-y-6">
                <div class="bg-emerald-600 p-10 rounded-senior shadow-lg text-white w-full">
                    <h1 class="text-3xl font-title font-bold">Bonjour, <?php echo htmlspecialchars($nom_pres); ?></h1>
                    <p class="opacity-90 mt-2">Vous avez <?php echo count($reservations); ?> mission(s) prévue(s).</p>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 w-full">
                    <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="fa-solid fa-file-invoice mr-2"></i>Mes relevés mensuels</h2>
                    <?php if (empty($factures_presta)): ?>
                        <p class="text-gray-400 text-sm">Aucun relevé disponible.</p>
                    <?php else: ?>
                        <ul class="space-y-2">
                            <?php foreach ($factures_presta as $f): ?>
                                <li class="flex items-center justify-between bg-emerald-50 rounded-xl px-4 py-3">
                                    <span class="text-sm font-medium text-gray-700">📄 <?= htmlspecialchars($f['numero_facture']) ?></span>
                                    <span class="text-xs text-gray-400 mx-4"><?= str_pad($f['mois'], 2, '0', STR_PAD_LEFT) ?>/<?= $f['annee'] ?> — Net : <?= number_format($f['montant_net_cents'] / 100, 2, ',', ' ') ?> €</span>
                                    <a href="telecharger_facture.php?id=<?= $f['id_facture'] ?>" target="_blank" class="text-sm text-emerald-600 font-semibold hover:underline">Télécharger</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div id="services" class="tab-content space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-title font-bold text-emerald-800">Mes Offres Planifiées</h2>
                    <?php if (isset($profil['statut']) && $profil['statut'] === 'valide' && !empty($profil['iban'])): ?>
                        <button onclick="toggleModal('modalService')" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-md">+ Publier une offre</button>
                    <?php endif; ?>
                </div>

                <?php if (isset($profil['statut']) && $profil['statut'] !== 'valide'): ?>
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 p-8 rounded-2xl text-center shadow-sm">
                        <i class="fa-solid fa-hourglass-half text-4xl mb-4 text-amber-400 block"></i>
                        <h3 class="font-bold text-xl mb-2">Fonctionnalité bloquée</h3>
                        <p class="text-sm">Votre compte est en cours de vérification. Vous pourrez publier vos offres une fois que l'équipe d'administration aura validé vos documents.</p>
                    </div>
                <?php elseif (empty($profil['iban'])): ?>
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-8 rounded-2xl text-center shadow-sm">
                        <i class="fa-solid fa-building-columns text-4xl mb-4 text-blue-400 block"></i>
                        <h3 class="font-bold text-xl mb-2">Configuration requise</h3>
                        <p class="text-sm">Veuillez renseigner votre <strong>IBAN</strong> dans l'onglet "Mon Entreprise" pour pouvoir créer des offres et recevoir des paiements.</p>
                    </div>
                <?php else: ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'service_ajoute'): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-4 font-bold text-sm">
                            <i class="fa-solid fa-check mr-2"></i>Offre et disponibilité publiées avec succès !
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if (empty($mes_services)): ?>
                            <div class="col-span-2 bg-white p-10 rounded-senior text-center border-2 border-dashed border-emerald-200">
                                <p class="text-slate-400 italic">Vous n'avez pas encore publié d'offres.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($mes_services as $srv): ?>
                                <div class="bg-white p-6 rounded-senior shadow-sm border border-emerald-50 relative group">
                                    <h3 class="font-bold text-lg text-emerald-700"><?php echo htmlspecialchars($srv['nom_service']); ?></h3>
                                    <p class="text-xs text-emerald-500 font-bold mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($srv['ville'] ?? 'Non précisée'); ?></p>
                                    
                                    <?php if($srv['date_debut']): ?>
                                        <div class="bg-emerald-50 text-emerald-800 text-xs font-bold p-3 rounded-xl mb-3 border border-emerald-100">
                                            <i class="fa-regular fa-clock mr-1"></i> Du <?= date('d/m/Y H:i', strtotime($srv['date_debut'])) ?> au <?= date('d/m/Y H:i', strtotime($srv['date_fin'])) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-4 flex justify-between items-center font-bold">
                                        <span class="text-xl"><?php echo htmlspecialchars($srv['prix']); ?>€ <small class="text-xs text-slate-400">/heure</small></span>
                                        <a href="delete_service.php?id=<?php echo (int) $srv['id_service']; ?>" class="text-red-400 hover:text-red-600 transition-colors"><i class="fa-solid fa-trash-can"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="planning" class="tab-content space-y-6">
               
                 <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Réservations reçues</h2>
                    <p class="text-slate-400 italic">Module de réservation</p>
                </div>
            </div>

            <div id="messages" class="tab-content space-y-6">
                <div class="bg-white p-10 rounded-senior shadow-sm border border-emerald-50 text-center text-slate-400 italic">
                    <i class="fa-solid fa-comment-dots text-4xl mb-4 block text-emerald-100"></i> Messagerie bientôt disponible.
                </div>
            </div>

            
            <div id="profil" class="tab-content space-y-6">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Mon Entreprise</h2>
                    
                    <?php if (isset($profil['statut']) && $profil['statut'] !== 'valide'): ?>
                        <div class="bg-amber-50 border border-amber-200 text-amber-700 p-6 rounded-2xl text-center mb-8">
                            <i class="fa-solid fa-hourglass-half text-3xl mb-2 text-amber-400 block"></i>
                            <h3 class="font-bold text-lg mb-1">Vérification en cours</h3>
                            <p class="text-sm">Votre profil et vos documents légaux sont examinés par notre équipe. Vous pourrez enregistrer votre IBAN une fois validé.</p>
                        </div>
                    <?php elseif (empty($profil['iban'])): ?>
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-2xl mb-8 text-sm flex gap-3 items-start">
                            <i class="fa-solid fa-circle-info mt-1 text-blue-500"></i>
                            <div>
                                <strong class="block mb-1">Dernière étape !</strong>
                                Votre compte est validé. Renseignez votre IBAN ci-dessous pour débloquer la création d'offres.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['profil']) && $_GET['profil'] === 'ok'): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-6 font-bold text-sm">
                            <i class="fa-solid fa-check mr-2"></i>Votre IBAN a été enregistré avec succès !
                        </div>
                    <?php endif; ?>

                    
                    <form action="update_pres.php" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Prénom</label>
                                <input type="text" value="<?php echo htmlspecialchars($profil['prenom'] ?? ''); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>
                            
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nom</label>
                                <input type="text" value="<?php echo htmlspecialchars($profil['nom'] ?? ''); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Email de connexion</label>
                                <input type="text" value="<?php echo htmlspecialchars($profil['email'] ?? ''); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Téléphone</label>
                                <input type="text" value="<?php echo htmlspecialchars($profil['telephone'] ?? ''); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Catégorie</label>
                                <input type="text" value="<?php echo htmlspecialchars($categorie_pres); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">SIRET</label>
                                <input type="text" value="<?php echo htmlspecialchars($profil['siret'] ?? ''); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="space-y-1 md:col-span-2">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">IBAN (Paiements)</label>
                                <input type="text" name="iban" value="<?php echo htmlspecialchars($profil['iban'] ?? ''); ?>" 
                                       placeholder="FR76..." 
                                       <?php echo (isset($profil['statut']) && $profil['statut'] !== 'valide') ? 'disabled' : 'required'; ?> 
                                       class="w-full p-4 rounded-xl outline-none uppercase font-mono tracking-widest transition-all <?php echo (isset($profil['statut']) && $profil['statut'] !== 'valide') ? 'bg-slate-100 border-none text-slate-400 cursor-not-allowed' : 'bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-emerald-500'; ?>">
                            </div>
                        </div>

                        <?php if (isset($profil['statut']) && $profil['statut'] === 'valide'): ?>
                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:bg-emerald-700 transition-all">
                                    <i class="fa-solid fa-save mr-2"></i>Enregistrer mon IBAN
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>

                </div>
            </div>

        </section>
    </main>

    <div id="modalService" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-6">
  
    </div>

    <script>
        function showTab(id, btn) {
            const tab = document.getElementById(id);
            if (!tab) return;
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('nav-active'); b.classList.add('text-slate-400'); });
            tab.classList.add('active');
            btn.classList.add('nav-active');
            btn.classList.remove('text-slate-400');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const btn = document.querySelector(`.tab-btn[onclick*="'${hash}'"]`);
                if (btn) showTab(hash, btn);
            }
        });
    </script>
</body>
</html>
