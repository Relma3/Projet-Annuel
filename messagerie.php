<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

require_once 'db_connect.php';

$db = getDB();

$stmt = $db->prepare("SELECT m.*, u.email AS expediteur_email
FROM messages m
JOIN utilisateur u ON m.id_expediteur = u.id_utilisateur
WHERE m.id_destinataire = ? OR m.id_expediteur = ?
ORDER BY m.created_at ASC");

$stmt->execute([$_SESSION['id'], $_SESSION['id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Silver Happy</title>
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

<div class="max-w-3xl mx-auto mt-10 px-4">
    <h1 class="text-3xl font-bold text-orange-500 mb-2">Messagerie</h1>
    <p class="text-gray-500 mb-6">Echangez avec l'equipe Silver Happy</p>

    <div class="bg-white rounded-2xl shadow p-6 mb-6 space-y-4 min-h-64">
        <?php if (empty($messages)) { ?>
            <p class="text-center text-gray-400 text-lg py-10">Aucun message pour le moment.</p>
        <?php } else { ?>
            <?php foreach ($messages as $m) { ?>
                <?php $moi = $m['id_expediteur'] == $_SESSION['id']; ?>

                <div class="flex <?php echo $moi ? 'justify-end' : 'justify-start'; ?>">
                    <div class="max-w-xs lg:max-w-md px-5 py-3 rounded-2xl text-lg <?php echo $moi ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-800'; ?>">
                        <?php if (!$moi) { ?>
                            <p class="text-sm font-semibold text-orange-500 mb-1">Equipe Silver Happy</p>
                        <?php } ?>
                             <!-- htmlspecialchars convertit < en &lt; — le script ne s'exécute plus, il s'affiche en texte. -->
                        <p><?php echo htmlspecialchars($m['contenu'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <p class="text-xs mt-1 <?php echo $moi ? 'text-orange-100' : 'text-gray-400'; ?>">
                            <?php echo date('d/m/Y à H:i', strtotime($m['created_at'])); ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <form action="traitement_message.php" method="POST" class="bg-white rounded-2xl shadow p-6">
        <label class="block text-lg font-semibold mb-3">Votre message</label>
        <textarea name="contenu" rows="3" required placeholder="Ecrivez votre message ici..." class="w-full border-2 border-gray-200 rounded-xl p-4 text-lg"></textarea>

        <button type="submit" class="mt-4 w-full bg-orange-500 text-white text-xl font-bold py-4 rounded-xl">
            Envoyer le message
        </button>
    </form>
</div>

</body>
</html>