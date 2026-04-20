<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

require_once 'db_connect.php';

$db = getDB();
$req = $db->query("SELECT * FROM conseils ORDER BY created_at DESC");
$conseils = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Conseils - Silver Happy</title>
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

<div class="max-w-4xl mx-auto mt-10 px-4">
    <h1 class="text-3xl font-bold text-orange-500 mb-2">Espace Conseils</h1>
    <p class="text-gray-500 mb-8">Des conseils pour bien vivre apres 60 ans</p>

    <?php if (empty($conseils)) { ?>
        <p class="text-gray-400 text-center py-10">Aucun conseil disponible pour le moment.</p>
    <?php } else { ?>
        <div class="grid gap-6">
            <?php foreach ($conseils as $c) { ?>
                <div class="bg-white rounded-2xl shadow p-6">
                    <p class="text-sm text-orange-600 mb-2"><?php echo $c['categorie']; ?></p>
                    <h2 class="text-xl font-bold text-gray-800 mb-2"><?php echo $c['titre']; ?></h2>
                    <p class="text-gray-600"><?php echo $c['contenu']; ?></p>
                    <p class="text-gray-400 text-sm mt-3">
                        Publie le <?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

</body>
</html>