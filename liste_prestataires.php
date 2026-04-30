<?php
require_once 'check_session.php';
require_once 'db_connect.php';

$cat_filter = $_GET['cat'] ?? '';

$titres = [
    'domicile'   => 'Services à domicile',
    'sante'      => 'Santé & Bien-être',
    'loisirs'    => 'Loisirs & Culture',
    'maison'     => 'Maison & Habitat',
    'formations' => 'Formations',
    'boutique'   => 'Boutique adaptée',
];
$titre_page = $cat_filter !== '' ? ($titres[$cat_filter] ?? ucfirst($cat_filter)) : 'Tous nos prestataires';

$jours = [
    'Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mer',
    'Thursday' => 'Jeu', 'Friday' => 'Ven', 'Saturday' => 'Sam', 'Sunday' => 'Dim'
];
$mois_court = [
    'January' => 'jan', 'February' => 'fév', 'March' => 'mar',
    'April' => 'avr', 'May' => 'mai', 'June' => 'juin', 'July' => 'juil',
    'August' => 'août', 'September' => 'sep', 'October' => 'oct',
    'November' => 'nov', 'December' => 'déc'
];

try {
    $sql = "
        SELECT s.*, p.prenom, p.nom, p.id_prestataire, p.note_moyenne, p.nombre_evaluations, p.ville AS ville_pres
        FROM services s
        JOIN prestataire p ON s.id_prestataire = p.id_prestataire
        WHERE p.statut = 'valide'
    ";
    $params = [];
    if ($cat_filter !== '') {
        $sql .= " AND p.categorie = ?";
        $params[] = $cat_filter;
    }
    $sql .= " ORDER BY p.note_moyenne DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $offres = $stmt->fetchAll();

    $disposByPres = [];
    if (!empty($offres)) {
        $ids = array_values(array_unique(array_column($offres, 'id_prestataire')));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmtD = $pdo->prepare("
            SELECT id_disponibilite, id_prestataire, date_debut, date_fin
            FROM disponibilites
            WHERE id_prestataire IN ($placeholders)
              AND type = 'libre'
              AND date_debut >= NOW()
            ORDER BY date_debut ASC
        ");
        $stmtD->execute($ids);
        foreach ($stmtD->fetchAll() as $d) {
            $disposByPres[$d['id_prestataire']][] = $d;
        }
    }

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titre_page); ?> — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { 'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B', 'vert-menthe': '#A0E8AF', 'peche-pastel': '#FFD9CA' },
                fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
                borderRadius: { 'senior': '28px' }
            }}
        }
    </script>
</head>
<body class="bg-sable-doux text-slate-800 font-sans">
    <?php include 'accessibilite.php'; ?>

    <nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="logo.png" alt="Silver Happy" class="h-12">
            <div class="hidden sm:block">
                <span class="text-2xl font-bold text-orange-corail block leading-none">Silver Happy</span>
                <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Bien vivre après 60 ans</span>
            </div>
        </div>
        <ul class="hidden md:flex gap-8 font-medium">
            <li><a href="index.php" class="hover:text-orange-corail transition-colors">Accueil</a></li>
            <li><a href="services.php" class="text-orange-corail font-bold">Services</a></li>
            <li><a href="contact.php" class="hover:text-orange-corail transition-colors">Contact</a></li>
        </ul>
        <div class="flex gap-3 items-center">
            <a href="<?php echo getDashboardLink(); ?>" class="bg-peche-pastel text-orange-corail px-5 py-2 rounded-senior font-bold hover:bg-orange-corail hover:text-white transition-all text-sm">
                <i class="fa-solid fa-user-circle mr-2"></i>
                <?php echo (isset($_SESSION['id']) && $_SESSION['type'] === 'senior') ? 'Mon Espace' : 'Espace Adhérent'; ?>
            </a>
            <a href="connexionpres.php" class="bg-vert-menthe/20 text-emerald-700 border border-vert-menthe px-5 py-2 rounded-senior font-bold hover:bg-vert-menthe hover:text-slate-800 transition-all text-sm">
                Espace Prestataire
            </a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto">

        <div class="flex items-center gap-4 mb-10">
            <a href="services.php" class="flex items-center gap-2 bg-white text-slate-600 px-5 py-3 rounded-senior font-bold shadow-sm hover:bg-peche-pastel hover:text-orange-corail transition-all border border-slate-100">
                <i class="fa-solid fa-chevron-left"></i> Retour aux services
            </a>
            <div>
                <h1 class="text-3xl font-title font-bold text-slate-900"><?php echo htmlspecialchars($titre_page); ?></h1>
                <p class="text-slate-500 text-sm mt-1"><?php echo count($offres); ?> prestataire<?php echo count($offres) > 1 ? 's' : ''; ?> trouvé<?php echo count($offres) > 1 ? 's' : ''; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($offres)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-senior border-2 border-dashed border-slate-200">
                    <i class="fa-solid fa-calendar-xmark text-4xl text-slate-300 mb-4 block"></i>
                    <p class="text-slate-400 italic font-medium text-lg">Aucun prestataire disponible dans cette catégorie.</p>
                    <a href="services.php" class="mt-6 inline-block bg-orange-corail text-white px-8 py-3 rounded-senior font-bold">Voir tous les services</a>
                </div>
            <?php else: foreach ($offres as $o):
                $id_pres  = $o['id_prestataire'];
                $dispos   = $disposByPres[$id_pres] ?? [];
                $nbDispos = count($dispos);
                $note     = (float)$o['note_moyenne'];
                $nbEvals  = (int)$o['nombre_evaluations'];
                $ville    = $o['ville'] ?? $o['ville_pres'] ?? 'Non précisée';
            ?>
            <div class="bg-white rounded-senior shadow-sm hover:shadow-xl transition-all border border-slate-50 flex flex-col p-8 relative">

                
                <?php if ($nbDispos === 0): ?>
                    <span class="absolute top-4 right-4 bg-slate-100 text-slate-400 text-[10px] font-bold px-3 py-1 rounded-full">Indisponible</span>
                <?php elseif ($nbDispos <= 2): ?>
                    <span class="absolute top-4 right-4 bg-orange-50 text-orange-500 text-[10px] font-bold px-3 py-1 rounded-full">
                        <i class="fa-solid fa-fire mr-1"></i><?php echo $nbDispos; ?> créneau<?php echo $nbDispos > 1 ? 'x' : ''; ?>
                    </span>
                <?php else: ?>
                    <span class="absolute top-4 right-4 bg-green-50 text-green-600 text-[10px] font-bold px-3 py-1 rounded-full">
                        <i class="fa-solid fa-circle-check mr-1"></i><?php echo $nbDispos; ?> créneaux
                    </span>
                <?php endif; ?>

                <div class="flex justify-between items-start mb-4 mt-4">
                    <span class="bg-peche-pastel text-orange-corail text-[10px] font-bold px-3 py-1 rounded-full uppercase"><?php echo htmlspecialchars($o['nom_service']); ?></span>
                    <span class="text-2xl font-bold text-slate-900"><?php echo $o['prix']; ?>€<small class="text-xs text-slate-400">/h</small></span>
                </div>

                <h2 class="text-xl font-bold text-slate-900 mb-1"><?php echo htmlspecialchars($o['prenom'] . ' ' . $o['nom']); ?></h2>

                <?php if ($nbEvals > 0): ?>
                <div class="flex items-center gap-1 mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star text-xs <?php echo $i <= round($note) ? 'text-yellow-400' : 'text-slate-200'; ?>"></i>
                    <?php endfor; ?>
                    <span class="text-xs text-slate-400 ml-1"><?php echo number_format($note, 1); ?> (<?php echo $nbEvals; ?> avis)</span>
                </div>
                <?php endif; ?>

   
                <div class="flex items-center gap-2 text-slate-400 text-[10px] mb-3 font-bold uppercase">
                    <i class="fa-solid fa-location-dot text-orange-corail"></i><?php echo htmlspecialchars($ville); ?>
                </div>

             
                <p class="text-slate-500 text-sm mb-4 italic flex-1">"<?php echo htmlspecialchars(mb_strimwidth($o['description'] ?? '', 0, 100, '...')); ?>"</p>

                <?php if ($nbDispos > 0 && isset($_SESSION['id']) && $_SESSION['type'] === 'senior'): ?>
                    <div class="mb-3">
                        <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">
                            <i class="fa-regular fa-calendar mr-1"></i>Choisir un créneau
                        </label>
                        <select id="dispo-<?php echo $o['id_service']; ?>"
                                onchange="mettreAJourLien(<?php echo $o['id_service']; ?>)"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-orange-corail outline-none">
                            <?php foreach ($dispos as $d):
                                $tsD    = strtotime($d['date_debut']);
                                $tsF    = strtotime($d['date_fin']);
                                $dureeH = round(($tsF - $tsD) / 3600, 1);
                                $label  = $jours[date('l', $tsD)] . ' ' . date('d', $tsD) . ' ' . $mois_court[date('F', $tsD)] . ' — ' . date('H:i', $tsD) . '→' . date('H:i', $tsF) . ' (' . $dureeH . 'h)';
                            ?>
                            <option value="<?php echo $d['id_disponibilite']; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="reserver.php?id_service=<?php echo $o['id_service']; ?>&id_dispo=<?php echo $dispos[0]['id_disponibilite']; ?>"
                       id="btn-<?php echo $o['id_service']; ?>"
                       class="block w-full text-center bg-orange-corail text-white py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg shadow-orange-200">
                        Réserver ce créneau
                    </a>

                <?php elseif ($nbDispos > 0): ?>
                    <a href="connexion.php" class="block w-full text-center bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-slate-800 transition-all">
                        Se connecter pour réserver
                    </a>
                <?php else: ?>
                    <button disabled class="block w-full text-center bg-slate-100 text-slate-400 py-4 rounded-2xl font-bold cursor-not-allowed">
                        Aucune disponibilité
                    </button>
                <?php endif; ?>

            </div>
            <?php endforeach; endif; ?>
        </div>
    </main>

    <footer class="bg-slate-900 text-white py-12 px-6 mt-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-slate-400">© 2026 Silver Happy — Bien vivre après 60 ans</p>
            <div class="flex gap-8">
                <a href="#" class="hover:text-orange-corail">Mentions légales</a>
                <a href="contact.php" class="hover:text-orange-corail">Aide</a>
            </div>
        </div>
    </footer>

    <script>
    function mettreAJourLien(idService) {
        const select = document.getElementById('dispo-' + idService);
        const btn    = document.getElementById('btn-' + idService);
        if (select && btn) {
            btn.href = 'reserver.php?id_service=' + idService + '&id_dispo=' + select.value;
        }
    }
    </script>

</body>
</html>
