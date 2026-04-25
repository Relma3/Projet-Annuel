<?php
session_start();
require_once 'db_connect.php';
 
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}
 
$id_service = isset($_GET['id_service']) ? intval($_GET['id_service']) : 0;
$id_senior  = $_SESSION['id'];
 

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
 
$stmtDispos = $pdo->prepare("
    SELECT id_disponibilite, date_debut, date_fin
    FROM disponibilites
    WHERE id_prestataire = ?
      AND type = 'libre'
      AND date_debut >= NOW()
    ORDER BY date_debut ASC
");
$stmtDispos->execute([$id_pres]);
$dispos = $stmtDispos->fetchAll();

$erreur  = null;
$succes  = false;
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dispo    = intval($_POST['id_disponibilite'] ?? 0);
    $debut       = $_POST['debut']       ?? '';
    $fin         = $_POST['fin']         ?? '';
    $description = trim($_POST['description'] ?? '');
 
    if (!$id_dispo || !$debut || !$fin) {
        $erreur = "Veuillez sélectionner un créneau.";
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
            $stmtCheck->execute([$id_dispo, $id_pres, $debut, $fin]);
            $dispo = $stmtCheck->fetch();
 
            if (!$dispo) {
                $pdo->rollBack();
                $erreur = "Ce créneau vient d'être pris. Veuillez en choisir un autre.";
            } else {
                // Créer la réservation
                $stmtRes = $pdo->prepare("
                    INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut)
                    VALUES (?, ?, ?, ?, 'en_attente')
                ");
                $stmtRes->execute([$id_senior, $id_pres, $debut, $description]);
                $idReservation = $pdo->lastInsertId();
 
                $pdo->prepare("
                    UPDATE disponibilites SET type = 'reserve', id_reservation = ?
                    WHERE id_disponibilite = ?
                ")->execute([$idReservation, $id_dispo]);
 
    
                if ($dispo['date_debut'] < $debut) {
                    $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")
                        ->execute([$id_pres, $dispo['date_debut'], $debut]);
                }
                if ($dispo['date_fin'] > $fin) {
                    $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")
                        ->execute([$id_pres, $fin, $dispo['date_fin']]);
                }
 
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
        tailwind.config = { theme: { extend: {
            colors: { 'sable': '#F4EDDE', 'corail': '#FF885B', 'menthe': '#A0E8AF', 'peche': '#FFD9CA' },
            fontFamily: { sans: ['DM Sans', 'sans-serif'], title: ['Quicksand', 'sans-serif'] },
            borderRadius: { senior: '28px' }
        }}}
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
            <img src="perso.png" class="w-full h-full object-cover">
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

        <div>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="bg-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                Choisissez un créneau disponible
            </h2>
 
            <?php if (empty($dispos)): ?>
                <div class="bg-slate-50 rounded-2xl p-8 text-center text-slate-400">
                    <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block"></i>
                    Aucun créneau disponible pour ce prestataire pour le moment.
                </div>
            <?php else: ?>
                <div class="mb-5 bg-slate-50 rounded-2xl p-4 flex items-center gap-4">
                    <label class="text-sm font-bold text-slate-500 whitespace-nowrap">Durée souhaitée :</label>
                    <select id="duree" onchange="mettreAJourFin()" class="flex-1 p-3 rounded-xl bg-white border border-slate-200 font-bold focus:ring-2 focus:ring-corail outline-none">
                        <option value="1">1 heure</option>
                        <option value="2" selected>2 heures</option>
                        <option value="3">3 heures</option>
                        <option value="4">4 heures</option>
                    </select>
                </div>
 
                <div class="space-y-3" id="liste-creneaux">
                    <?php foreach ($dispos as $d):
                        $tsDebut  = strtotime($d['date_debut']);
                        $tsFin    = strtotime($d['date_fin']);
                        $dureeMax = round(($tsFin - $tsDebut) / 3600, 1);
                    ?>
                    <label class="block cursor-pointer group">
                        <input type="radio" name="id_disponibilite" value="<?php echo $d['id_disponibilite']; ?>"
                               data-debut="<?php echo $d['date_debut']; ?>"
                               data-fin="<?php echo $d['date_fin']; ?>"
                               class="peer sr-only" required>
                        <div class="flex justify-between items-center p-5 border-2 border-slate-100 rounded-2xl bg-slate-50
                                    peer-checked:border-corail peer-checked:bg-peche transition-all group-hover:bg-peche/50">
                            <div>
                                <p class="font-bold text-slate-800">
                                    <?php echo date('l d F', $tsDebut); ?>
                                </p>
                                <p class="text-sm text-slate-500 mt-1">
                                    <i class="fa-regular fa-clock mr-1"></i>
                                    <?php echo date('H:i', $tsDebut); ?> → <?php echo date('H:i', $tsFin); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="bg-menthe/60 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full">
                                    Dispo <?php echo $dureeMax; ?>h
                                </span>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
 
                <input type="hidden" name="debut" id="input-debut">
                <input type="hidden" name="fin"   id="input-fin">
            <?php endif; ?>
        </div>
 
        <div>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="bg-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                Message pour le prestataire <span class="text-slate-400 font-normal text-sm">(optionnel)</span>
            </h2>
            <textarea name="description" placeholder="Précisions sur l'adresse, besoins spécifiques..."
                      class="w-full p-5 bg-slate-50 rounded-2xl border-2 border-slate-100 focus:border-corail outline-none min-h-[100px] resize-none"></textarea>
        </div>
 
        <div id="recap-cout" class="bg-slate-50 rounded-2xl p-5 hidden">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Coût estimé :</span>
                <span id="cout-estime" class="text-2xl font-bold text-slate-900"></span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Basé sur <?php echo $offre['prix']; ?>€/h</p>
        </div>
 
        <?php if (!empty($dispos)): ?>
        <button type="submit"
                class="w-full bg-corail text-white py-5 rounded-senior font-bold text-lg shadow-lg shadow-orange-200 hover:scale-[1.02] transition-all">
            <i class="fa-solid fa-check mr-2"></i>Confirmer la réservation
        </button>
        <p class="text-center text-slate-400 text-sm">
            <i class="fa-solid fa-lock mr-1"></i> Paiement sécurisé Silver Happy
        </p>
        <?php endif; ?>
 
    </form>
</main>
 
<script>
const tarif = <?php echo (float)$offre['prix']; ?>;
 
document.addEventListener('change', function(e) {
    if (e.target.name === 'id_disponibilite' || e.target.id === 'duree') {
        mettreAJourFin();
    }
});
 
function mettreAJourFin() {
    const selected = document.querySelector('input[name="id_disponibilite"]:checked');
    if (!selected) return;
 
    const debut     = new Date(selected.dataset.debut);
    const finDispo  = new Date(selected.dataset.fin);
    const duree     = parseInt(document.getElementById('duree').value);
 
    const finVoulue = new Date(debut.getTime() + duree * 3600 * 1000);
    const fin       = finVoulue <= finDispo ? finVoulue : finDispo;
 
    document.getElementById('input-debut').value = toMysql(debut);
    document.getElementById('input-fin').value   = toMysql(fin);
 
    const heuresReelles = (fin - debut) / 3600000;
    document.getElementById('cout-estime').textContent = (tarif * heuresReelles).toFixed(2) + ' €';
    document.getElementById('recap-cout').classList.remove('hidden');
}
 
function toMysql(date) {
    return date.toISOString().slice(0, 19).replace('T', ' ');
}
</script>
 
</body>
</html>
 
