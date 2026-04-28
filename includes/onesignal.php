<?php
$onesignal_app_id = getenv('ONESIGNAL_APP_ID') ?: '';
?>
<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "<?= htmlspecialchars($onesignal_app_id) ?>",
      notifyButton: { enable: false },
      allowLocalhostAsSecureOrigin: true,
    });

    // Demande la permission à l'utilisateur
    await OneSignal.Slidedown.promptPush();

    // Récupère le Player ID et l'envoie au backend
    const playerId = await OneSignal.User.PushSubscription.id;
    if (playerId) {
      fetch('/api/save_player_id.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: playerId })
      });
    }
  });
</script>