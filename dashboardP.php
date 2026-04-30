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
    $stmtProfil = $pdo->prepare("SELECT * FROM prestataire WHERE id_prestataire = ?");
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
                        <p class="text-gray-400 text-sm">Aucun relevé disponible. Les relevés sont générés automatiquement le 1er de chaque mois.</p>
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
                    <button onclick="toggleModal('modalService')" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-md">+ Publier une offre</button>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-4 font-bold text-sm">
                        <?php
                        echo match ($_GET['error']) {
                            'desc_courte'     => 'La description doit faire au moins 10 caractères.',
                            'prix_invalide'   => 'Le prix doit être supérieur à 0.',
                            'dates_invalides' => 'Les dates doivent être dans le futur et cohérentes.',
                            'sql'             => 'Erreur base de données.',
                            default           => 'Une erreur est survenue.'
                        };
                        ?>
                    </div>
                <?php endif; ?>

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
                                        <i class="fa-regular fa-clock mr-1"></i> 
                                        Du <?= date('d/m/Y H:i', strtotime($srv['date_debut'])) ?> <br>
                                        Au <?= date('d/m/Y H:i', strtotime($srv['date_fin'])) ?>
                                    </div>
                                <?php endif; ?>

                                <p class="text-slate-500 text-sm mt-1 line-clamp-2"><?php echo htmlspecialchars($srv['description']); ?></p>
                                
                                <div class="mt-4 flex justify-between items-center font-bold">
                                    <span class="text-xl"><?php echo htmlspecialchars($srv['prix']); ?>€ <small class="text-xs text-slate-400">/heure</small></span>
                                    <a href="delete_service.php?id=<?php echo (int) $srv['id_service']; ?>" class="text-red-400 hover:text-red-600 transition-colors">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="planning" class="tab-content space-y-6">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Réservations reçues</h2>
                    <?php if (empty($reservations)): ?>
                        <p class="text-slate-400 italic">Aucune réservation reçue pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($reservations as $r): ?>
                            <?php
                            $debut = strtotime($r['date_reservation']);
                            $fin = !empty($r['date_fin']) ? strtotime($r['date_fin']) : null;
                            $statutClass = match ($r['statut']) {
                                'en_attente' => 'bg-yellow-100 text-yellow-700',
                                'confirme'   => 'bg-emerald-100 text-emerald-700',
                                'termine'    => 'bg-slate-100 text-slate-500',
                                'annule'     => 'bg-red-100 text-red-500',
                                default      => 'bg-slate-100 text-slate-400'
                            };
                            $statutLabel = match ($r['statut']) {
                                'en_attente' => 'En attente',
                                'confirme'   => 'Confirmé',
                                'termine'    => 'Terminé',
                                'annule'     => 'Annulé',
                                default      => $r['statut']
                            };
                            ?>
                            <div class="flex justify-between items-center p-5 border border-slate-100 rounded-2xl bg-slate-50 mb-3">
                                <div>
                                    <p class="font-bold"><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></p>
                                    <p class="text-sm text-slate-400">
                                        <?php echo date('d/m/Y à H:i', $debut); ?>
                                        <?php if ($fin): ?> → <?php echo date('H:i', $fin); ?><?php endif; ?>
                                    </p>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <span class="text-xs font-bold px-3 py-1 rounded-full <?php echo $statutClass; ?>">
                                        <?php echo htmlspecialchars($statutLabel); ?>
                                    </span>
                                    <?php if ($r['statut'] === 'en_attente'): ?>
                                        <a href="act_res.php?id=<?php echo (int) $r['id_reservation']; ?>&a=accepter" class="bg-emerald-500 text-white text-xs px-3 py-1.5 rounded-lg font-bold hover:bg-emerald-600">✓ Accepter</a>
                                        <a href="act_res.php?id=<?php echo (int) $r['id_reservation']; ?>&a=refuser" class="bg-red-100 text-red-500 text-xs px-3 py-1.5 rounded-lg font-bold hover:bg-red-200">✗ Refuser</a>
                                    <?php elseif ($r['statut'] === 'confirme'): ?>
                                        <a href="act_res.php?id=<?php echo (int) $r['id_reservation']; ?>&a=terminer" class="bg-slate-200 text-slate-600 text-xs px-3 py-1.5 rounded-lg font-bold hover:bg-slate-300">Marquer terminé</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="messages" class="tab-content space-y-6">
                <div class="bg-white p-10 rounded-senior shadow-sm border border-emerald-50 text-center text-slate-400 italic">
                    <i class="fa-solid fa-comment-dots text-4xl mb-4 block text-emerald-100"></i>
                    Messagerie bientôt disponible.
                </div>
            </div>

        
            <div id="profil" class="tab-content space-y-6">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-emerald-50">
                    <h2 class="text-2xl font-title font-bold text-emerald-800 mb-6">Mon Entreprise</h2>
                    
                    <?php if (isset($profil['statut']) && $profil['statut'] !== 'valide'): ?>
                    
                        <div class="bg-amber-50 border border-amber-200 text-amber-700 p-8 rounded-2xl text-center">
                            <i class="fa-solid fa-hourglass-half text-4xl mb-4 text-amber-400 block"></i>
                            <h3 class="font-bold text-xl mb-2">Compte en cours de vérification</h3>
                            <p class="text-sm">Votre profil et vos documents légaux sont actuellement examinés par notre équipe d'administration.</p>
                            <p class="text-sm mt-2 font-medium">Vous pourrez finaliser la configuration de votre entreprise (et renseigner votre IBAN pour les paiements) une fois votre compte validé.</p>
                        </div>
                    <?php else: ?>
                      
                        <?php if (isset($_GET['profil']) && $_GET['profil'] === 'ok'): ?>
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-6 font-bold text-sm">
                                <i class="fa-solid fa-check mr-2"></i>Vos informations ont été mises à jour.
                            </div>
                        <?php endif; ?>

                        <?php if (empty($profil['iban'])): ?>
                            <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-2xl mb-6 text-sm flex gap-3 items-start">
                                <i class="fa-solid fa-circle-info mt-1 text-blue-500"></i>
                                <div>
                                    <strong class="block mb-1">Votre compte est validé !</strong>
                                    Il ne vous reste plus qu'à renseigner votre IBAN ci-dessous pour pouvoir recevoir les paiements de vos futures prestations.
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="update_pres.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nom du responsable</label>
                                    <input type="text" value="<?php echo htmlspecialchars($nom_pres); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">Catégorie</label>
                                    <input type="text" value="<?php echo htmlspecialchars($categorie_pres); ?>" disabled class="w-full p-4 bg-slate-100 rounded-xl border-none text-slate-500 font-bold cursor-not-allowed">
                                </div>
                                <div class="space-y-1 md:col-span-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">IBAN (Requis pour les paiements) *</label>
                                    <input type="text" name="iban" value="<?php echo htmlspecialchars($profil['iban'] ?? ''); ?>" placeholder="Ex: FR76 1234 5678..." required class="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none uppercase font-mono tracking-widest">
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:bg-emerald-700 transition-all">
                                    <i class="fa-solid fa-save mr-2"></i>Enregistrer mes informations
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </section>
    </main>

    <div id="modalService" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-lg rounded-senior shadow-2xl overflow-hidden">
            <div class="bg-emerald-600 p-6 text-white flex justify-between items-center font-bold">
                <h3 class="text-xl">Planifier une Offre</h3>
                <button onclick="toggleModal('modalService')" class="text-2xl">&times;</button>
            </div>
            <form action="add_service.php" method="POST" class="p-8 space-y-4">
                <input type="text" name="nom_service" value="<?php echo htmlspecialchars($categorie_pres); ?>" readonly class="w-full p-4 bg-slate-100 border-none rounded-2xl text-slate-500 font-bold cursor-not-allowed outline-none">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="ville" placeholder="Lieu (Ville)" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    <input type="number" name="prix" placeholder="Prix total (€)" min="1" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 space-y-4">
                    <h4 class="text-xs font-bold text-emerald-800 uppercase">Disponibilité de l'offre</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Début</label>
                            <input type="datetime-local" id="dispo-debut" name="debut" required class="w-full p-3 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Fin</label>
                            <input type="datetime-local" id="dispo-fin" name="fin" required class="w-full p-3 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>
                </div>

                <textarea name="description" placeholder="Description courte (10 caractères min.)" rows="3" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-emerald-700 transition-all">Publier et Planifier</button>
            </form>
        </div>
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

        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const btn = document.querySelector(`.tab-btn[onclick*="'${hash}'"]`);
                if (btn) showTab(hash, btn);
            }
    
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const minDate = now.toISOString().slice(0, 16);
            
            const debutInput = document.getElementById('dispo-debut');
            const finInput = document.getElementById('dispo-fin');
            
            if(debutInput && finInput) {
                debutInput.min = minDate;
                finInput.min = minDate;
            }
        });
    </script>
</body>
</html>
