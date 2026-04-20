<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

require_once 'db_connect.php';

$db = getDB();
$stmt = $db->query("SELECT * FROM article ORDER BY id_article DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$exemples = [
    ['nom' => 'Telephone Senior Simplifie', 'prix' => 89.99, 'description' => 'Telephone avec grosses touches et affichage clair'],
    ['nom' => 'Montre Connectee Sante', 'prix' => 129.99, 'description' => 'Suivi du rythme cardiaque et detection de chutes'],
    ['nom' => 'Coussin Lombaire Confort', 'prix' => 34.99, 'description' => 'Soutien dorsal pour plus de confort'],
    ['nom' => 'Kit Jardin Facile', 'prix' => 24.99, 'description' => 'Outils de jardinage adaptes aux seniors'],
    ['nom' => 'Lampe Loupe LED', 'prix' => 29.99, 'description' => 'Loupe avec lumiere integree'],
    ['nom' => 'Boite a Medicaments', 'prix' => 19.99, 'description' => 'Pilulier simple pour la semaine']
];

$liste = $articles;
if (empty($articles)) {
    $liste = $exemples;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boutique - Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen" style="font-size:18px;">

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <span class="text-orange-500 font-bold text-2xl">Silver Happy</span>
    <div class="flex gap-4">
        <a href="dashboardS.php" class="text-gray-600">Tableau de bord</a>
        <a href="planning.php" class="text-gray-600">Mon planning</a>
        <a href="boutique.php" class="text-gray-600">Boutique</a>
        <a href="messagerie.php" class="text-gray-600">Messagerie</a>
        <a href="conseils.php" class="text-gray-600">Conseils</a>
        <a href="logout.php" class="text-red-400">Déconnexion</a>
    </div>
</nav>

<div class="max-w-6xl mx-auto mt-10 px-4">
    <h1 class="text-3xl font-bold text-orange-500 mb-2">Boutique Silver Happy</h1>
    <p class="text-gray-500 mb-8">Articles et produits adaptes aux seniors</p>

    <?php if (isset($_GET['commande'])) { ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 text-lg">
            Votre commande a bien ete enregistree.
        </div>
    <?php } ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($liste as $a) { ?>
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">
                    <?php echo htmlspecialchars($a['nom'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>

                <p class="text-gray-500 mb-4 text-base">
                    <?php echo htmlspecialchars($a['description'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-orange-500">
                        <?php echo number_format($a['prix'], 2); ?>€
                    </span>

                    <form method="POST" action="traitement_commande.php">
                        <?php if (isset($a['id_article'])) { ?>
                            <input type="hidden" name="id_article" value="<?php echo htmlspecialchars($a['id_article'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php } else { ?>
                            <input type="hidden" name="nom_article" value="<?php echo htmlspecialchars($a['nom'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="prix" value="<?php echo $a['prix']; ?>">
                        <?php } ?>

                        <button type="submit" class="bg-orange-500 text-white px-6 py-3 rounded-xl font-bold text-base">
                            Commander
                        </button>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>