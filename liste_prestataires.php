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
    'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'
];
$mois = [
    'January' => 'janvier', 'February' => 'février', 'March' => 'mars',
    'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet',
    'August' => 'août', 'September' => 'septembre', 'October' => 'octobre',
    'November' => 'novembre', 'December' => 'décembre'
];

try {
    $sql = "
        SELECT 
            s.*,
            p.prenom, p.nom, p.id_prestataire, p.note_moyenne, p.nombre_evaluations, p.ville AS ville_pres,
            d.id_disponibilite, d.date_debut, d.date_fin, d.type AS dispo_type
        FROM services s
        JOIN prestataire p ON s.id_prestataire = p.id_prestataire
        LEFT JOIN disponibilites d ON d.id_service = s.id_service
            AND d.type = 'libre'
            AND d.date_debut >= NOW()
        WHERE p.statut = 'valide'
    ";

    $params = [];
    if ($cat_filter !== '') {
        $sql .= " AND p.categorie = ?";
        $params[] = $cat_filter;
    }

    $sql .= " ORDER BY p.note_moyenne DESC, d.date_debut ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $offres = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur liste_prestataires: " . $e->getMessage());
    http_response_code(500);
    die("Erreur serveur");
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
                <p class="text-slate-500 text-sm mt-1">
                    <?php echo count($offres); ?> offre<?php echo count($offres) > 1 ? 's' : ''; ?> trouvée<?php echo count($offres) > 1 ? 's' : ''; ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($offres)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-senior border-2 border-dashed border-slate-200">
                    <i class="fa-solid fa-calendar-xmark text-4xl text-slate-300 mb-4 block"></i>
                    <p class="text-slate-400 italic font-medium text-lg">Aucun service disponible dans cette catégorie.</p>
                    <a href="services.php" class="mt-6 inline-block bg-orange-corail text-white px-8 py-3 rounded-senior font-bold">Voir tous les services</a>
                </div>
            <?php else: foreach ($offres as $o):
                $note    = (float)$o['note_moyenne'];
                $nbEvals = (int)$o['nombre_evaluations'];
                $ville   = $o['ville'] ?? $o['ville_pres'] ?? 'Non précisée';
                $hasDispo = !empty($o['id_disponibilite']);

                $dispoLabel = null;
                if ($hasDispo) {
                    $tsD = strtotime($o['date_debut']);
                    $tsF = strtotime($o['date_fin']);
                    $dureeH = round(($tsF - $tsD) / 3600, 1);
                    $dispoLabel = $jours[date('l', $tsD)] . ' ' . date('d', $tsD) . ' ' . $mois[date('F', $tsD)] . ' ' . date('Y', $tsD)
                        . ' · ' . date('H:i', $tsD) . ' → ' . date('H:i', $tsF)
                        . ' (' . $dureeH . 'h)';
                }
            ?>
            <div class="bg-white rounded-senior shadow-sm hover:shadow-xl transition-all border border-slate-50 flex flex-col p-8 relative">

                <?php if (!$hasDispo): ?>
                    <span class="absolute top-4 right-4 bg-slate-100 text-slate-400 text-[10px] font-bold px-3 py-1 rounded-full">Indisponible</span>
                <?php else: ?>
                    <span class="absolute top-4 right-4 bg-green-50 text-green-600 text-[10px] font-bold px-3 py-1 rounded-full">
                        <i class="fa-solid fa-circle-check mr-1"></i>Disponible
                    </span>
                <?php endif; ?>

                <div class="flex justify-between items-start mb-4 mt-4">
                    <span class="bg-peche-pastel text-orange-corail text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                        <?php echo htmlspecialchars($o['nom_service']); ?>
                    </span>
                    <span class="text-2xl font-bold text-slate-900">
                        <?php echo $o['prix']; ?>€<small class="text-xs text-slate-400">/h</small>
                    </span>
                </div>

                <h2 class="text-xl font-bold text-slate-900 mb-1">
                    <?php echo htmlspecialchars($o['prenom'] . ' ' . $o['nom']); ?>
                </h2>

             
                <?php if ($nbEvals > 0): ?>
                <div class="flex items-center gap-1 mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star text-xs <?php echo $i <= round($note) ? 'text-yellow-400' : 'text-slate-200'; ?>"></i>
                    <?php endfor; ?>
                    <span class="text-xs text-slate-400 ml-1"><?php echo number_format($note, 1); ?> (<?php echo $nbEvals; ?> avis)</span>
                </div>
                <?php endif; ?>

                
                <div class="flex items-center gap-2 text-slate-400 text-[10px] mb-3 font-bold uppercase">
                    <i class="fa-solid fa-location-dot text-orange-corail"></i>
                    <?php echo htmlspecialchars($ville); ?>
                </div>
                <p class="text-slate-500 text-sm mb-4 italic flex-1">
                    "<?php echo htmlspecialchars(mb_strimwidth($o['description'] ?? '', 0, 100, '...')); ?>"
                </p>

                <?php if ($hasDispo): ?>
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 mb-4">
                    <p class="text-xs font-bold text-emerald-700">
                        <i class="fa-regular fa-calendar-check mr-1"></i>Créneau disponible
                    </p>
                    <p class="text-sm font-bold text-slate-700 mt-1"><?php echo $dispoLabel; ?></p>
                </div>
                <?php endif; ?>

                <?php if ($hasDispo && isset($_SESSION['id']) && $_SESSION['type'] === 'senior'): ?>
                    <a href="reserver.php?id_service=<?php echo $o['id_service']; ?>&id_dispo=<?php echo $o['id_disponibilite']; ?>"
                       class="block w-full text-center bg-orange-corail text-white py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg shadow-orange-200">
                        Réserver ce service
                    </a>
                <?php elseif ($hasDispo): ?>
                    <a href="connexion.php"
                       class="block w-full text-center bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-slate-800 transition-all">
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

</body>
</html>
