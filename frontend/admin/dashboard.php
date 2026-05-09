<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — Silver Happy</title>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        'orange-corail': '#FF885B',
        'peche-pastel': '#FFD9CA',
      },
      fontFamily: {
        'sans': ['Roboto', 'sans-serif'],
        'title': ['Quicksand', 'sans-serif'],
      }
    }
  }
}
</script>

<style>
  .sidebar-link { transition: all 0.2s; }
  .sidebar-link.active { background: rgba(255,136,91,0.15); color: #FF885B; border-left: 3px solid #FF885B; }
  .sidebar-link:not(.active):hover { background: rgba(255,255,255,0.05); }
  .section { display: none; }
  .section.active { display: block; }
  .stat-card { transition: transform 0.2s; }
  .stat-card:hover { transform: translateY(-2px); }
</style>
</head>

<body class="bg-slate-900 text-white font-sans min-h-screen flex">

<aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col fixed h-full z-20">
  <div class="p-6 border-b border-slate-700">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 bg-orange-corail rounded-xl flex items-center justify-center">
        <i class="fa-solid fa-shield-halved text-white text-sm"></i>
      </div>
      <div>
        <p class="font-title font-bold text-white text-sm leading-none">Silver Happy</p>
        <p class="text-slate-400 text-xs mt-0.5">Administration</p>
      </div>
    </div>
  </div>

  <nav class="flex-1 p-4 space-y-1">
    <p class="text-slate-500 text-xs uppercase tracking-widest font-bold mb-3 px-3">Tableau de bord</p>

    <a onclick="showSection('overview')" class="sidebar-link active flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium">
      <i class="fa-solid fa-chart-line w-4 text-center"></i> Vue d'ensemble
    </a>

    <p class="text-slate-500 text-xs uppercase tracking-widest font-bold mt-5 mb-3 px-3">Gestion</p>

    <a onclick="showSection('seniors')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-users w-4 text-center"></i> Seniors
      <span id="badge-seniors" class="ml-auto bg-orange-corail text-white text-xs px-2 py-0.5 rounded-full hidden"></span>
    </a>

    <a onclick="showSection('prestataires')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-briefcase w-4 text-center"></i> Prestataires
      <span id="badge-pres" class="ml-auto bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full hidden"></span>
    </a>

    <a onclick="showSection('categories')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-tag w-4 text-center"></i> Catégories
    </a>

    <a onclick="showSection('evenements')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-calendar-days w-4 text-center"></i> Événements
    </a>

    <a onclick="showSection('articles')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-box w-4 text-center"></i> Articles
    </a>

    <a onclick="showSection('documents')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-file-alt w-4 text-center"></i> Documents
    </a>

    <a href="/frontend/admin/admin_conseils.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300">
      <i class="fa-solid fa-lightbulb w-4 text-center"></i> Conseils
    </a>

    <a onclick="showSection('finances')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-euro-sign w-4 text-center"></i> Finances
    </a>

     <a onclick="showSection('logs')" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer text-sm font-medium text-slate-300">
      <i class="fa-solid fa-list w-4 text-center"></i> Logs
    </a>

  </nav>

  <div class="p-4 border-t border-slate-700">
    <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white text-sm transition-colors">
      <i class="fa-solid fa-arrow-left w-4 text-center"></i> Retour au site
    </a>
    <button onclick="deconnexion()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-red-400 text-sm transition-colors mt-1">
      <i class="fa-solid fa-power-off w-4 text-center"></i> Déconnexion
    </button>
  </div>
</aside>

<main class="ml-64 flex-1 min-h-screen">

  <header class="bg-slate-800/50 backdrop-blur border-b border-slate-700 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
    <div>
      <h1 id="page-title" class="font-title font-bold text-lg text-white">Vue d'ensemble</h1>
      <p id="page-sub" class="text-slate-400 text-xs">Bienvenue dans le panneau d'administration Silver Happy</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 bg-orange-corail rounded-full flex items-center justify-center text-white text-sm font-bold">A</div>
      <span class="text-slate-300 text-sm font-medium">Admin</span>
    </div>
  </header>

  <div class="p-8">

    <div id="section-overview" class="section active">

      <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 bg-orange-corail/15 rounded-xl flex items-center justify-center">
              <i class="fa-solid fa-users text-orange-corail"></i>
            </div>
            <span class="text-green-400 text-xs font-bold bg-green-400/10 px-2 py-1 rounded-full">Actif</span>
          </div>
          <p class="text-3xl font-bold text-white mb-1" id="stat-seniors">—</p>
          <p class="text-slate-400 text-sm">Seniors inscrits</p>
        </div>

        <div class="stat-card bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 bg-blue-500/15 rounded-xl flex items-center justify-center">
              <i class="fa-solid fa-briefcase text-blue-400"></i>
            </div>
          </div>
         <p class="text-3xl font-bold text-white mb-1" id="stat-pres">—</p>
<p class="text-slate-400 text-sm">Prestataires</p>

<p class="text-xs mt-2">
  <span class="text-green-400">
    ✔ <span id="presta-valides">0</span> validés
  </span><br>

  <span class="text-orange-400">
    ⏳ <span id="presta-attente">0</span> en attente
  </span>
</p>
        </div>

        <div class="stat-card bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 bg-purple-500/15 rounded-xl flex items-center justify-center">
              <i class="fa-solid fa-tag text-purple-400"></i>
            </div>
          </div>
          <p class="text-3xl font-bold text-white mb-1" id="stat-cats">—</p>
          <p class="text-slate-400 text-sm">Catégories</p>
        </div>

        <div class="stat-card bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 bg-emerald-500/15 rounded-xl flex items-center justify-center">
              <i class="fa-solid fa-calendar text-emerald-400"></i>
            </div>
          </div>
          <p class="text-3xl font-bold text-white mb-1" id="stat-events">—</p>
          <p class="text-slate-400 text-sm">Événements</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <h3 class="font-bold text-white mb-4">Répartition des utilisateurs</h3>
          <div style="height:220px; position:relative;">
            <canvas id="chartUsers"></canvas>
          </div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <h3 class="font-bold text-white mb-4">Activité de la plateforme</h3>
          <div style="height:220px; position:relative;">
            <canvas id="chartActivity"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div id="section-seniors" class="section">
      <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-700 flex justify-between items-center">
          <h2 class="font-bold text-white">Liste des seniors</h2>
          <button onclick="chargerSeniors()" class="bg-orange-corail hover:brightness-110 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
            <i class="fa-solid fa-rotate-right mr-2"></i>Actualiser
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-700/50 text-slate-400 text-xs uppercase tracking-wider">
                <th class="text-left px-6 py-3">ID</th>
                <th class="text-left px-6 py-3">Email</th>
                <th class="text-left px-6 py-3">Type</th>
                <th class="text-left px-6 py-3">Inscrit le</th>
              </tr>
            </thead>
            <tbody id="table-seniors" class="divide-y divide-slate-700">
              <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">Cliquez sur Actualiser pour charger</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

   <!-- Section : Gestion des prestataires -->
   <div id="section-prestataires" class="section">

  <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">

    <div class="p-6 border-b border-slate-700 flex justify-between items-center">
      <h2 class="font-bold text-white">Gestion des prestataires</h2>

      <button onclick="chargerPrestataires()" class="bg-blue-500 px-4 py-2 rounded-xl">
        Actualiser
      </button>
    </div>

    <table class="w-full">
      <thead>
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody id="table-prestataires"></tbody>
    </table>

  </div>

</div>
<!-- /Section prestataires -->
<!-- Section : Validation des documents -->
 <div id="section-documents" class="section">

  <div class="bg-slate-800 rounded-2xl p-6">

    <h2>Validation documents</h2>

    <button onclick="chargerDocuments()" class="bg-blue-500 px-4 py-2 rounded">
      Actualiser
    </button>

    <table class="w-full mt-4">
      <thead>
        <tr>
          <th>Email</th>
          <th>Type</th>
          <th>Fichier</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody id="table-documents"></tbody>
    </table>

  </div>
</div>
<!-- /Section validation documents -->

<!-- Section : Catégories de prestations -->

    <div id="section-categories" class="section">
      <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
          <div class="p-6 border-b border-slate-700 flex justify-between items-center">
            <h2 class="font-bold text-white">Catégories de prestations</h2>
            <button onclick="chargerCategories()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
              <i class="fa-solid fa-rotate-right mr-2"></i>Actualiser
            </button>
          </div>
          <ul id="liste-categories" class="divide-y divide-slate-700 p-0"></ul>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <h3 class="font-bold text-white mb-4">Ajouter une catégorie</h3>
          <div class="space-y-4">
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Nom</label>
              <input id="cat-nom" placeholder="ex: Santé & Bien-être"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-purple-500 outline-none transition-colors">
            </div>
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Description</label>
              <textarea id="cat-desc" placeholder="Description de la catégorie..." rows="3"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-purple-500 outline-none transition-colors resize-none"></textarea>
            </div>
            <button onclick="ajouterCategorie()"
              class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2.5 rounded-xl font-medium text-sm transition-all">
              <i class="fa-solid fa-plus mr-2"></i>Ajouter
            </button>
          </div>
        </div>
      </div>
    </div>
<!-- /Section catégories -->

<!-- Section : Finances et statistiques -->

 <div id="section-finances" class="section">

<h2 class="text-xl font-bold mb-4">Finances</h2>

<div class="grid grid-cols-4 gap-6 mb-6">

<div class="bg-slate-800 p-4 rounded-xl">
  CA : <?= number_format($stats['ca_total'],2) ?> €
</div>

<div class="bg-slate-800 p-4 rounded-xl">
  Commissions : <?= number_format($stats['commissions'],2) ?> €
</div>

<div class="bg-slate-800 p-4 rounded-xl">
  Seniors actifs : <?= $stats['seniors'] ?>
</div>

<div class="bg-slate-800 p-4 rounded-xl">
  Prestataires actifs : <?= $stats['prestataires'] ?>
</div>

</div>

<h3 class="mb-3 font-bold">Paiements récents</h3>

<table class="w-full">
<tr>
  <th>Montant</th>
  <th>Payeur</th>
  <th>Date</th>
</tr>

<?php foreach ($stats['paiements'] as $p): ?>
<tr>
  <td><?= $p['montant_cents']/100 ?> €</td>
  <td><?= $p['nom_payeur'] ?></td>
  <td><?= $p['date_paiement'] ?></td>
</tr>
<?php endforeach; ?>

</table>

</div>

<!-- /Section finances -->

    <div id="section-evenements" class="section">
      <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
          <div class="p-6 border-b border-slate-700 flex justify-between items-center">
            <h2 class="font-bold text-white">Événements</h2>
            <button onclick="chargerEvenements()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
              <i class="fa-solid fa-rotate-right mr-2"></i>Actualiser
            </button>
          </div>
          <ul id="liste-evenements" class="divide-y divide-slate-700 p-0"></ul>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
          <h3 class="font-bold text-white mb-4">Ajouter un événement</h3>
          <div class="space-y-4">
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Titre</label>
              <input id="ev-titre" placeholder="ex: Atelier Mémoire"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-emerald-500 outline-none transition-colors">
            </div>
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Date</label>
              <input id="ev-date" type="date"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-emerald-500 outline-none transition-colors">
            </div>
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Lieu</label>
              <input id="ev-lieu" placeholder="ex: Paris 11ème"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-emerald-500 outline-none transition-colors">
            </div>
            <div>
              <label class="text-slate-400 text-xs font-bold block mb-1">Nombre de places</label>
              <input id="ev-places" type="number" placeholder="20"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:border-emerald-500 outline-none transition-colors">
            </div>
            <button onclick="ajouterEvenement()"
              class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-medium text-sm transition-all">
              <i class="fa-solid fa-plus mr-2"></i>Créer l'événement
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Section : Gestion des articles -->
  <div id="section-articles" class="section">
   <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">

    <div class="p-6 border-b border-slate-700 flex justify-between items-center">
      <h2 class="font-bold text-white">Gestion des articles</h2>

      <button onclick="openModal()" class="bg-orange-corail px-4 py-2 rounded-xl">
         Ajouter
      </button>
    </div>

    <table class="w-full">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Prix</th>
          <th>Dispo</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody id="table-articles"></tbody>
    </table>

  </div>
</div>
<!-- /Section articles -->

<!-- Section : Logs système -->
 <div id="section-logs" class="section">

  <div class="bg-slate-800 p-6 rounded-2xl">

    <h2 class="text-white font-bold mb-4">Logs système</h2>

    <button onclick="chargerLogs()" class="bg-blue-500 px-4 py-2 rounded mb-4">
      Actualiser
    </button>

    <table class="w-full">
      <thead>
        <tr>
          <th>Email</th>
          <th>Action</th>
          <th>IP</th>
          <th>Date</th>
        </tr>
      </thead>

      <tbody id="table-logs"></tbody>
    </table>

  </div>

</div>
<!-- /Section logs -->

  </div>
</main>

<div id="toast" class="fixed bottom-6 right-6 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium hidden z-50 transition-all">
  <i class="fa-solid fa-check mr-2"></i><span id="toast-msg"></span>
</div>

<script src="admin.js"></script>
<script>
const titles = {
  overview: ['Vue d\'ensemble', 'Bienvenue dans le panneau d\'administration Silver Happy'],
  seniors: ['Seniors', 'Gestion des adhérents inscrits sur la plateforme'],
  prestataires: ['Prestataires', 'Gestion des prestataires partenaires'],
  categories: ['Catégories', 'Gestion des catégories de prestations'],
  evenements: ['Événements', 'Gestion des événements Silver Happy'],
  articles: ['Articles', 'Gestion des articles'],
  documents: ['Documents', 'Validation des documents des prestataires']
};

function showSection(name) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
  document.getElementById('section-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
  document.getElementById('page-title').textContent = titles[name][0];
  document.getElementById('page-sub').textContent = titles[name][1];
  if (name === 'overview') chargerStats();
  if (name === 'articles') chargerArticles();
  if (name === 'prestataires') chargerPrestataires();
  if (name === 'documents') chargerDocuments();
  if (name === 'logs') chargerLogs();
  if (name === 'finances') chargerFinances();
  
}

function deconnexion() {
  localStorage.removeItem('sh_admin_token');
  window.location.href = '/connexion_admin.php';
}

function toast(msg, color = 'bg-green-500') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = `fixed bottom-6 right-6 ${color} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium z-50`;
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3000);
}

async function chargerStats() {
  try {
    const [s, p, c, e] = await Promise.all([
      fetch('/api/admin.php?action=seniors', { headers: { Authorization: 'Bearer ' + token() } }).then(r => r.json()),
      fetch('/api/admin.php?action=prestataires', { headers: { Authorization: 'Bearer ' + token() } }).then(r => r.json()),
      fetch('/api/admin.php?action=categories', { headers: { Authorization: 'Bearer ' + token() } }).then(r => r.json()),
      fetch('/api/admin.php?action=evenements', { headers: { Authorization: 'Bearer ' + token() } }).then(r => r.json()),
      fetch('/api/admin.php?action=articles', { headers: { Authorization: 'Bearer ' + token() } }).then(r => r.json())

    ]);
    const ns = Array.isArray(s) ? s.length : 0;
    const np = Array.isArray(p) ? p.length : 0;
    const valides = p.filter(x => x.est_actif == 1).length;
    const attente = p.filter(x => x.est_actif == 0).length;
  
    document.getElementById('presta-valides').textContent = valides;
    document.getElementById('presta-attente').textContent = attente;
    const nc = Array.isArray(c) ? c.length : 0;
    const ne = Array.isArray(e) ? e.length : 0;

    document.getElementById('stat-seniors').textContent = ns;
    document.getElementById('stat-pres').textContent = np;
    document.getElementById('stat-cats').textContent = nc;
    document.getElementById('stat-events').textContent = ne;

    if (ns > 0) { document.getElementById('badge-seniors').textContent = ns; document.getElementById('badge-seniors').classList.remove('hidden'); }
    if (np > 0) { document.getElementById('badge-pres').textContent = np; document.getElementById('badge-pres').classList.remove('hidden'); }

    const ctx1 = document.getElementById('chartUsers');
    if (ctx1._chart) ctx1._chart.destroy();
    ctx1._chart = new Chart(ctx1, {
      type: 'doughnut',
      data: {
        labels: ['Seniors', 'Prestataires'],
        datasets: [{ data: [ns, np], backgroundColor: ['#FF885B', '#3b82f6'], borderWidth: 0, hoverOffset: 4 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '70%'
      }
    });

    const ctx2 = document.getElementById('chartActivity');
    if (ctx2._chart) ctx2._chart.destroy();
    ctx2._chart = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: ['Seniors', 'Prestataires', 'Catégories', 'Événements'],
        datasets: [{ data: [ns, np, nc, ne], backgroundColor: ['#FF885B', '#3b82f6', '#a855f7', '#10b981'], borderRadius: 8, borderWidth: 0 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
          y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', stepSize: 1 }, beginAtZero: true }
        }
      }
    });
  } catch(e) { console.error(e); }
}

const _origSeniors = chargerSeniors;
chargerSeniors = async function() {
  try {
    const res = await fetch('/api/admin.php?action=seniors', { headers: { Authorization: 'Bearer ' + token() } });
    const data = await res.json();
    const tbody = document.getElementById('table-seniors');
    if (!Array.isArray(data)) { tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-red-400">Erreur de chargement</td></tr>'; return; }
    tbody.innerHTML = data.map(s => `
      <tr class="hover:bg-slate-700/30 transition-colors">
        <td class="px-6 py-4 text-slate-400 text-sm">#${s.id_utilisateur}</td>
        <td class="px-6 py-4 text-white text-sm">${s.email}</td>
        <td class="px-6 py-4"><span class="bg-orange-corail/15 text-orange-corail text-xs px-2 py-1 rounded-full font-medium">Senior</span></td>
        <td class="px-6 py-4 text-slate-400 text-sm">${s.created_at ? new Date(s.created_at).toLocaleDateString('fr') : '—'}</td>
      </tr>`).join('');
    toast(data.length + ' senior(s) chargé(s)');
  } catch(e) { console.error(e); }
};

const _origPres = chargerPrestataires;
chargerPrestataires = async function() {
  try {
    const res = await fetch('/api/admin.php?action=prestataires', { headers: { Authorization: 'Bearer ' + token() } });
    const data = await res.json();
    const valides = data.filter(p => p.est_actif == 1).length;
   const attente = data.filter(p => p.est_actif == 0).length;
    const tbody = document.getElementById('table-prestataires');
    if (!Array.isArray(data)) { tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-red-400">Erreur de chargement</td></tr>'; return; }
    tbody.innerHTML = data.map(p => `
      <tr class="hover:bg-slate-700/30 transition-colors">
        <td class="px-6 py-4 text-slate-400 text-sm">#${p.id_utilisateur}</td>
        <td class="px-6 py-4 text-white text-sm">${p.email}</td>
        <td class="px-6 py-4"><span class="bg-blue-500/15 text-blue-400 text-xs px-2 py-1 rounded-full font-medium">Prestataire</span></td>
        <td class="px-6 py-4 text-slate-400 text-sm">${p.created_at ? new Date(p.created_at).toLocaleDateString('fr') : '—'}</td>
      </tr>`).join('');
    toast(data.length + ' prestataire(s) chargé(s)', 'bg-blue-500');
  } catch(e) { console.error(e); }
};

const _origCats = chargerCategories;
chargerCategories = async function() {
  try {
    const res = await fetch('/api/admin.php?action=categories', { headers: { Authorization: 'Bearer ' + token() } });
    const data = await res.json();
    const ul = document.getElementById('liste-categories');
    if (!Array.isArray(data)) { ul.innerHTML = '<li class="px-6 py-4 text-red-400 text-sm">Erreur</li>'; return; }
    ul.innerHTML = data.length === 0 ? '<li class="px-6 py-8 text-center text-slate-400 text-sm">Aucune catégorie</li>' :
      data.map(c => `
        <li class="flex justify-between items-center px-6 py-4 hover:bg-slate-700/30 transition-colors">
          <div>
            <p class="text-white text-sm font-medium">${c.nom}</p>
            ${c.description ? `<p class="text-slate-400 text-xs mt-0.5">${c.description}</p>` : ''}
          </div>
          <button onclick="supprimerCategorie(${c.id})" class="text-red-400 hover:text-red-300 hover:bg-red-400/10 p-2 rounded-lg transition-all">
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        </li>`).join('');
  } catch(e) { console.error(e); }
};

const _origEvs = chargerEvenements;
chargerEvenements = async function() {
  try {
    const res = await fetch('/api/admin.php?action=evenements', { headers: { Authorization: 'Bearer ' + token() } });
    const data = await res.json();
    const ul = document.getElementById('liste-evenements');
    if (!Array.isArray(data)) { ul.innerHTML = '<li class="px-6 py-4 text-red-400 text-sm">Erreur</li>'; return; }
    ul.innerHTML = data.length === 0 ? '<li class="px-6 py-8 text-center text-slate-400 text-sm">Aucun événement</li>' :
      data.map(e => `
        <li class="flex justify-between items-center px-6 py-4 hover:bg-slate-700/30 transition-colors">
          <div>
            <p class="text-white text-sm font-medium">${e.titre}</p>
            <p class="text-slate-400 text-xs mt-0.5">${e.lieu || ''} ${e.date_debut ? '— ' + new Date(e.date_debut).toLocaleDateString('fr') : ''} ${e.nombre_places ? '· ' + e.nombre_places + ' places' : ''}</p>
          </div>
          <button onclick="supprimerEvenement(${e.id})" class="text-red-400 hover:text-red-300 hover:bg-red-400/10 p-2 rounded-lg transition-all">
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
        </li>`).join('');
  } catch(e) { console.error(e); }
};

window.addEventListener('load', () => { chargerStats(); chargerCategories(); chargerEvenements(); });
</script>
<script>

// NAVIGATION
// CHARGER ARTICLES
async function chargerArticles(){
    const res = await fetch('/api/admin/articles.php');
    const data = await res.json();

    const table = document.getElementById('table-articles');

    table.innerHTML = data.map(a => `
        <tr>
            <td>${a.id_article}</td>
            <td>${a.nom}</td>
            <td>${a.prix}€</td>

            <td>
                <button onclick="toggle(${a.id_article})">
                    ${a.disponible ? '🟢' : '🔴'}
                </button>
            </td>

            <td>
                <button onclick="supprimer(${a.id_article})">❌</button>
            </td>
        </tr>
    `).join('');
}

// TOGGLE
async function toggle(id){
    await fetch('/api/admin/articles.php', {
        method:'PATCH',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id_article:id})
    });

    chargerArticles();
}

// DELETE
async function supprimer(id){
    if(!confirm("Supprimer ?")) return;

    await fetch('/api/admin/articles.php', {
        method:'DELETE',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id_article:id})
    });

    chargerArticles();
}

// MODAL
function openModal(){
    document.getElementById('modal').classList.remove('hidden');
}

// ADD
async function ajouterArticle(){

    const nom = document.getElementById('nom').value;
    const prix = document.getElementById('prix').value;
    const description = document.getElementById('desc').value;

    // ⚠️ sécurité
    if(!nom || !description){
        alert("Nom et description obligatoires");
        return;
    }

    try {
        const res = await fetch('/api/admin/articles.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({
                nom,
                prix,
                description
            })
        });

        const data = await res.json();

        if(data.error){
            alert(data.error);
            return;
        }

        alert("Article ajouté ");

        document.getElementById('modal').classList.add('hidden');

        chargerArticles();

    } catch(e){
        console.error(e);
        alert("Erreur serveur");
    }
}
async function chargerPrestataires() {
  try {
    const res = await fetch('/api/admin/prestataires.php');
    const data = await res.json();

    const table = document.getElementById('table-prestataires');

    if (!Array.isArray(data)) {
      table.innerHTML = '<tr><td colspan="4">Erreur</td></tr>';
      return;
    }

    table.innerHTML = data.map(p => `
      <tr class="border-b border-slate-700">

        <td class="p-3">#${p.id_utilisateur}</td>
        <td class="p-3">${p.email}</td>
        <td class="p-3">
          ${
            p.est_actif == 1
              ? '<span class="text-green-400">Validé</span>'
              : '<span class="text-orange-400">En attente</span>'
          }
        </td>

        <td class="p-3 flex gap-2">

          ${
            p.est_actif == 0
              ? `<button onclick="validerPrestataire(${p.id_utilisateur}, 1)"
                  class="bg-green-500 px-3 py-1 rounded text-xs">
                  Valider
                </button>`
              : ''
          }

          ${
            p.est_actif == 1
              ? `<button onclick="validerPrestataire(${p.id_utilisateur}, 0)"
                  class="bg-red-500 px-3 py-1 rounded text-xs">
                  Désactiver
                </button>`
              : ''
          }

          <button onclick="voirDocs(${p.id_utilisateur})"
              class="text-blue-400 hover:underline text-xs">
              Documents
          </button>

        </td>

      </tr>
    `).join('');

  } catch (e) {
    console.error(e);
  }
}
async function validerPrestataire(id, etat){

    await fetch('/api/admin/prestataires.php', {
        method:'PATCH',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({
            id_utilisateur:id,
            est_actif:etat
        })
    });

    chargerPrestataires();
}
async function chargerDocuments() {

  const res = await fetch('/api/admin/documents.php', {
  headers: { Authorization: 'Bearer ' + token() }
});
  const data = await res.json();

  const table = document.getElementById('table-documents');

  table.innerHTML = data.map(d => `
    <tr>
      <td>${d.email}</td>
      <td>${d.type_document}</td>

      <td>
        <a href="${d.chemin_fichier}" target="_blank" class="text-blue-400">
  Voir
</a>
      </td>

      <td>
  ${
    d.statut === 'valide'
      ? '<span class="text-green-400">Validé</span>'
      : d.statut === 'refuse'
      ? '<span class="text-red-400">Refusé</span>'
      : '<span class="text-orange-400">En attente</span>'
  }
</td>

      <td>
        <button onclick="validerDoc(${d.id_document}, 'valide')">oui</button>
        <button onclick="validerDoc(${d.id_document}, 'refuse')">non</button>
      </td>
    </tr>
  `).join('');
}
async function validerDoc(id, statut){

  await fetch('/api/admin/documents.php', {
    method: 'PATCH',
    headers:{
      'Content-Type':'application/json',
      Authorization: 'Bearer ' + token()
    },
    body: JSON.stringify({
      id_document: id,
      statut: statut
    })
  });

  chargerDocuments();
}
async function voirDocs(id_prestataire) {

  // switch vers section documents
  showSection('documents');

  // charger documents filtrés
  const res = await fetch('/api/admin/documents.php?id_prestataire=' + id_prestataire, {
    headers: { Authorization: 'Bearer ' + token() }
  });

  const data = await res.json();

  const table = document.getElementById('table-documents');

  table.innerHTML = data.map(d => `
    <tr>
      <td>${d.email}</td>
      <td>${d.type_document}</td>

      <td>
        <a href="/${d.chemin_fichier}" target="_blank" class="text-blue-400">
          Voir
        </a>
      </td>

      <td>
        ${
          d.statut === 'valide'
            ? '<span class="text-green-400">Validé</span>'
            : d.statut === 'refuse'
            ? '<span class="text-red-400">Refusé</span>'
            : '<span class="text-orange-400">En attente</span>'
        }
      </td>

      <td>
        <button onclick="validerDoc(${d.id_document}, 'valide')">oui</button>
        <button onclick="validerDoc(${d.id_document}, 'refuse')">non</button>
      </td>
    </tr>
  `).join('');
}

async function chargerLogs() {

  const res = await fetch('/api/admin/logs.php');
  const data = await res.json();

  const table = document.getElementById('table-logs');

  table.innerHTML = data.map(l => `
    <tr>
      <td>${l.email ?? '—'}</td>
      <td>${l.action}</td>
      <td>${l.ip}</td>
      <td>${new Date(l.date_action).toLocaleString()}</td>
    </tr>
  `).join('');
}

async function chargerFinances() {
  const res = await fetch('/api/admin.php?action=stats_financieres');
  const data = await res.json();

  document.getElementById('ca').textContent = data.ca_total + ' €';
  document.getElementById('commissions').textContent = data.commissions + ' €';
  document.getElementById('seniors-actifs').textContent = data.seniors;
  document.getElementById('prestataires-actifs').textContent = data.prestataires;

  const table = data.paiements.map(p => `
    <tr>
      <td>${p.montant_cents / 100} €</td>
      <td>${p.nom_payeur}</td>
      <td>${p.date_paiement}</td>
    </tr>
  `).join('');

  document.querySelector('#section-finances table').innerHTML += table;
}

</script>

<div id="modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">

  <div class="bg-slate-800 p-6 rounded-2xl w-96 border border-slate-700">

    <h2 class="text-white font-bold mb-4">Ajouter un article</h2>

    <input id="nom" class="w-full mb-2 p-2 bg-slate-700 rounded" placeholder="Nom">
    <input id="prix" class="w-full mb-2 p-2 bg-slate-700 rounded" placeholder="Prix">
    <textarea id="desc" class="w-full mb-2 p-2 bg-slate-700 rounded"></textarea>

    <button onclick="ajouterArticle()" class="bg-orange-corail px-4 py-2 rounded w-full">
      Ajouter
    </button>

  </div>
</div>

</body>
</html>
