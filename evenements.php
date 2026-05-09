<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_senior = $_SESSION['id'];

$stmt = $pdo->prepare("
    SELECT e.*,
           (e.nombre_places - COUNT(ie.id_inscription)) AS places_restantes,
           MAX(CASE WHEN ie.id_senior = ? THEN 1 ELSE 0 END) AS deja_inscrit
    FROM evenements e
    LEFT JOIN inscription_evenement ie ON ie.id_evenement = e.id
    WHERE e.date_debut >= NOW()
    GROUP BY e.id
    ORDER BY e.date_debut ASC
");
$stmt->execute([$id_senior]);
$evenements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">

<a href="dashboardS.php" class="text-orange-500 font-bold mb-6 inline-block">← Retour au tableau de bord</a>
<h1 class="text-2xl font-bold mb-6"><span data-i18n="events_title">Événements à venir</span></h1>

<?php if (isset($_GET['msg'])): ?>
    <p class="mb-4 p-3 rounded bg-green-100 text-green-700"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<?php if (empty($evenements)): ?>
    <p class="text-gray-400">Aucun événement programmé.</p>
<?php else: ?>
    <?php foreach ($evenements as $e): ?>
    <div class="bg-white rounded-xl shadow p-5 mb-4 flex justify-between items-center">
        <div>
            <p class="font-bold text-lg"><?= htmlspecialchars($e['titre']) ?></p>
            <p class="text-gray-500 text-sm">
                <?= date('d/m/Y à H:i', strtotime($e['date_debut'])) ?>
                <?= $e['lieu'] ? '· ' . htmlspecialchars($e['lieu']) : '' ?>
            </p>
            <p class="text-sm mt-1 <?= $e['places_restantes'] <= 0 ? 'text-red-500' : 'text-green-600' ?>">
                <?= $e['deja_inscrit'] ? '<span data-i18n="events_registered">✓ Inscrit(e)</span>' : ($e['places_restantes'] <= 0 ? 'Complet' : $e['places_restantes'] . ' place(s) disponible(s)') ?>
            </p>
        </div>
        <div>
            <?php if ($e['deja_inscrit']): ?>
                <a href="api/evenements.php?action=annuler&id=<?= $e['id'] ?>" 
                   onclick="return confirm('Se désinscrire ?')"
                   class="border border-red-400 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50">
                    Se désinscrire
                </a>
            <?php elseif ($e['places_restantes'] > 0): ?>
                <a href="api/evenements.php?action=inscrire&id=<?= $e['id'] ?>"
                   class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600">
                    S'inscrire
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="/lang/i18n.js"></script>
</body>
</html>