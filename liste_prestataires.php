<?php
session_start();
require_once 'db_connect.php';

$categories = [];
$prestataires = [];
$id_cat_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $stmtCat = $pdo->query("SELECT * FROM categories_prestations ORDER BY nom");
    $categories = $stmtCat->fetchAll();

    $sql = "SELECT id_prestataire, nom, prenom, ville, categorie, description, tarif_horaire
            FROM prestataire
            WHERE statut = 'valide'";
    $params = [];

    if ($id_cat_filter > 0) {
        $stmtNomCat = $pdo->prepare("SELECT nom FROM categories_prestations WHERE id = ?");
        $stmtNomCat->execute([$id_cat_filter]);
        $cat = $stmtNomCat->fetch();

        if ($cat) {
            $sql .= " AND categorie = ?";
            $params[] = $cat['nom'];
        }
    }

    if ($search != '') {
        $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR categorie LIKE ? OR description LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY nom";

    $stmtPres = $pdo->prepare($sql);
    $stmtPres->execute($params);
    $prestataires = $stmtPres->fetchAll();

} catch (PDOException $e) {
    $categories = [];
    $prestataires = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Prestataires</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-orange-50 text-gray-800">

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <a href="index.php" class="text-2xl font-bold text-orange-500">Silver Happy</a>

    <div>
        <?php if (isset($_SESSION['id'])) { ?>
            <a href="dashboardS.php" class="bg-orange-500 text-white px-4 py-2 rounded-xl">Mon Espace</a>
        <?php } else { ?>
            <a href="connexion.php" class="bg-orange-500 text-white px-4 py-2 rounded-xl">Se connecter</a>
        <?php } ?>
    </div>
</nav>

<main class="max-w-6xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold text-center text-orange-500 mb-2">Nos Prestataires</h1>
    <p class="text-center text-gray-500 mb-8">Des professionnels validés par Silver Happy</p>

    <form method="GET" class="flex flex-wrap gap-3 justify-center mb-8">
        <input
            type="text"
            name="q"
            value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Rechercher..."
            class="border rounded-xl px-4 py-2"
        >

        <select name="cat" class="border rounded-xl px-4 py-2">
            <option value="0">Toutes les catégories</option>
            <?php foreach ($categories as $c) { ?>
                <option value="<?php echo $c['id']; ?>" <?php if ($id_cat_filter == $c['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($c['nom']); ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" class="bg-orange-500 text-white px-5 py-2 rounded-xl">
            Filtrer
        </button>

        <?php if ($id_cat_filter || $search != '') { ?>
            <a href="liste_prestataires.php" class="px-5 py-2 underline text-gray-600">
                Réinitialiser
            </a>
        <?php } ?>
    </form>

    <?php if (empty($prestataires)) { ?>
        <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-400">
            Aucun prestataire disponible.
        </div>
    <?php } else { ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($prestataires as $p) { ?>
                <div class="bg-white rounded-2xl shadow p-6 flex flex-col">
                    <p class="text-sm text-orange-500 font-bold mb-2">
                        <?php echo htmlspecialchars($p['categorie']); ?>
                    </p>

                    <h2 class="text-xl font-bold mb-1">
                        <?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?>
                    </h2>

                    <p class="text-gray-500 mb-2">
                        <?php echo htmlspecialchars($p['ville']); ?>
                    </p>

                    <p class="text-gray-600 mb-4 flex-1">
                        <?php echo htmlspecialchars($p['description']); ?>
                    </p>

                    <?php if (!empty($p['tarif_horaire'])) { ?>
                        <p class="text-lg font-bold text-orange-500 mb-4">
                            <?php echo number_format((float)$p['tarif_horaire'], 0); ?> €/h
                        </p>
                    <?php } ?>

                    <?php if (isset($_SESSION['id']) && $_SESSION['type'] == 'senior') { ?>
                        <a href="reservation.php?id=<?php echo $p['id_prestataire']; ?>"
                           class="block text-center bg-orange-500 text-white py-3 rounded-xl font-bold">
                            Prendre rendez-vous
                        </a>
                    <?php } else { ?>
                        <a href="connexion.php"
                           class="block text-center bg-gray-200 text-gray-700 py-3 rounded-xl font-bold">
                            Connectez-vous pour réserver
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</main>

</body>
</html>