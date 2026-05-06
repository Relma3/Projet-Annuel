<?php
require_once 'db_connect.php';

$stmt = $pdo->query("SELECT * FROM conseil WHERE visible = 1 ORDER BY created_at DESC");
$conseils = $stmt->fetchAll();
?>

<h1>Conseils</h1>

<?php foreach ($conseils as $c): ?>
    <h2><?= htmlspecialchars($c['titre']) ?></h2>
    <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
    <hr>
<?php endforeach; ?>