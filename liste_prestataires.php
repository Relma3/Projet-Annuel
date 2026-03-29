<?php
session_start();
require_once 'db_connect.php';

try {
    $stmtCat = $pdo->query("SELECT * FROM categories_prestations ORDER BY nom ASC");
    $categories = $stmtCat->fetchAll();

    $id_cat_filter = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

    $sql = "SELECT s.*, p.prenom, p.nom, p.id_prestataire 
            FROM services s
            JOIN prestataire p ON s.id_prestataire = p.id_prestataire 
            WHERE 1=1"; 
    $params = [];
    if ($id_cat_filter > 0) {
        $sql .= " AND p.id_categorie = ?"; 
        $params[] = $id_cat_filter;
    }

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
            theme: {
                extend: {
                    colors: { 'sable': '#F4EDDE', 'corail': '#FF885B', 'emerald-pro': '#065F46', 'peche-pastel': '#FFD9CA' },
                    fontFamily: { 'sans': ['DM Sans', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
                    borderRadius: { 'senior': '30px' }
                }
            }
        }
    </script>
</head>
<body class="bg-sable font-sans text-slate-800">

    <nav class="fixed w-full bg-white/90 backdrop-blur-md shadow-sm z-50 px-8 py-4 flex justify-between items-center">
        <div class="flex-1">
            <a href="index.php" class="flex items-center gap-3 w-max">
                <span class="text-2xl font-bold text-corail font-title">Silver Happy</span>
            </a>
        </div>
        <div class="hidden md:flex flex-1 justify-center gap-12 font-bold text-sm uppercase tracking-widest text-slate-400">
            <a href="index.php" class="hover:text-corail transition-colors">Accueil</a>
            <a href="boutique.php" class="hover:text-corail transition-colors">Boutique</a>
        </div>
        <div class="flex-1 flex justify-end">
            <?php if(isset($_SESSION['id'])): ?>
                <a href="dashboardS.php" class="bg-corail text-white px-6 py-2 rounded-full font-bold text-xs shadow-md">Mon Espace</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-title font-bold text-slate-900 mb-4">Découvrez nos services</h1>
            <p class="text-slate-500">Des professionnels qualifiés pour vous simplifier la vie.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(empty($offres)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-senior border-2 border-dashed border-slate-200">
                    <p class="text-slate-400 italic font-medium">Aucune offre de service n'est disponible pour le moment.</p>
                </div>
            <?php else: foreach($offres as $o): ?>
                <div class="bg-white rounded-senior shadow-sm hover:shadow-xl transition-all border border-slate-50 overflow-hidden group p-8">
                    
                    <div class="flex justify-between items-start mb-4">
                        <span class="bg-peche-pastel text-corail text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                            <?php echo htmlspecialchars($o['nom_service']); ?>
                        </span>
                        <span class="text-2xl font-bold text-slate-900"><?php echo $o['prix']; ?>€<small class="text-xs text-slate-400">/h</small></span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($o['prenom'] . ' ' . $o['nom']); ?></h2>
                    
                    <div class="flex items-center gap-2 text-slate-400 text-[10px] mb-4 font-bold uppercase">
                        <i class="fa-solid fa-location-dot text-corail"></i>
                        <?php echo htmlspecialchars($o['ville']); ?>
                    </div>

                    <p class="text-slate-500 text-sm mb-8 italic">
                        "<?php echo htmlspecialchars($o['description']); ?>"
                    </p>

                    <a href="reserver.php?id_service=<?php echo $o['id_service']; ?>" 
                       class="block w-full text-center bg-corail text-white py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg shadow-corail/20">
                        Réserver ce service
                    </a>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>
</body>
</html>
