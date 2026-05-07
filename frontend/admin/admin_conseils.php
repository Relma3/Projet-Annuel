<?php
require_once '../../db_connect.php';

// récupérer les conseils
$stmt = $pdo->query("SELECT * FROM conseil ORDER BY created_at DESC");
$conseils = $stmt->fetchAll();
?>

<h1>Gestion des conseils</h1>

<a href="add_conseil.php">➕ Ajouter un conseil</a>

<table border="1">
<tr>
    <th>ID</th>
    <th>Titre</th>
    <th>Catégorie</th>
    <th>Visible</th>
</tr>

<?php foreach ($conseils as $c): ?>
<tr>
    <td><?= $c['id_conseil'] ?></td>
    <td><?= $c['titre'] ?></td>
    <td><?= $c['categorie'] ?></td>
    <td><?= $c['visible'] ?></td>
</tr>
<?php endforeach; ?>

</table>