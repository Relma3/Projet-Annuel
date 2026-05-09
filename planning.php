<?php
session_start();
require_once 'db_connect.php';
require_once 'check_session.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_senior = $_SESSION['id'];
$prenom    = $_SESSION['prenom'] ?? 'Senior';

// Récupère les réservations confirmées/en attente
$stmtRes = $pdo->prepare("
    SELECT r.id_reservation, r.date_reservation, r.statut,
           COALESCE(s.nom_service, 'Prestation') AS titre,
           p.prenom AS p_prenom, p.nom AS p_nom,
           dv.id_devis, dv.montant_ttc, dv.statut AS devis_statut
    FROM reservation r
    JOIN prestataire p ON r.id_prestataire = p.id_prestataire
    LEFT JOIN disponibilites d ON r.id_disponibilite = d.id_disponibilite
    LEFT JOIN services s ON d.id_service = s.id_service
    LEFT JOIN devis dv ON dv.id_reservation = r.id_reservation
    WHERE r.id_senior = ?
      AND r.statut IN ('en_attente', 'confirme')
    ORDER BY r.date_reservation ASC
");
$stmtRes->execute([$id_senior]);
$reservations = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

// Récupère les RDV médicaux planifiés
$stmtRdv = $pdo->prepare("
    SELECT r.id_rdv, r.date_rdv, r.statut,
           p.prenom AS med_prenom, p.nom AS med_nom,
           p.specialite
    FROM rdv_medical r
    JOIN prestataire p ON r.id_prestataire = p.id_prestataire
    WHERE r.id_senior = ?
      AND r.statut = 'planifie'
    ORDER BY r.date_rdv ASC
");
$stmtRdv->execute([$id_senior]);
$rdvs = $stmtRdv->fetchAll(PDO::FETCH_ASSOC);

// Récupère les événements auxquels le senior est inscrit
$stmtEv = $pdo->prepare("
    SELECT e.id, e.titre, e.date_debut, e.lieu
    FROM evenements e
    JOIN inscription_evenement ie ON ie.id_evenement = e.id
    WHERE ie.id_senior = ?
      AND e.date_debut >= NOW()
    ORDER BY e.date_debut ASC
");
$stmtEv->execute([$id_senior]);
$evenements = $stmtEv->fetchAll(PDO::FETCH_ASSOC);

// Formate les événements pour FullCalendar 
$events = [];

foreach ($reservations as $r) {
    $events[] = [
        'id'    => 'res-' . $r['id_reservation'],
        'title' => $r['titre'] . ' — ' . $r['p_prenom'] . ' ' . $r['p_nom'],
        'start' => $r['date_reservation'],
        'color' => '#E37A55', 
        'extendedProps' => ['type' => 'service', 'statut' => $r['statut']]
    ];
}

foreach ($rdvs as $r) {
    $events[] = [
        'id'    => 'rdv-' . $r['id_rdv'],
        'title' => 'Dr ' . $r['med_prenom'] . ' ' . $r['med_nom'] . ($r['specialite'] ? ' (' . $r['specialite'] . ')' : ''),
        'start' => $r['date_rdv'],
        'color' => '#3B82F6', 
        'extendedProps' => ['type' => 'medical', 'statut' => $r['statut']]
    ];
}

foreach ($evenements as $e) {
    $events[] = [
        'id'    => 'ev-' . $e['id'],
        'title' => $e['titre'] . ($e['lieu'] ? ' @ ' . $e['lieu'] : ''),
        'start' => $e['date_debut'],
        'color' => '#10B981', 
        'extendedProps' => ['type' => 'evenement']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Planning – Silver Happy</title>

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>

    <!-- FullCalendar -->
    <link  href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <!-- Localisation française -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { corail: '#E37A55', sable: '#F4EDDE', 'peche-pale': '#FDF0EB' },
                    fontFamily: { sans: ['DM Sans', 'sans-serif'], title: ['Quicksand', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'DM Sans', sans-serif; font-size: 18px; }
        /* Surcharge FullCalendar pour Senior (polices grandes) */
        .fc { font-size: 1rem; }
        .fc .fc-button { font-size: 1rem; padding: .5em 1em; }
        .fc .fc-toolbar-title { font-size: 1.4rem; font-family: 'Quicksand', sans-serif; font-weight: 600; color: #E37A55; }
        .fc .fc-event { font-size: .9rem; border-radius: 8px; border: none; padding: 2px 6px; cursor: pointer; }
        .fc .fc-daygrid-day-number { font-size: 1rem; font-weight: 500; }
        .fc .fc-col-header-cell-cushion { font-size: 1rem; font-weight: 600; color: #475569; }
        .fc .fc-button-primary { background-color: #E37A55; border-color: #E37A55; }
        .fc .fc-button-primary:hover { background-color: #c96642; border-color: #c96642; }
        .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #c96642; border-color: #c96642; }
        .fc .fc-today-button { background-color: #64748b; border-color: #64748b; }
        .fc .fc-today-button:hover { background-color: #475569; border-color: #475569; }
        .fc .fc-daygrid-day.fc-day-today { background: #FDF0EB; }
        /* Tooltip custom */
        #tooltip {
            display: none; position: fixed; z-index: 9999;
            background: white; border: 1px solid #e2e8f0;
            border-radius: 12px; padding: 12px 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            max-width: 260px; font-size: 15px; line-height: 1.6;
        }
    </style>
</head>
<body class="bg-sable font-sans text-slate-800">

<?php include 'accessibilite.php'; ?>

<!-- Nav -->
<nav class="bg-white/90 backdrop-blur-md shadow-sm px-6 py-4 flex justify-between items-center sticky top-0 z-40">
    <span class="text-xl font-bold text-corail font-title">Silver Happy</span>
    <div class="flex items-center gap-6 text-sm font-medium">
        <a href="dashboardS.php" class="text-slate-600 hover:text-corail transition-colors">
            <i class="fa-solid fa-house mr-1"></i>Tableau de bord
        </a>
        <a href="services.php" class="text-slate-600 hover:text-corail transition-colors"data-i18n="nav_services">Services</a>
        <a href="boutique.php" class="text-slate-600 hover:text-corail transition-colors"data-i18n="nav_boutique">Boutique</a>
        <a href="logout.php" class="text-red-400 hover:text-red-600 transition-colors"data-i18n="nav_logout">Déconnexion</a>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">

    <!-- En-tête -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-3xl font-title font-bold text-corail">Mon Planning</h1>
            <p class="text-slate-500 mt-1">Bonjour <?= htmlspecialchars($prenom) ?>, voici tous vos rendez-vous</p>
        </div>
        <div class="flex gap-3 flex-wrap text-sm">
            <span class="flex items-center gap-2 bg-white border border-slate-200 rounded-full px-4 py-2">
                <span class="w-3 h-3 rounded-full bg-[#E37A55] inline-block"></span> Service
            </span>
            <span class="flex items-center gap-2 bg-white border border-slate-200 rounded-full px-4 py-2">
                <span class="w-3 h-3 rounded-full bg-[#3B82F6] inline-block"></span> Médical
            </span>
            <span class="flex items-center gap-2 bg-white border border-slate-200 rounded-full px-4 py-2">
                <span class="w-3 h-3 rounded-full bg-[#10B981] inline-block"></span> Événement
            </span>
        </div>
    </div>

    <!-- Calendrier -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-8">
        <div id="calendar"></div>
    </div>

    <!-- Liste des prochains RDV -->
    <div class="bg-white rounded-3xl shadow-sm p-6">
        <h2 class="text-xl font-title font-bold text-slate-700 mb-4">
            <i class="fa-solid fa-list-check text-corail mr-2"></i><span data-i18n="planning_rdv">Prochains rendez-vous</span>
        </h2>
        <?php
        // Fusionne et trie tous les événements à venir
        $tous = [];
        foreach ($reservations as $r) {
            $tous[] = [
                'date'         => $r['date_reservation'],
                'label'        => ($r['titre'] ?? 'Service') . ' avec ' . $r['p_prenom'] . ' ' . $r['p_nom'],
                'type'         => 'service',
                'statut'       => $r['statut'],
                'id_devis'     => $r['id_devis'] ?? null,
                'montant_ttc'  => $r['montant_ttc'] ?? null,
                'devis_statut' => $r['devis_statut'] ?? null,
                'id_reservation' => $r['id_reservation'],
            ];
        }
        foreach ($rdvs as $r) {
            $tous[] = ['date' => $r['date_rdv'], 'label' => 'Dr ' . $r['med_prenom'] . ' ' . $r['med_nom'] . ($r['specialite'] ? ' (' . $r['specialite'] . ')' : ''), 'type' => 'medical', 'statut' => 'planifié'];
        }
        foreach ($evenements as $e) {
            $tous[] = ['date' => $e['date_debut'], 'label' => $e['titre'] . ($e['lieu'] ? ' — ' . $e['lieu'] : ''), 'type' => 'evenement', 'statut' => 'inscrit'];
        }
        usort($tous, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
        $couleurs = ['service' => 'bg-orange-50 border-orange-200 text-orange-700', 'medical' => 'bg-blue-50 border-blue-200 text-blue-700', 'evenement' => 'bg-emerald-50 border-emerald-200 text-emerald-700'];
        $icones   = ['service' => 'fa-hands-helping', 'medical' => 'fa-stethoscope', 'evenement' => 'fa-star'];
        ?>
        <?php if (empty($tous)): ?>
            <p class="text-slate-400 italic text-center py-6">Aucun rendez-vous à venir.<br>
                <a href="services.php" class="text-corail font-bold not-italic hover:underline">Réserver un service →</a>
            </p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($tous, 0, 8) as $ev): ?>
                <div class="flex items-center gap-4 p-4 rounded-2xl border <?= $couleurs[$ev['type']] ?>">
                    <i class="fa-solid <?= $icones[$ev['type']] ?> text-xl w-6 text-center flex-shrink-0"></i>
                    <div class="flex-1">
                        <p class="font-bold"><?= htmlspecialchars($ev['label']) ?></p>
                        <p class="text-sm opacity-80 mt-0.5"><?= date('l d F Y à H:i', strtotime($ev['date'])) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                        <?php if (($ev['devis_statut'] ?? null) === 'envoye'): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-right">
                                <p class="text-xs font-bold text-blue-700 mb-2">Devis reçu : <?= number_format($ev['montant_ttc'], 2, ',', ' ') ?> €</p>
                                <div class="flex gap-2 justify-end mb-2">
                                    <a href="voir_devis.php?id=<?= $ev['id_devis'] ?>" target="_blank"
                                    class="text-xs bg-white border border-slate-300 text-slate-600 px-3 py-1 rounded-lg font-bold hover:bg-slate-100">
                                        Consulter
                                    </a>
                                </div>
                                <div class="flex gap-2">
                                    <a href="act_devis.php?id=<?= $ev['id_devis'] ?>&action=accepter"
                                    onclick="return confirm('Accepter ce devis ?')"
                                    class="text-xs bg-emerald-500 text-white px-3 py-1 rounded-lg font-bold hover:bg-emerald-600">Accepter</a>
                                    <a href="act_devis.php?id=<?= $ev['id_devis'] ?>&action=refuser"
                                    onclick="return confirm('Refuser ce devis ?')"
                                    class="text-xs bg-red-100 text-red-600 px-3 py-1 rounded-lg font-bold hover:bg-red-200">Refuser</a>
                                </div>
                            </div>
                        <?php elseif (($ev['devis_statut'] ?? null) === 'accepte'): ?>
                            <a href="payer_devis.php?id=<?= $ev['id_devis'] ?>"
                            class="text-xs bg-orange-500 text-white px-4 py-2 rounded-lg font-bold hover:bg-orange-600">
                                Payer <?= number_format($ev['montant_ttc'], 2, ',', ' ') ?> €
                            </a>
                        <?php else: ?>
                            <span class="text-xs font-bold uppercase px-3 py-1 rounded-full bg-white/60 border border-current">
                                <?= htmlspecialchars($ev['statut']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Tooltip -->
<div id="tooltip"></div>

<script>
const events = <?= json_encode($events, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const tooltip    = document.getElementById('tooltip');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale:         'fr',
        initialView:    'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', list: 'Liste' },
        events:         events,
        height:         'auto',
        eventDisplay:   'block',

        eventMouseEnter: function (info) {
            const p = info.event.extendedProps;
            const typeLabel = { service: 'Service', medical: 'RDV Médical', evenement: 'Événement' };
            tooltip.innerHTML = `
                <strong style="font-size:16px">${info.event.title}</strong><br>
                <span style="color:#64748b">${typeLabel[p.type] ?? ''}</span><br>
                <span style="color:#64748b">${info.event.start ? info.event.start.toLocaleDateString('fr-FR', {weekday:'long',day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'}) : ''}</span>
            `;
            tooltip.style.display = 'block';
        },
        eventMouseMove: function (info) {
            tooltip.style.top  = (info.jsEvent.clientY + 14) + 'px';
            tooltip.style.left = (info.jsEvent.clientX + 14) + 'px';
        },
        eventMouseLeave: function () {
            tooltip.style.display = 'none';
        },

        // Clic sur un événement → redirige vers le dashboard
        eventClick: function (info) {
            tooltip.style.display = 'none';
            window.location.href = 'dashboardS.php#planning';
        },

        noEventsContent: "Aucun rendez-vous ce mois-ci"
    });

    calendar.render();
});
</script>
<script src="/lang/i18n.js"></script>
</body>
</html>