<?php
/**Gestion des commandes boutique (admin) */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware.php';
verifier_admin();

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT c.*, CONCAT(s.prenom, ' ', s.nom) AS senior_nom
        FROM commandes c
        JOIN senior s ON c.id_senior = s.id_senior
        ORDER BY c.created_at DESC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($method === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($data['id_commande'] ?? 0);
    $statut = $data['statut'] ?? '';

    if (!$id || !in_array($statut, ['expediee', 'livree'])) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Données invalides']);
        exit;
    }

    $pdo->prepare("UPDATE commandes SET statut = ? WHERE id_commande = ?")
        ->execute([$statut, $id]);

    echo json_encode(['ok' => true]);
}