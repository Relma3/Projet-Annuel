<?php
session_start();
require_once 'db_connect.php';

$cat_slug = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : 'domicile';

try {
    $stmt = $pdo->prepare("SELECT * FROM categories_prestations WHERE nom LIKE ?");
    $stmt->execute(['%' . $cat_slug . '%']);
    $db_cat = $stmt->fetch();
} catch (PDOException $e) {
    $db_cat = false;
}

$infos = [
    'domicile' => [
        'titre'              => 'Services à domicile',
        'tagline'            => 'Retrouvez le plaisir d\'un foyer impeccable sans effort.',
        'image'              => 'service.png',
        'description_defaut' => 'Parce que votre domicile est votre havre de paix, Silver Happy a sélectionné des experts du soin de l\'intérieur. Nos intervenants veillent à votre confort quotidien en respectant vos habitudes.',
        'points_forts'       => ['Produits écoresponsables', 'Ponctualité garantie', 'Personnel formé aux seniors']
    ],
    'sante' => [
        'titre'              => 'Santé & Bien-être',
        'tagline'            => 'Une approche douce et préventive pour votre vitalité.',
        'image'              => 'service.png',
        'description_defaut' => 'La santé après 60 ans demande une attention particulière. Nous vous mettons en relation avec des professionnels de la médecine douce pour maintenir votre autonomie.',
        'points_forts'       => ['Téléconsultation incluse', 'Suivi personnalisé', 'Experts certifiés']
    ],
    'loisirs' => [
        'titre'              => 'Loisirs & Culture',
        'tagline'            => 'Évènements, sorties et rencontres pour rester actif.',
        'image'              => 'service.png',
        'description_defaut' => 'Silver Happy organise des événements culturels, des soirées de rencontres, des visites et des conférences dédiés aux seniors. Restez connecté et épanoui.',
        'points_forts'       => ['Événements hebdomadaires', 'Sorties culturelles', 'Rencontres entre seniors']
    ],
    'maison' => [
        'titre'              => 'Maison & Habitat',
        'tagline'            => 'Petit bricolage, jardinage et amélioration de l\'habitat.',
        'image'              => 'service.png',
        'description_defaut' => 'Nos artisans qualifiés interviennent chez vous pour tous vos travaux du quotidien : petite plomberie, jardinage, décoration ou amélioration de votre habitat.',
        'points_forts'       => ['Artisans certifiés', 'Devis gratuit', 'Intervention rapide']
    ],
    'formations' => [
        'titre'              => 'Formations',
        'tagline'            => 'Cours d\'informatique, de langues et ateliers thématiques.',
        'image'              => 'service.png',
        'description_defaut' => 'Apprenez à votre rythme avec nos formateurs spécialisés pour les seniors. Informatique, langues étrangères, arts ou cuisine — il n\'est jamais trop tard pour apprendre.',
        'points_forts'       => ['Cours adaptés aux seniors', 'Petits groupes', 'Formateurs patients']
    ],
    'boutique' => [
        'titre'              => 'Boutique adaptée',
        'tagline'            => 'Articles de confort et équipements spécialisés.',
        'image'              => 'service.png',
        'description_defaut' => 'Retrouvez dans notre boutique une sélection d\'articles de confort, d\'équipements de maintien à domicile et de produits adaptés à votre quotidien.',
        'points_forts'       => ['Produits sélectionnés', 'Livraison à domicile', 'Garantie satisfaction']
    ],
];

$content = $infos[$cat_slug] ?? $infos['domicile'];
$description_finale = ($db_cat && !empty($db_cat['description'])) ? $db_cat['description'] : $content['description_defaut'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $content['titre']; ?> - Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B', 'peche-pastel': '#FFD9CA' },
                    fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
                    borderRadius: { 'senior': '40px' }
                }
            }
        }
    </script>
</head>
<body class="bg-sable-doux font-sans text-slate-800">
    <?php include 'accessibilite.php'; ?>

    <nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-8 py-4 flex justify-between items-center">
        <a href="services.php" class="font-bold text-slate-600 hover:text-orange-corail transition-colors">
            <i class="fa-solid fa-chevron-left mr-2"></i> Nos Services
        </a>
        <img src="logo.png" alt="Logo" class="h-10">
    </nav>

    <main class="pt-24">
        <section class="relative h-[500px] flex items-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="<?php echo $content['image']; ?>" alt="Service" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
            </div>
            
            <div class="relative z-10 max-w-7xl mx-auto px-6 w-full text-white">
                <span class="bg-orange-corail px-4 py-1 rounded-full text-sm font-bold uppercase tracking-widest mb-4 inline-block shadow-lg text-white">Pour vous</span>
                <h1 class="text-5xl md:text-7xl font-bold font-title mb-6"><?php echo $content['titre']; ?></h1>
                <p class="text-xl md:text-2xl max-w-2xl opacity-90 leading-relaxed"><?php echo $content['tagline']; ?></p>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 py-20 grid grid-cols-1 lg:grid-cols-3 gap-16">
            
            <div class="lg:col-span-2 space-y-10">
                <div>
                    <h2 class="text-3xl font-bold mb-6 flex items-center gap-4 text-slate-900">
                        <span class="w-12 h-1 bg-orange-corail rounded-full"></span>
                        En savoir plus sur ce service
                    </h2>
                    <p class="text-xl text-slate-600 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($description_finale)); ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($content['points_forts'] as $point): ?>
                    <div class="flex items-center gap-4 p-6 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i class="fa-solid fa-circle-check text-orange-corail text-2xl"></i>
                        <span class="font-bold text-slate-700"><?php echo $point; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="bg-white p-10 rounded-senior shadow-2xl sticky top-32 border border-slate-100 text-center">
                    <h3 class="text-2xl font-bold mb-6 text-slate-900 font-title">Prêt à commencer ?</h3>
                    <p class="text-slate-500 mb-8 leading-relaxed italic">"Plus de 200 prestataires certifiés sont prêts à vous aider."</p>
                    
                    <?php if (isset($_SESSION['id']) && $_SESSION['type'] === 'senior'): ?>
                        <a href="liste_prestataires.php?cat=<?php echo $cat_slug; ?>" 
                           class="block w-full bg-orange-corail text-white py-5 rounded-full font-bold text-xl shadow-lg hover:brightness-110 transition-all mb-4">
                           Trouver un prestataire
                        </a>
                    <?php else: ?>
                        <a href="connexion.php" class="block w-full bg-slate-900 text-white py-5 rounded-full font-bold text-xl hover:bg-slate-800 transition-all mb-4">
                            Se connecter
                        </a>
                    <?php endif; ?>
                    
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-shield-halved mr-1"></i> Garantie satisfaction Silver Happy
                    </p>
                </div>
            </aside>
        </section>
    </main>

</body>
</html>
