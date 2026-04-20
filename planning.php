<?php
require_once 'check_session.php';

if (!$is_connected || $_SESSION['type'] != 'senior') {
    header('Location: connexion.php');
    exit;
}

require_once 'db_connect.php';

$db = getDB();
$stmt = $db->prepare("SELECT r.*, u.email AS prestataire_email FROM reservation r JOIN utilisateur u ON r.id_prestataire = u.id_utilisateur WHERE r.id_senior = ? ORDER BY r.date_reservation ASC");
$stmt->execute([$_SESSION['id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Planning - Silver Happy</title>
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

<div class="max-w-4xl mx-auto mt-10 px-4">
    <h1 class="text-3xl font-bold text-orange-500 mb-2">Mon Planning</h1>
    <p class="text-gray-500 mb-8">Vos rendez-vous à venir</p>

    <div class="mb-6">
        <a href="reservation.php" class="bg-orange-500 text-white text-lg font-bold px-8 py-4 rounded-2xl inline-block">
            + Prendre un nouveau RDV
        </a>
    </div>

    <?php if (empty($reservations)) { ?>
        <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-400">
            <p class="text-xl">Aucun rendez-vous pour le moment.</p>
        </div>
    <?php } else { ?>
        <div class="space-y-4">
            <?php foreach ($reservations as $r) { ?>
                <div class="bg-white rounded-2xl shadow p-6 flex justify-between items-center">
                    <div>
                        <p class="text-xl font-bold text-gray-800">
                            <?php echo date('d/m/Y H:i', strtotime($r['date_reservation'])); ?>
                        </p>

                        <p class="text-gray-500 mt-1">
                            Prestataire : <?php echo $r['prestataire_email']; ?>
                        </p>

                        <?php if (!empty($r['description'])) { ?>
                            <p class="text-gray-400 mt-1 italic">
                                <?php echo $r['description']; ?>
                            </p>
                        <?php } ?>
                    </div>

                    <div class="text-right">
                        <?php
                        $texte = $r['statut'];
                        $classe = 'bg-gray-100 text-gray-700';

                        if ($r['statut'] == 'en_attente') {
                            $texte = 'En attente';
                            $classe = 'bg-yellow-100 text-yellow-700';
                        }

                        if ($r['statut'] == 'confirme') {
                            $texte = 'Confirmé';
                            $classe = 'bg-green-100 text-green-700';
                        }

                        if ($r['statut'] == 'annule') {
                            $texte = 'Annulé';
                            $classe = 'bg-red-100 text-red-700';
                        }
                        ?>

                        <span class="<?php echo $classe; ?> px-4 py-2 rounded-full font-semibold text-base">
                            <?php echo $texte; ?>
                        </span>

                        <?php if ($r['statut'] == 'en_attente') { ?>
                            <form action="traitement_annulation.php" method="POST" class="mt-3">
                                <input type="hidden" name="id_reservation" value="<?php echo $r['id_reservation']; ?>">
                                <button type="submit" class="text-red-400 text-base underline">
                                    Annuler
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<script src="onesignal.js"></script>
</body>
</html>