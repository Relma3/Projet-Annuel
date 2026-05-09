<?php
/**
 * Envoi de notifications push OneSignal — Silver Happy
 * Enregistre en BDD et envoie via l'API OneSignal si player_id disponible
 */
function envoyerNotification(PDO $pdo, int $id_senior, string $titre, string $message, string $type = 'info'): void {
    // Récupérer le player_id du senior
    $stmt = $pdo->prepare("SELECT onesignal_player_id FROM senior WHERE id_senior = ?");
    $stmt->execute([$id_senior]);
    $player_id = $stmt->fetchColumn();

    // Historiser en BDD même sans player_id
    $pdo->prepare("INSERT INTO notification (id_senior, titre, message, type) VALUES (?, ?, ?, ?)")
        ->execute([$id_senior, $titre, $message, $type]);

    // Envoyer via OneSignal uniquement si le senior a accepté les notifs
    if (!$player_id) return;

    $onesignal_app_id  = getenv('ONESIGNAL_APP_ID');
    $onesignal_api_key = getenv('ONESIGNAL_API_KEY');

    $payload = json_encode([
        'app_id'             => $onesignal_app_id,
        'include_player_ids' => [$player_id],
        'headings'           => ['en' => $titre, 'fr' => $titre],
        'contents'           => ['en' => $message, 'fr' => $message],
    ]);

    $ch = curl_init('https://onesignal.com/api/v1/notifications');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $onesignal_api_key,
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
}