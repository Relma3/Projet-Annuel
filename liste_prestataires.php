<?php
session_start();
require_once 'db_connect.php';
 
try {
    $stmtCat = $pdo->query("SELECT * FROM categories_prestations ORDER BY nom ASC");
    $categories = $stmtCat->fetchAll();
 
    $id_cat_filter = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
 
    $sql = "
        SELECT 
            s.*,
            p.prenom, p.nom, p.id_prestataire, p.note_moyenne, p.nombre_evaluations,
            COUNT(d.id_disponibilite) AS nb_dispos_semaine,
            MIN(d.date_debut)         AS prochaine_dispo
        FROM services s
        JOIN prestataire p ON s.id_prestataire = p.id_prestataire
        LEFT JOIN disponibilites d 
            ON d.id_prestataire = p.id_prestataire
            AND d.type          = 'libre'
            AND d.date_debut    BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
        WHERE p.statut = 'valide'
    ";
 
    $params = [];
    if ($id_cat_filter > 0) {
        $sql .= " AND p.id_categorie = ?";
        $params[] = $id_cat_filter;
    }
 
    $sql .= " GROUP BY s.id_service, p.id_prestataire ORDER BY nb_dispos_semaine DESC, p.note_moyenne DESC";
 
    $stmtPres = $pdo->prepare($sql);
    $stmtPres->execute($params);
    $offres = $stmtPres->fetchAll();
 
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Services — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { 'sable': '#F4EDDE', 'corail': '#FF885B', 'peche': '#FFD9CA' },
                fontFamily: { sans: ['DM Sans', 'sans-serif'], title: ['Quicksand', 'sans-serif'] },
                borderRadius: { senior: '30px' }
            }}
        }
    </script>
</head>
<body class="bg-sable font-sans text-slate-800">
 
<nav class="fixed w-full bg-white/90 backdrop-blur-md shadow-sm z-50 px-8 py-4 flex justify-between items-center">
    <a href="index.php" class="text-2xl font-bold text-corail font-title">Silver Happy</a>
    <div class="hidden md:flex gap-12 font-bold text-sm uppercase tracking-widest text-slate-400">
        <a href="index.php"    class="hover:text-corail transition-colors">Accueil</a>
        <a href="boutique.php" class="hover:text-corail transition-colors">Boutique</a>
    </div>
    <?php if (isset($_SESSION['id'])): ?>
        <a href="dashboardS.php" class="bg-corail text-white px-6 py-2 rounded-full font-bold text-xs shadow-md">Mon Espace</a>
    <?php else: ?>
        <div></div>
    <?php endif; ?>
</nav>
 
<main class="pt-32 pb-20 px-6 max-w-7xl mx-auto">
 
    <div class="text-center mb-12">
        <h1 class="text-4xl font-title font-bold text-slate-900 mb-4">Découvrez nos services</h1>
        <p class="text-slate-500">Des professionnels qualifiés pour vous simplifier la vie.</p>
    </div>

    <?php if (!empty($categories)): ?>
    <div class="flex flex-wrap gap-3 justify-center mb-10">
        <a href="liste_prestataires.php"
           class="px-5 py-2 rounded-full font-bold text-sm transition-all <?php echo $id_cat_filter === 0 ? 'bg-corail text-white shadow-md' : 'bg-white text-slate-500 hover:bg-peche'; ?>">
            Tous
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="?cat=<?php echo $cat['id_categorie']; ?>"
           class="px-5 py-2 rounded-full font-bold text-sm transition-all <?php echo $id_cat_filter === (int)$cat['id_categorie'] ? 'bg-corail text-white shadow-md' : 'bg-white text-slate-500 hover:bg-peche'; ?>">
            <?php echo htmlspecialchars($cat['nom']); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($offres)): ?>
            <div class="col-span-full text-center py-20 bg-white rounded-senior border-2 border-dashed border-slate-200">
                <p class="text-slate-400 italic font-medium">Aucune offre disponible pour le moment.</p>
            </div>
        <?php else: foreach ($offres as $o):
            $nbDispos       = (int)$o['nb_dispos_semaine'];
            $prochaineDate  = $o['prochaine_dispo'] ? date('d/m à H\hi', strtotime($o['prochaine_dispo'])) : null;
            $note           = (float)$o['note_moyenne'];
            $nbEvals        = (int)$o['nombre_evaluations'];
        ?>
            <div class="bg-white rounded-senior shadow-sm hover:shadow-xl transition-all border border-slate-50 overflow-hidden flex flex-col p-8 relative">
 
             
                <?php if ($nbDispos === 0): ?>
                    <span class="absolute top-4 right-4 bg-slate-100 text-slate-400 text-[10px] font-bold px-3 py-1 rounded-full">
                        Indisponible cette semaine
                    </span>
                <?php elseif ($nbDispos <= 2): ?>
                    <span class="absolute top-4 right-4 bg-orange-50 text-orange-500 text-[10px] font-bold px-3 py-1 rounded-full">
                        <i class="fa-solid fa-fire mr-1"></i><?php echo $nbDispos; ?> créneau<?php echo $nbDispos > 1 ? 'x' : ''; ?> restant<?php echo $nbDispos > 1 ? 's' : ''; ?>
                    </span>
                <?php else: ?>
                    <span class="absolute top-4 right-4 bg-green-50 text-green-600 text-[10px] font-bold px-3 py-1 rounded-full">
                        <i class="fa-solid fa-circle-check mr-1"></i><?php echo $nbDispos; ?> créneaux dispo
                    </span>
                <?php endif; ?>
 
                <div class="flex justify-between items-start mb-4 mt-4">
                    <span class="bg-peche text-corail text-[10px] font-bold px-3 py-1 rounded-full uppercase">
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
 
   
                <div class="flex items-center gap-2 text-slate-400 text-[10px] mb-4 font-bold uppercase">
                    <i class="fa-solid fa-location-dot text-corail"></i>
                    <?php echo htmlspecialchars($o['ville']); ?>
                </div>
 
                <p class="text-slate-500 text-sm mb-6 italic flex-1">
                    "<?php echo htmlspecialchars(mb_strimwidth($o['description'], 0, 100, '...')); ?>"
                </p>

                <?php if ($prochaineDate): ?>
                <p class="text-xs text-emerald-600 font-bold mb-4">
                    <i class="fa-regular fa-calendar-check mr-1"></i>
                    Prochaine dispo : <?php echo $prochaineDate; ?>
                </p>

                <?php if ($nbDispos > 0): ?>
                    <a href="reserver.php?id_service=<?php echo $o['id_service']; ?>"
                       class="block w-full text-center bg-corail text-white py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg shadow-corail/20">
                        Réserver ce service
                    </a>
                <?php else: ?>
                    <button disabled
                            class="block w-full text-center bg-slate-100 text-slate-400 py-4 rounded-2xl font-bold cursor-not-allowed">
                        Aucune disponibilité
                    </button>
                <?php endif; ?>
 
            </div>
        <?php endforeach; endif; ?>
    </div>
</main>
 
</body>
</html>
 
