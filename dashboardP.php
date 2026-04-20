<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] != 'prestataire') {
    header('Location: connexionpres.php');
    exit();
}

$id_pres = $_SESSION['id'];

$stmtProfil = $pdo->prepare("SELECT * FROM prestataire WHERE id_prestataire = ?");
$stmtProfil->execute([$id_pres]);
$profil = $stmtProfil->fetch();

$stmtRes = $pdo->prepare("
    SELECT r.*, s.nom AS senior_nom, s.prenom AS senior_prenom, s.adresse AS senior_adresse, u.email AS senior_email
    FROM reservation r
    JOIN senior s ON s.id_senior = r.id_senior
    JOIN utilisateur u ON u.id_utilisateur = r.id_senior
    WHERE r.id_prestataire = ?
    ORDER BY r.date_reservation ASC
");
$stmtRes->execute([$id_pres]);
$reservations = $stmtRes->fetchAll();

$rdv_a_venir = [];
foreach ($reservations as $r) {
    if (($r['statut'] == 'en_attente' || $r['statut'] == 'confirme') && strtotime($r['date_reservation']) >= time()) {
        $rdv_a_venir[] = $r;
    }
}

$stmtDevis = $pdo->prepare("
    SELECT d.*, s.nom AS senior_nom, s.prenom AS senior_prenom
    FROM devis d
    JOIN senior s ON s.id_senior = d.id_senior
    WHERE d.id_prestataire = ?
    ORDER BY d.created_at DESC
");
$stmtDevis->execute([$id_pres]);
$devis_list = $stmtDevis->fetchAll();

$stmtEval = $pdo->prepare("
    SELECT e.*, s.prenom
    FROM evaluations e
    JOIN senior s ON s.id_senior = e.id_senior
    WHERE e.id_prestataire = ?
    ORDER BY e.created_at DESC
    LIMIT 10
");
$stmtEval->execute([$id_pres]);
$evaluations = $stmtEval->fetchAll();

$note_moy = 0;
if (count($evaluations) > 0) {
    $total = 0;
    foreach ($evaluations as $e) {
        $total += $e['note'];
    }
    $note_moy = round($total / count($evaluations), 1);
}

$stmtStats = $pdo->prepare("
    SELECT COUNT(*) FROM reservation
    WHERE id_prestataire = ? AND statut = 'termine'
    AND MONTH(date_reservation) = MONTH(NOW())
    AND YEAR(date_reservation) = YEAR(NOW())
");
$stmtStats->execute([$id_pres]);
$pres_ce_mois = $stmtStats->fetchColumn();

$mois_courant = date('Y-m');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Prestataire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #F0F9F4; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .nav-tab.active { background: #059669; color: white; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-attente { background: #FEF3C7; color: #92400E; }
        .badge-confirme { background: #D1FAE5; color: #065F46; }
        .badge-termine { background: #DBEAFE; color: #1E40AF; }
        .badge-annule { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>

<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <div>
        <strong class="text-emerald-700 text-2xl">Silver Happy PRO</strong>
    </div>
    <div class="flex items-center gap-4">
        <span>Bonjour <?php echo $profil['prenom'] . ' ' . $profil['nom']; ?></span>
        <?php if ($profil['statut'] == 'valide') { ?>
            <span class="badge badge-confirme">Validé</span>
        <?php } else { ?>
            <span class="badge badge-attente">En attente</span>
        <?php } ?>
        <a href="logout.php" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full">Déconnexion</a>
    </div>
</nav>

<main class="max-w-6xl mx-auto p-6">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow text-center">
            <p class="text-3xl font-bold text-emerald-600"><?php echo count($rdv_a_venir); ?></p>
            <p>RDV à venir</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow text-center">
            <p class="text-3xl font-bold text-blue-600"><?php echo $pres_ce_mois; ?></p>
            <p>Prestations ce mois</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow text-center">
            <p class="text-3xl font-bold text-amber-500"><?php echo $note_moy > 0 ? $note_moy : '-'; ?></p>
            <p>Note moyenne</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow text-center">
            <p class="text-3xl font-bold text-purple-600"><?php echo count($devis_list); ?></p>
            <p>Devis envoyés</p>
        </div>
    </div>

    <div class="flex gap-2 mb-6 flex-wrap">
        <button onclick="switchTab('planning')" id="tab-btn-planning" class="nav-tab active px-4 py-2 rounded-full bg-white">Planning</button>
        <button onclick="switchTab('devis')" id="tab-btn-devis" class="nav-tab px-4 py-2 rounded-full bg-white">Devis</button>
        <button onclick="switchTab('factures')" id="tab-btn-factures" class="nav-tab px-4 py-2 rounded-full bg-white">Factures</button>
        <button onclick="switchTab('evaluations')" id="tab-btn-evaluations" class="nav-tab px-4 py-2 rounded-full bg-white">Evaluations</button>
        <button onclick="switchTab('profil')" id="tab-btn-profil" class="nav-tab px-4 py-2 rounded-full bg-white">Mon Profil</button>
    </div>

    <div id="tab-planning" class="tab-content active bg-white rounded-2xl p-6 shadow">
        <h2 class="text-xl font-bold mb-4">Mon Planning</h2>

        <?php if (empty($reservations)) { ?>
            <p>Aucune réservation.</p>
        <?php } else { ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-emerald-50">
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Senior</th>
                        <th class="p-3 text-left">Adresse</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r) { ?>
                        <?php
                        $classe = 'badge-annule';
                        $label = 'Annulé';

                        if ($r['statut'] == 'en_attente') {
                            $classe = 'badge-attente';
                            $label = 'En attente';
                        }
                        if ($r['statut'] == 'confirme') {
                            $classe = 'badge-confirme';
                            $label = 'Confirmé';
                        }
                        if ($r['statut'] == 'termine') {
                            $classe = 'badge-termine';
                            $label = 'Terminé';
                        }
                        ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo date('d/m/Y H:i', strtotime($r['date_reservation'])); ?></td>
                            <td class="p-3"><?php echo $r['senior_prenom'] . ' ' . $r['senior_nom']; ?></td>
                            <td class="p-3"><?php echo $r['senior_adresse']; ?></td>
                            <td class="p-3"><?php echo $r['description']; ?></td>
                            <td class="p-3"><span class="badge <?php echo $classe; ?>"><?php echo $label; ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>

    <div id="tab-devis" class="tab-content bg-white rounded-2xl p-6 shadow">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Mes Devis</h2>
            <button onclick="toggleFormDevis()" class="bg-emerald-600 text-white px-4 py-2 rounded-full">+ Nouveau devis</button>
        </div>

        <div id="form-devis" class="hidden bg-emerald-50 p-4 rounded-xl mb-6">
            <input type="number" id="devis-senior-id" placeholder="ID senior" class="border p-2 rounded w-full mb-3">
            <input type="number" id="devis-montant" placeholder="Montant" class="border p-2 rounded w-full mb-3">
            <input type="text" id="devis-desc" placeholder="Description" class="border p-2 rounded w-full mb-3">
            <button onclick="envoyerDevis()" class="bg-emerald-600 text-white px-4 py-2 rounded">Envoyer</button>
            <p id="devis-msg" class="mt-3 hidden"></p>
        </div>

        <?php if (empty($devis_list)) { ?>
            <p>Aucun devis envoyé.</p>
        <?php } else { ?>
            <?php foreach ($devis_list as $d) { ?>
                <div class="bg-slate-50 rounded-xl p-4 mb-3 flex justify-between">
                    <div>
                        <p class="font-bold"><?php echo $d['senior_prenom'] . ' ' . $d['senior_nom']; ?></p>
                        <p><?php echo $d['description']; ?></p>
                        <p class="text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-emerald-700"><?php echo number_format($d['montant'], 2); ?> €</p>
                        <p><?php echo $d['statut']; ?></p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <div id="tab-factures" class="tab-content bg-white rounded-2xl p-6 shadow">
        <h2 class="text-xl font-bold mb-4">Mes Factures</h2>
        <div id="factures-container">Chargement...</div>
    </div>

    <div id="tab-evaluations" class="tab-content bg-white rounded-2xl p-6 shadow">
        <h2 class="text-xl font-bold mb-4">Mes Evaluations</h2>

        <?php if (empty($evaluations)) { ?>
            <p>Aucune évaluation.</p>
        <?php } else { ?>
            <?php foreach ($evaluations as $ev) { ?>
                <div class="bg-slate-50 rounded-xl p-4 mb-3">
                    <p class="font-bold">Note : <?php echo $ev['note']; ?>/5</p>
                    <p><?php echo $ev['commentaire']; ?></p>
                    <p class="text-sm text-gray-500"><?php echo $ev['prenom']; ?> - <?php echo date('d/m/Y', strtotime($ev['created_at'])); ?></p>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <div id="tab-profil" class="tab-content bg-white rounded-2xl p-6 shadow max-w-xl">
        <h2 class="text-xl font-bold mb-4">Mon Profil</h2>

        <form action="update_pres.php" method="POST" class="space-y-4">
            <input type="text" name="categorie" value="<?php echo $profil['categorie']; ?>" class="w-full border rounded p-3" placeholder="Catégorie">
            <input type="number" name="tarif_horaire" value="<?php echo $profil['tarif_horaire']; ?>" class="w-full border rounded p-3" placeholder="Tarif horaire">
            <textarea name="description" class="w-full border rounded p-3" rows="4" placeholder="Description"><?php echo $profil['description']; ?></textarea>
            <input type="text" name="ville" value="<?php echo $profil['ville']; ?>" class="w-full border rounded p-3" placeholder="Ville">
            <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded">Enregistrer</button>
        </form>

        <div class="mt-6">
            <h3 class="font-bold mb-3">Télécharger ma facture</h3>
            <div class="flex gap-3">
                <input type="month" id="mois-facture" value="<?php echo $mois_courant; ?>" class="border rounded p-3">
                <button onclick="telechargerFacture()" class="bg-blue-600 text-white px-4 py-2 rounded">Télécharger PDF</button>
            </div>
        </div>
    </div>

</main>

<script>
const TOKEN = "<?php echo $_SESSION['jwt_token'] ?? ''; ?>";
const ID_PRES = <?php echo $id_pres; ?>;

function switchTab(name) {
    document.querySelectorAll('.tab-content').forEach(function(tab) {
        tab.classList.remove('active');
    });

    document.querySelectorAll('.nav-tab').forEach(function(btn) {
        btn.classList.remove('active');
    });

    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('tab-btn-' + name).classList.add('active');

    if (name == 'factures') {
        chargerFactures();
    }
}

function toggleFormDevis() {
    document.getElementById('form-devis').classList.toggle('hidden');
}

async function envoyerDevis() {
    const id_senior = document.getElementById('devis-senior-id').value;
    const montant = document.getElementById('devis-montant').value;
    const description = document.getElementById('devis-desc').value;
    const msg = document.getElementById('devis-msg');

    if (!id_senior || !montant || !description) {
        msg.textContent = 'Remplis tous les champs';
        msg.classList.remove('hidden');
        return;
    }

    const res = await fetch('/api/devis/creer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + TOKEN
        },
        body: JSON.stringify({
            id_senior: parseInt(id_senior),
            montant: parseFloat(montant),
            description: description
        })
    });

    const data = await res.json();

    if (res.ok) {
        msg.textContent = 'Devis envoyé';
        msg.classList.remove('hidden');
        setTimeout(function() {
            location.reload();
        }, 1000);
    } else {
        msg.textContent = data.message || 'Erreur';
        msg.classList.remove('hidden');
    }
}

async function chargerFactures() {
    const container = document.getElementById('factures-container');

    try {
        const res = await fetch('/api/prestataire/facturation/mes-factures', {
            headers: {
                'Authorization': 'Bearer ' + TOKEN
            }
        });

        const data = await res.json();

        if (!data.factures || data.factures.length == 0) {
            container.innerHTML = 'Aucune facture';
            return;
        }

        let html = '';
        data.factures.forEach(function(f) {
            html += '<div class="bg-slate-50 rounded-xl p-4 mb-3 flex justify-between">';
            html += '<div><p class="font-bold">' + f.nom + '</p><p class="text-sm text-gray-500">' + f.date + '</p></div>';
            html += '<a href="/api/prestataire/facturation/telecharger?nom=' + encodeURIComponent(f.nom) + '&token=' + TOKEN + '" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded">PDF</a>';
            html += '</div>';
        });

        container.innerHTML = html;
    } catch (e) {
        container.innerHTML = 'Erreur de chargement';
    }
}

function telechargerFacture() {
    const mois = document.getElementById('mois-facture').value;
    window.open('/api/prestataire/facturation/telecharger?nom=SH-' + ID_PRES + '-' + mois.replace('-', '') + '.pdf', '_blank');
}
</script>
</body>
</html>