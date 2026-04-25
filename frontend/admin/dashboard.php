<?php
session_start();

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header('Location: /connexion_admin.php');
    exit;

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — Silver Happy</title>

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://kit.fontawesome.com/168ebc7feb.js"></script>

<style>
.section { display:none; }
.section.active { display:block; }
.sidebar-link.active { background: rgba(255,136,91,0.15); color:#FF885B; border-left:3px solid #FF885B; }
</style>
</head>

<body class="bg-slate-900 text-white flex">

<!-- SIDEBAR -->
<aside class="w-64 bg-slate-800 p-4 min-h-screen">

<h2 class="text-xl font-bold mb-6">Silver Happy</h2>

<a onclick="showSection('overview')" class="sidebar-link block py-2 cursor-pointer">📊 Dashboard</a>
<a onclick="showSection('articles')" class="sidebar-link block py-2 cursor-pointer">🛒 Articles</a>

</aside>

<!-- MAIN -->
<main class="flex-1 p-6">

<!-- DASHBOARD -->
<div id="section-overview" class="section active">
<h1 class="text-2xl mb-4">Dashboard</h1>
</div>

<!-- ARTICLES -->
<div id="section-articles" class="section">

<div class="flex justify-between items-center mb-4">
<h1 class="text-xl">Gestion des articles</h1>

<button onclick="openModal()" class="bg-orange-500 px-4 py-2 rounded">
➕ Ajouter
</button>
</div>

<table class="w-full bg-slate-800">
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

</main>

<!-- MODAL -->
<div id="modal" class="hidden fixed inset-0 bg-black/60 flex justify-center items-center">

<div class="bg-slate-800 p-6 rounded w-96">

<h2 class="mb-4">Ajouter article</h2>

<input id="nom" placeholder="Nom" class="w-full mb-2 p-2 bg-slate-700">
<input id="prix" placeholder="Prix" class="w-full mb-2 p-2 bg-slate-700">
<textarea id="desc" class="w-full mb-2 p-2 bg-slate-700"></textarea>

<button onclick="ajouterArticle()" class="bg-green-500 px-4 py-2 w-full">
Ajouter
</button>

</div>
</div>

<script>

// NAV
function showSection(name){
document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
document.getElementById('section-'+name).classList.add('active');

if(name==='articles') chargerArticles();
}

// LOAD
async function chargerArticles(){
const res = await fetch('/api/admin/articles.php');
const data = await res.json();

const table = document.getElementById('table-articles');

table.innerHTML = data.map(a=>`
<tr>
<td>${a.id_article}</td>
<td>${a.nom}</td>
<td>${a.prix}€</td>

<td>
<button onclick="toggle(${a.id_article})">
${a.disponible ? '🟢':'🔴'}
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
await fetch('/api/admin/articles.php',{
method:'PATCH',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({id_article:id})
});
chargerArticles();
}

// DELETE
async function supprimer(id){
if(!confirm("Supprimer ?")) return;

await fetch('/api/admin/articles.php',{
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

const nom=document.getElementById('nom').value;
const prix=document.getElementById('prix').value;
const description=document.getElementById('desc').value;

await fetch('/api/admin/articles.php',{
method:'POST',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({nom,prix,description})
});

document.getElementById('modal').classList.add('hidden');
chargerArticles();
}

</script>

</body>
</html>
