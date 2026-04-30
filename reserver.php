<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_service = isset($_GET['id_service']) ? intval($_GET['id_service']) : 0;
$id_dispo   = isset($_GET['id_dispo'])   ? intval($_GET['id_dispo'])   : 0;
$id_senior  = $_SESSION['id'];
$erreur     = null;

$jours = [
    'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'
];
$mois = [
    'January' => 'janvier', 'February' => 'février', 'March' => 'mars',
    'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet',
    'August' => 'août', 'September' => 'septembre', 'October' => 'octobre',
    'November' => 'novembre', 'December' => 'décembre'
];
$stmt = $pdo->prepare("
    SELECT s.*, p.prenom, p.nom, p.id_prestataire, p.tarif_horaire, p.note_moyenne, p.categorie
    FROM services s
    JOIN prestataire p ON s.id_prestataire = p.id_prestataire
    WHERE s.id_service = ?
");
$stmt->execute([$id_service]);
$offre = $stmt->fetch();
if (!$offre) { die("Service introuvable."); }

$id_pres = $offre['id_prestataire'];

$stmtDispo = $pdo->prepare("
    SELECT id_disponibilite, date_debut, date_fin
    FROM disponibilites
    WHERE id_disponibilite = ?
      AND id_prestataire   = ?
      AND type             = 'libre'
      AND date_debut       >= NOW()
");
$stmtDispo->execute([$id_dispo, $id_pres]);
$dispo_choisie = $stmtDispo->fetch();

if (!$dispo_choisie) {
    header('Location: liste_prestataires.php?cat=' . urlencode($offre['categorie']) . '&erreur=creneau_pris');
    exit();
}

$tsDebut  = strtotime($dispo_choisie['date_debut']);
$tsFin    = strtotime($dispo_choisie['date_fin']);
$dureeMax = round(($tsFin - $tsDebut) / 3600, 1);
$jour_fr  = $jours[date('l', $tsDebut)] . ' ' . date('d', $tsDebut) . ' ' . $mois[date('F', $tsDebut)] . ' ' . date('Y', $tsDebut);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dispo_post = intval($_POST['id_disponibilite'] ?? 0);
    $debut         = $_POST['debut'] ?? '';
    $fin           = $_POST['fin']   ?? '';
    $description   = trim($_POST['description'] ?? '');

    if (!$id_dispo_post || !$debut || !$fin) {
        $erreur = "Veuillez choisir une heure de début.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare("
                SELECT * FROM disponibilites
                WHERE id_disponibilite = ?
                  AND id_prestataire   = ?
                  AND type             = 'libre'
                  AND date_debut       <= ?
                  AND date_fin         >= ?
                FOR UPDATE
            ");
            $stmtCheck->execute([$id_dispo_post, $id_pres, $debut, $fin]);
            $dispo = $stmtCheck->fetch();

            if (!$dispo) {
                $pdo->rollBack();
                $erreur = "Ce créneau vient d'être pris. Veuillez en choisir un autre.";
            } else {
                $stmtRes = $pdo->prepare("
                    INSERT INTO reservation (id_senior, id_prestataire, date_reservation, date_fin, id_disponibilite, description, statut)
                    VALUES (?, ?, ?, ?, ?, ?, 'en_attente')
                ");
                $stmtRes->execute([$id_senior, $id_pres, $debut, $fin, $id_dispo_post, $description]);
                $pdo->commit();
                header('Location: dashboardS.php?msg=ok#planning');
                exit();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreur = "Erreur serveur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { sable: '#F4EDDE', corail: '#FF885B', menthe: '#A0E8AF', peche: '#FFD9CA' },
                fontFamily: { sans: ['DM Sans', 'sans-serif'], title: ['Quicksand', 'sans-serif'] },
                borderRadius: { senior: '28px' }
            }}
        }
    </script>
</head>
<body class="bg-sable font-sans text-slate-800 min-h-screen">

<nav class="fixed w-full bg-white/90 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
    <a href="index.php" class="text-xl font-bold text-corail font-title">Silver Happy</a>
    <a href="javascript:history.back()" class="text-slate-500 font-bold hover:text-corail">
        <i class="fa-solid fa-xmark mr-2"></i>Annuler
    </a>
</nav>

<main class="pt-28 pb-20 px-4 max-w-2xl mx-auto">

    <div class="bg-peche rounded-senior p-6 mb-6 flex items-center gap-5 shadow-sm">
        <div class="w-16 h-16 rounded-full bg-white overflow-hidden border-4 border-white shadow">
            <img src="perso.png" class="w-full h-full object-cover" alt="Prestataire">
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-corail tracking-wide">Votre prestataire</p>
            <h1 class="text-xl font-bold"><?php echo htmlspecialchars($offre['prenom'] . ' ' . $offre['nom']); ?></h1>
            <p class="text-sm text-slate-500"><?php echo htmlspecialchars($offre['nom_service']); ?> · <?php echo $offre['prix']; ?>€/h</p>
        </div>
    </div>

    <?php if ($erreur): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-6 font-bold text-sm">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo htmlspecialchars($erreur); ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-senior shadow-lg p-8 space-y-8">

        <input type="hidden" name="id_disponibilite" value="<?php echo $dispo_choisie['id_disponibilite']; ?>">
        <input type="hidden" name="debut" id="input-debut">
        <input type="hidden" name="fin"   id="input-fin">

        <div>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="bg-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                Votre créneau sélectionné
            </h2>

            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-5 mb-5">
                <p class="font-bold text-slate-800 mb-1">
                    <?php echo $jour_fr; ?>
                    <span class="text-xs text-emerald-600 font-bold ml-2 bg-white px-2 py-1 rounded-full">
                        Disponible <?php echo $dureeMax; ?>h
                    </span>
                </p>
                <p class="text-sm text-slate-500">
                    <i class="fa-regular fa-clock mr-1"></i>
                    De <?php echo date('H:i', $tsDebut); ?> à <?php echo date('H:i', $tsFin); ?>
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Heure de début</label>
                    <input type="time"
                           id="heure-debut"
                           class="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 font-bold focus:ring-2 focus:ring-corail outline-none"
                           min="<?php echo date('H:i', $tsDebut); ?>"
                           max="<?php echo date('H:i', $tsFin); ?>"
                           value="<?php echo date('H:i', $tsDebut); ?>"
                           data-date="<?php echo date('Y-m-d', $tsDebut); ?>"
                           data-debut-min="<?php echo date('H:i', $tsDebut); ?>"
                           data-fin-ts="<?php echo $tsFin; ?>"
                           onchange="calculer()">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Durée souhaitée</label>
                    <select id="duree-select" onchange="calculer()"
                            class="w-full p-3 bg-slate-50 rounded-xl border border-slate-200 font-bold focus:ring-2 focus:ring-corail outline-none">
                        <?php foreach ([1, 2, 3, 4] as $h): ?>
                        <option value="<?php echo $h; ?>"
                            <?php echo $h > $dureeMax ? 'disabled' : ''; ?>
                            <?php echo ($h == 2 && $dureeMax >= 2) || ($h == 1 && $dureeMax < 2) ? 'selected' : ''; ?>>
                            <?php echo $h; ?> heure<?php echo $h > 1 ? 's' : ''; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>


        <div>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="bg-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                Message pour le prestataire <span class="text-slate-400 font-normal text-sm">(optionnel)</span>
            </h2>
            <textarea name="description" placeholder="Précisions sur l'adresse, besoins spécifiques..."
                      class="w-full p-5 bg-slate-50 rounded-2xl border-2 border-slate-100 focus:border-corail outline-none min-h-[100px] resize-none"></textarea>
        </div>

        <!-- Récap -->
        <div id="recap-cout" class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
            <p class="text-xs font-bold text-emerald-600 uppercase mb-3"><i class="fa-solid fa-circle-check mr-1"></i>Récapitulatif</p>
            <div class="flex justify-between items-center mb-2">
                <span class="text-slate-500">Horaire :</span>
                <span id="recap-horaire" class="font-bold text-slate-800 text-right"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Coût estimé :</span>
                <span id="cout-estime" class="text-2xl font-bold text-corail"></span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Basé sur <?php echo $offre['prix']; ?>€/h · En attente de confirmation du prestataire</p>
        </div>

        <button type="submit" id="btn-confirmer"
                class="w-full bg-corail text-white py-5 rounded-senior font-bold text-lg shadow-lg hover:scale-[1.02] transition-all">
            <i class="fa-solid fa-paper-plane mr-2"></i>Envoyer la demande
        </button>
        <p class="text-center text-slate-400 text-sm">
            <i class="fa-solid fa-info-circle mr-1"></i>Votre demande sera envoyée au prestataire pour confirmation
        </p>

    </form>
</main>

<script>
const tarif   = <?php echo (float)$offre['prix']; ?>;
const finTs   = <?php echo $tsFin; ?> * 1000;
const dateStr = '<?php echo date('Y-m-d', $tsDebut); ?>';
const debutMinStr = '<?php echo date('H:i', $tsDebut); ?>';

function calculer() {
    const heureInput  = document.getElementById('heure-debut');
    const dureeSelect = document.getElementById('duree-select');

    if (!heureInput.value) return;

    const debut     = new Date(dateStr + 'T' + heureInput.value + ':00');
    const debutMin  = new Date(dateStr + 'T' + debutMinStr + ':00');
    const duree     = parseInt(dureeSelect.value);
    const finVoulue = new Date(debut.getTime() + duree * 3600 * 1000);
    const finMax    = new Date(finTs);
    const fin       = finVoulue <= finMax ? finVoulue : finMax;

    if (debut < debutMin) {
        heureInput.value = debutMinStr;
        return;
    }

    const dureeReelle = (fin - debut) / 3600000;
    if (dureeReelle < 0.5) return;

    document.getElementById('input-debut').value = toMysql(debut);
    document.getElementById('input-fin').value   = toMysql(fin);

    const opts = { weekday: 'long', day: 'numeric', month: 'long' };
    document.getElementById('recap-horaire').textContent =
        debut.toLocaleDateString('fr-FR', opts) + ' · ' +
        debut.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}) + ' → ' +
        fin.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
    document.getElementById('cout-estime').textContent = (tarif * dureeReelle).toFixed(2) + ' €';
}

function toMysql(date) {
    const local = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
    return local.toISOString().slice(0, 19).replace('T', ' ');
}

document.addEventListener('DOMContentLoaded', calculer);
</script>
</body>
</html>
