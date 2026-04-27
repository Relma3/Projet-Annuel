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

    <div id="section-prestataires" class="section">
      <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-700 flex justify-between items-center">
          <h2 class="font-bold text-white">Liste des prestataires</h2>
          <button onclick="chargerPrestataires()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
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
            <tbody id="table-pres" class="divide-y divide-slate-700">
              <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">Cliquez sur Actualiser pour charger</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

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
};

function showSection(name) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
  document.getElementById('section-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
  document.getElementById('page-title').textContent = titles[name][0];
  document.getElementById('page-sub').textContent = titles[name][1];
  if (name === 'overview') chargerStats();
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
    ]);
    const ns = Array.isArray(s) ? s.length : 0;
    const np = Array.isArray(p) ? p.length : 0;
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
    const tbody = document.getElementById('table-pres');
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
    //malikat

// NAVIGATION
function showSection(name){
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById('section-' + name).classList.add('active');

    if(name === 'articles') chargerArticles();
}

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

    await fetch('/api/admin/articles.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({nom, prix, description})
    });

    document.getElementById('modal').classList.add('hidden');
    chargerArticles();
}

</script>

</body>
</html>