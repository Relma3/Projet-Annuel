<?php
session_start();
require_once 'db_connect.php';

$id_pres = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pres <= 0) {
    header('Location: liste_prestataires.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM prestataire WHERE id_prestataire = ? AND statut = 'valide'");
    $stmt->execute([$id_pres]);
    $p = $stmt->fetch();

    if (!$p) {
        die("Prestataire introuvable.");
    }

    $nom_categorie = !empty($p['categorie']) ? $p['categorie'] : "Intervenant Silver Happy";
    }

} catch (PDOException $e) {
    die("Erreur technique : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($p['prenom']); ?> — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sable': '#F4EDDE', 'corail': '#FF885B', 'emerald-pro': '#065F46', 'peche-pale': '#FDF0EB' },
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
                <img src="logo.png" alt="Logo" class="h-10">
                <span class="text-2xl font-bold text-corail font-title">Silver Happy</span>
            </a>
        </div>
        <div class="hidden md:flex flex-1 justify-center gap-12 font-bold text-sm uppercase tracking-widest text-slate-400">
            <a href="index.php" class="hover:text-corail transition-colors">Accueil</a>
            <a href="boutique.php" class="hover:text-corail transition-colors">Boutique</a>
            <a href="contact.php" class="hover:text-corail transition-colors">Contact</a>
        </div>
        <div class="flex-1 flex justify-end">
            <?php if(isset($_SESSION['id'])): ?>
                <a href="<?php echo ($_SESSION['type'] === 'senior') ? 'dashboardS.php' : 'dashboardP.php'; ?>" class="bg-corail text-white px-6 py-2 rounded-full font-bold text-xs uppercase tracking-widest shadow-md">Mon Espace</a>
            <?php else: ?>
                <a href="connexion.php" class="text-corail font-bold text-sm hover:underline">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-5xl mx-auto">
        <div class="bg-white rounded-senior shadow-sm border border-slate-50 overflow-hidden mb-10">
            <div class="h-40 bg-gradient-to-r from-corail to-peche-pale"></div>
            <div class="px-10 pb-10 relative">
                <div class="w-32 h-32 bg-white rounded-3xl shadow-xl -mt-16 mb-6 p-2">
                    <div class="w-full h-full rounded-2xl bg-slate-100 flex items-center justify-center text-4xl font-bold text-corail uppercase">
                        <?php echo strtoupper(substr($p['prenom'], 0, 1)); ?>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h1 class="text-3xl font-title font-bold text-slate-900"><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></h1>
                        <p class="text-corail font-bold uppercase tracking-widest text-sm mt-1">
                            <?php echo htmlspecialchars($nom_categorie); ?>
                        </p>
                        <div class="flex items-center gap-6 mt-4 text-slate-400 text-sm font-medium">
                            <span><i class="fa-solid fa-location-dot text-corail mr-2"></i><?php echo htmlspecialchars($p['ville']); ?></span>
                            <span><i class="fa-solid fa-star text-yellow-400 mr-2"></i>4.9/5</span>
                        </div>
                    </div>
                    <?php if(isset($_SESSION['type']) && $_SESSION['type'] === 'senior'): ?>
                        <a href="#reserver" class="bg-emerald-pro text-white px-10 py-4 rounded-2xl font-bold shadow-lg hover:scale-105 transition-all text-center">
                            Réserver maintenant
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-8 rounded-senior shadow-sm border border-slate-50">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-quote-left text-corail"></i> À propos
                    </h2>
                    <p class="text-slate-600 leading-relaxed italic">
                        "<?php echo nl2br(htmlspecialchars($p['description'])); ?>"
                    </p>
                </div>

                <div id="reserver" class="bg-white p-8 rounded-senior shadow-sm border border-slate-50">
                    <h2 class="text-xl font-bold mb-6">Tarifs et réservation</h2>
                    <div class="p-8 bg-peche-pale/30 rounded-3xl border border-corail/10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div class="text-center md:text-left">
                            <h3 class="font-bold text-slate-800 text-lg">Service d'accompagnement</h3>
                            <p class="text-xs text-slate-500 font-bold uppercase mt-1 tracking-wider">
                                <i class="fa-solid fa-clock mr-1 text-corail"></i> Tarif horaire fixe
                            </p>
                        </div>
                        <div class="text-center md:text-right">
                            <p class="text-4xl font-bold text-corail"><?php echo number_format($p['tarif_horaire'], 2); ?> €<span class="text-sm font-normal text-slate-400">/h</span></p>
                            <?php if(isset($_SESSION['type']) && $_SESSION['type'] === 'senior'): ?>
                                <a href="reserver.php?id_pres=<?php echo $id_pres; ?>" class="inline-block mt-4 bg-emerald-pro text-white text-xs uppercase font-bold px-8 py-3 rounded-xl hover:bg-slate-900 transition-colors shadow-md">
                                    Choisir ce créneau
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-emerald-pro p-8 rounded-senior text-white shadow-xl">
                    <h3 class="font-bold text-lg mb-4">Garantie Silver Happy</h3>
                    <ul class="space-y-4 text-sm opacity-90 font-medium">
                        <li class="flex gap-3 items-center"><i class="fa-solid fa-circle-check text-white"></i> Identité vérifiée</li>
                        <li class="flex gap-3 items-center"><i class="fa-solid fa-circle-check text-white"></i> Références contrôlées</li>
                        <li class="flex gap-3 items-center"><i class="fa-solid fa-circle-check text-white"></i> Assurance incluse</li>
                    </ul>
                </div>
                <div class="bg-white p-8 rounded-senior shadow-sm border border-slate-50">
                    <h3 class="font-bold text-slate-900 mb-4">Disponibilités</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Ce prestataire intervient généralement en semaine de 08:00 à 18:00. Les horaires précis seront à confirmer lors de la réservation.</p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
