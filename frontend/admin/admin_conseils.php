<?php
require_once '../../db_connect.php';

$stmt = $pdo->query("SELECT * FROM conseil ORDER BY created_at DESC");
$conseils = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin Conseils</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">

<style>
body { font-family: 'DM Sans', sans-serif; }
</style>

</head>

<body class="bg-slate-900 text-white p-10">

<h1 class="text-3xl font-bold mb-6">💡 Gestion des conseils</h1>

<!-- 🔥 Bouton ajouter -->
<button onclick="toggleForm()" 
class="mb-6 bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-xl font-bold shadow">
➕ Ajouter un conseil
</button>

<!-- 🔥 FORMULAIRE -->
<div id="formAdd" class="hidden bg-slate-800 p-6 rounded-2xl mb-8 shadow">
<form action="traitement_conseil.php" method="POST" class="space-y-4">

<input type="text" name="titre" placeholder="Titre"
class="w-full p-3 rounded-lg bg-slate-700 text-white" required>

<textarea name="contenu" placeholder="Contenu"
class="w-full p-3 rounded-lg bg-slate-700 text-white" required></textarea>

<input type="text" name="categorie" placeholder="Catégorie"
class="w-full p-3 rounded-lg bg-slate-700 text-white">

<input type="text" name="auteur" placeholder="Auteur"
class="w-full p-3 rounded-lg bg-slate-700 text-white">

<button type="submit" name="action" value="add"
class="bg-green-500 hover:bg-green-600 px-6 py-3 rounded-xl font-bold">
✅ Publier
</button>

</form>
</div>

<!-- 🔥 TABLEAU -->
<div class="bg-slate-800 rounded-2xl shadow overflow-hidden">

<table class="w-full text-left">
<thead class="bg-slate-700 text-sm uppercase text-slate-300">
<tr>
<th class="p-4">ID</th>
<th class="p-4">Titre</th>
<th class="p-4">Catégorie</th>
<th class="p-4">Visible</th>
<th class="p-4">Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($conseils as $c): ?>
<tr class="border-b border-slate-700 hover:bg-slate-700">

<td class="p-4"><?= $c['id_conseil'] ?></td>

<td class="p-4 font-bold"><?= htmlspecialchars($c['titre']) ?></td>

<td class="p-4"><?= htmlspecialchars($c['categorie'] ?? '—') ?></td>

<td class="p-4">
<?php if ($c['visible']): ?>
<span class="text-green-400 font-bold">✔ Visible</span>
<?php else: ?>
<span class="text-red-400 font-bold">✖ Caché</span>
<?php endif; ?>
</td>

<td class="p-4 flex gap-2">

<!-- DELETE -->
<form action="traitement_conseil.php" method="POST">
<input type="hidden" name="id" value="<?= $c['id_conseil'] ?>">
<button type="submit" name="action" value="delete"
class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg">
🗑
</button>
</form>

</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

<script>
function toggleForm() {
    document.getElementById('formAdd').classList.toggle('hidden');
}
</script>

</body>
</html>