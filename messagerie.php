<?php
require_once 'check_session.php';
if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}
require_once 'db_connect.php';
$db = getDB();
$stmt = $db->prepare("
    SELECT m.*, u.email AS expediteur_email
    FROM messages m
    JOIN utilisateur u ON m.id_expediteur = u.id_utilisateur
    WHERE m.id_destinataire = ? OR m.id_expediteur = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$_SESSION['id'], $_SESSION['id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie — Silver Happy</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <script>
    tailwind.config = {
        theme: { extend: {
            colors: {
                'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B',
                'vert-menthe': '#A0E8AF', 'peche-pastel': '#FFD9CA',
                'corail': '#FF885B', 'peche-pale': '#FFD9CA', 'sable': '#F4EDDE',
            },
            fontFamily: { 'sans': ['Roboto','sans-serif'], 'title': ['Quicksand','sans-serif'] },
            borderRadius: { 'senior': '28px' }
        }}
    }
    </script>
    <style>body { font-family: 'Roboto', sans-serif; background-color: #F4EDDE; } h1,h2,h3 { font-family: 'Quicksand', sans-serif; }</style>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/onesignal.php'; ?>
</head>
<body class="bg-sable-doux text-slate-800 min-h-screen">
<?php include 'accessibilite.php'; ?>

<nav class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <img src="/logo.png" alt="Silver Happy" class="h-12">
        <div>
            <span class="text-2xl font-bold text-orange-corail block leading-none font-title">Silver Happy</span>
            <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Bien vivre après 60 ans</span>
        </div>
    </div>
    <div class="flex gap-4 items-center">
        <a href="dashboardS.php" class="text-slate-600 hover:text-orange-corail font-bold transition-colors">
            <i class="fa-solid fa-arrow-left mr-1"></i> Tableau de bord
        </a>
        <a href="logout.php" class="text-red-400 hover:text-red-600 font-bold transition-colors" data-i18n="nav_logout">Déconnexion</a>
    </div>
</nav>

<div class="pt-28 px-6 max-w-3xl mx-auto pb-16">
    <h1 class="text-3xl font-title font-bold text-orange-corail mb-2">Ma Messagerie</h1>
    <p class="text-slate-500 mb-6">Échangez avec l'équipe Silver Happy</p>

    <?php if (isset($_GET['envoye'])): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-6 font-bold text-sm">
            <i class="fa-solid fa-check mr-2"></i>Message envoyé avec succès.
        </div>
    <?php endif; ?>

    <!-- Fil de messages -->
    <div class="bg-white rounded-senior shadow-sm p-6 mb-6 space-y-4 min-h-64">
        <?php if (empty($messages)): ?>
            <p class="text-center text-slate-300 text-lg py-10 italic">
                <i class="fa-solid fa-comment-dots text-4xl block mb-3 text-slate-200"></i>
                Aucun message pour le moment.
            </p>
        <?php else: ?>
            <?php foreach ($messages as $m):
                $moi = $m['id_expediteur'] == $_SESSION['id'];
            ?>
                <div class="flex <?= $moi ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-xs lg:max-w-md px-5 py-3 rounded-2xl <?= $moi ? 'bg-orange-corail text-white' : 'bg-slate-100 text-slate-800' ?>">
                        <?php if (!$moi): ?>
                            <p class="text-sm font-bold text-orange-corail mb-1">Équipe Silver Happy</p>
                        <?php endif; ?>
                        <p class="text-base"><?= htmlspecialchars($m['contenu']) ?></p>
                        <p class="text-xs mt-1 <?= $moi ? 'text-orange-100' : 'text-slate-400' ?>">
                            <?= date('d/m/Y à H:i', strtotime($m['created_at'])) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Formulaire d'envoi -->
    <form action="traitement_message.php" method="POST" class="bg-white rounded-senior shadow-sm p-6">
        <label class="block font-bold text-slate-700 mb-3">Votre message</label>
        <textarea name="contenu" rows="3" required
            placeholder="Écrivez votre message ici..."
            class="w-full border-2 border-slate-100 rounded-2xl p-4 text-base focus:border-orange-corail focus:outline-none resize-none"></textarea>
        <button type="submit"
            class="mt-4 w-full bg-orange-corail text-white text-lg font-bold py-4 rounded-senior hover:brightness-110 transition-all">
            <i class="fa-solid fa-paper-plane mr-2"></i>Envoyer
        </button>
    </form>
</div>

<script src="/lang/i18n.js"></script>
</body>
</html>