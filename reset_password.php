<?php
/** Formulaire de réinitialisation de mot de passe */
require_once 'db_connect.php';

$token  = $_GET['token'] ?? '';
$erreur = null;
$succes = false;

if (!$token) { header('Location: connexion.php'); exit(); }

// Vérifier le token
$stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE token_confirmation = ? AND token_expiration > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $erreur = "Ce lien est invalide ou a expiré.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $mdp     = $_POST['password'] ?? '';
    $confirm = $_POST['confirm']  ?? '';

    if (strlen($mdp) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($mdp !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($mdp, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ?, token_confirmation = NULL, token_expiration = NULL WHERE id_utilisateur = ?")
            ->execute([$hash, $user['id_utilisateur']]);
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
<div class="bg-white rounded-2xl shadow p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-orange-500 mb-6">Nouveau mot de passe</h1>

    <?php if ($succes): ?>
        <p class="text-green-600 font-bold mb-4">✅ Mot de passe modifié avec succès !</p>
        <a href="connexion.php" class="block text-center bg-orange-500 text-white py-3 rounded-xl font-bold hover:bg-orange-600">
            Se connecter
        </a>
    <?php elseif ($erreur): ?>
        <p class="text-red-500 font-bold mb-4"><?= htmlspecialchars($erreur) ?></p>
        <a href="connexion.php" class="text-orange-500 hover:underline">Retour à la connexion</a>
    <?php else: ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-sm font-bold text-gray-500 block mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="text-sm font-bold text-gray-500 block mb-1">Confirmer le mot de passe</label>
                <input type="password" name="confirm" required minlength="8"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-xl font-bold hover:bg-orange-600">
                Enregistrer le nouveau mot de passe
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>