<?php
session_start();
require_once 'db_connect.php';
 
$cat_slug = isset($_GET['cat']) ? preg_replace('/[^a-z]/', '', strtolower($_GET['cat'])) : 'domicile';

$infos = [
    'domicile' => [
        'titre' => 'Services à domicile',
        'tagline' => 'Retrouvez le plaisir d\'un foyer impeccable sans effort.',
        'image' => 'service.png',
        'description_defaut' => 'Parce que votre domicile est votre havre de paix, Silver Happy a sélectionné des experts du soin de l\'intérieur. Nos intervenants veillent à votre confort quotidien en respectant vos habitudes.',
        'points_forts' => ['Produits écoresponsables', 'Ponctualité garantie', 'Personnel formé aux seniors'],
        'couleur' => 'orange-corail'
    ],
    'loisirs' => [
        'titre' => 'Loisirs & Culture',
        'tagline' => 'Des moments de partage pour rester connecté et s\'épanouir.',
        'image' => 'service.png',
        'description_defaut' => 'Événements, soirées de rencontres, visites culturelles, jeux et conférences : Silver Happy organise des activités variées pour nourrir votre curiosité et entretenir vos liens sociaux.',
        'points_forts' => ['Sorties en petits groupes', 'Animateurs formés', 'Transport possible sur demande'],
        'couleur' => 'emerald-700'
    ],
    'sante' => [
        'titre' => 'Santé & Bien-être',
        'tagline' => 'Une approche douce et préventive pour votre vitalité.',
        'image' => 'service.png',
        'description_defaut' => 'La santé après 60 ans demande une attention particulière. Nous vous mettons en relation avec des professionnels de la médecine douce et proposons des télérendez-vous avec des médecins validés.',
        'points_forts' => ['Téléconsultation incluse', 'Suivi personnalisé', 'Experts certifiés'],
        'couleur' => 'orange-corail'
    ],
    'habitat' => [
        'titre' => 'Maison & Habitat',
        'tagline' => 'Un logement sûr, adapté, et agréable à vivre.',
        'image' => 'service.png',
        'description_defaut' => 'De l\'entretien du jardin aux petits travaux, en passant par l\'adaptation de votre logement pour prévenir les chutes : nos artisans interviennent en toute confiance pour sécuriser et embellir votre cadre de vie.',
        'points_forts' => ['Artisans vérifiés', 'Devis gratuit', 'Adaptation anti-chute'],
        'couleur' => 'emerald-700'
    ],
    'formation' => [
        'titre' => 'Formations',
        'tagline' => 'Apprendre n\'a pas d\'âge : découvrez, progressez, partagez.',
        'image' => 'service.png',
        'description_defaut' => 'Cours d\'informatique pour apprivoiser le numérique, ateliers de langues étrangères, initiations à la photographie, à la cuisine, à la musique... Nos formateurs vous accompagnent à votre rythme.',
        'points_forts' => ['Cours en petits groupes ou individuels', 'Débutants bienvenus', 'En salle ou à domicile'],
        'couleur' => 'orange-corail'
    ]
];
 
try {
    $stmt = $pdo->prepare("SELECT * FROM categories_prestations WHERE nom LIKE ? OR slug = ?");
    $stmt->execute(['%' . $cat_slug . '%', $cat_slug]);
    $db_cat = $stmt->fetch();
} catch (PDOException $e) {
    $db_cat = false;
}
 
$content = $infos[$cat_slug] ?? $infos['domicile'];
$description_finale = ($db_cat && !empty($db_cat['description'])) ? $db_cat['description'] : $content['description_defaut'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['titre']); ?> - Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="accessibilite.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B', 'vert-menthe': '#A0E8AF', 'peche-pastel': '#FFD9CA' },
                    fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
                    borderRadius: { 'senior': '40px' }
                }
            }
        }
    </script>
</head>
<body class="bg-sable-doux font-sans text-slate-800">
    <nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-8 py-4 flex justify-between items-center">
        <a href="services.php" class="font-bold text-slate-600 hover:text-orange-corail transition-colors">
            <i class="fa-solid fa-chevron-left mr-2"></i> <span data-i18n="back_services">Nos Services</span>
        </a>
        <img src="logo.png" alt="Logo" class="h-10">
    </nav>
 
    <main class="pt-24">
        <section class="relative h-[500px] flex items-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="<?php echo htmlspecialchars($content['image']); ?>" alt="Service" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
            </div>
            <div class="relative z-10 max-w-7xl mx-auto px-6 w-full text-white">
                <span class="bg-orange-corail px-4 py-1 rounded-full text-sm font-bold uppercase tracking-widest mb-4 inline-block shadow-lg text-white" data-i18n="for_you">Pour vous</span>
                <h1 class="text-5xl md:text-7xl font-bold font-title mb-6"><?php echo htmlspecialchars($content['titre']); ?></h1>
                <p class="text-xl md:text-2xl max-w-2xl opacity-90 leading-relaxed"><?php echo htmlspecialchars($content['tagline']); ?></p>
            </div>
        </section>
 
        <section class="max-w-7xl mx-auto px-6 py-20 grid grid-cols-1 lg:grid-cols-3 gap-16">
            <div class="lg:col-span-2 space-y-10">
                <div>
                    <h2 class="text-3xl font-bold mb-6 flex items-center gap-4 text-slate-900">
                        <span class="w-12 h-1 bg-orange-corail rounded-full"></span>
                        <span data-i18n="know_more">En savoir plus sur ce service</span>
                    </h2>
                    <p class="text-xl text-slate-600 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($description_finale)); ?>
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($content['points_forts'] as $point): ?>
                    <div class="flex items-center gap-4 p-6 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i class="fa-solid fa-circle-check text-orange-corail text-2xl"></i>
                        <span class="font-bold text-slate-700"><?php echo htmlspecialchars($point); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
 
            <aside class="lg:col-span-1">
                <div class="bg-white p-10 rounded-senior shadow-2xl sticky top-32 border border-slate-100 text-center">
                    <h3 class="text-2xl font-bold mb-6 text-slate-900 font-title" data-i18n="ready_start">Prêt à commencer ?</h3>
                    <p class="text-slate-500 mb-8 leading-relaxed italic">"Plus de 200 prestataires certifiés sont prêts à vous aider."</p>
 
                    <?php if (isset($_SESSION['id']) && $_SESSION['type'] === 'senior'): ?>
                        <a href="liste_prestataires.php?cat=<?php echo urlencode($cat_slug); ?>"
                           class="block w-full bg-orange-corail text-white py-5 rounded-full font-bold text-xl shadow-lg hover:brightness-110 transition-all mb-4"
                           data-i18n="find_provider">
                           Trouver un prestataire
                        </a>
                    <?php else: ?>
                        <a href="connexion.php" class="block w-full bg-slate-900 text-white py-5 rounded-full font-bold text-xl hover:bg-slate-800 transition-all mb-4" data-i18n="login_short">
                            Se connecter
                        </a>
                    <?php endif; ?>
 
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-shield-halved mr-1"></i> <span data-i18n="guarantee">Garantie satisfaction Silver Happy</span>
                    </p>
                </div>
            </aside>
        </section>
    </main>
 
    <script src="i18n.js"></script>
    <script src="accessibilite.js"></script>
</body>
</html>
 
