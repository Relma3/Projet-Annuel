const params = new URLSearchParams(window.location.search);
const tokenUrl = params.get("token");

if (tokenUrl) {
    localStorage.setItem("sh_admin_token", tokenUrl);
    window.history.replaceState({}, document.title, window.location.pathname);
}

function token() {
    return localStorage.getItem("sh_admin_token");
}

function authHeaders() {
    return {
        "Authorization": "Bearer " + token(),
        "Content-Type": "application/json"
    };
}

async function chargerSeniors() {
    const res = await fetch("/api/admin/seniors", {
        headers: authHeaders()
    });

    const data = await res.json();
    const tbody = document.getElementById("table-seniors");

    if (!tbody) return;

    if (!Array.isArray(data) || data.length == 0) {
        tbody.innerHTML = "<tr><td colspan='4' class='px-4 py-6 text-center text-slate-400'>Aucun senior</td></tr>";
        return;
    }

    let html = "";

    data.forEach(function(s) {
        html += "<tr class='border-t border-slate-700 hover:bg-slate-700'>";
        html += "<td class='px-4 py-3'>" + s.id_utilisateur + "</td>";
        html += "<td class='px-4 py-3'>" + s.email + "</td>";
        html += "<td class='px-4 py-3'>" + s.type_utilisateur + "</td>";
        html += "<td class='px-4 py-3 text-slate-400'>" + (s.created_at ? s.created_at.substring(0, 10) : "") + "</td>";
        html += "</tr>";
    });

    tbody.innerHTML = html;
}

async function chargerPrestataires() {
    const res = await fetch("/api/admin/prestataires", {
        headers: authHeaders()
    });

    const data = await res.json();
    const tbody = document.getElementById("table-pres");

    if (!tbody) return;

    if (!Array.isArray(data) || data.length == 0) {
        tbody.innerHTML = "<tr><td colspan='5' class='px-4 py-6 text-center text-slate-400'>Aucun prestataire</td></tr>";
        return;
    }

    let html = "";

    data.forEach(function(p) {
        let bouton = "";
        let badgeClass = "bg-yellow-800 text-yellow-200";

        if (p.statut == "valide") {
            badgeClass = "bg-green-800 text-green-200";
        } else {
            bouton = "<button onclick='validerPrestataire(" + p.id_utilisateur + ")' class='bg-green-600 px-3 py-1 rounded text-xs'>Valider</button>";
        }

        html += "<tr class='border-t border-slate-700 hover:bg-slate-700'>";
        html += "<td class='px-4 py-3'>" + p.id_utilisateur + "</td>";
        html += "<td class='px-4 py-3'>" + p.email + "</td>";
        html += "<td class='px-4 py-3'>" + (p.nom || "") + " " + (p.prenom || "") + "</td>";
        html += "<td class='px-4 py-3'><span class='px-2 py-1 rounded text-xs font-bold " + badgeClass + "'>" + p.statut + "</span></td>";
        html += "<td class='px-4 py-3'>" + bouton + "</td>";
        html += "</tr>";
    });

    tbody.innerHTML = html;
}

async function validerPrestataire(id) {
    await fetch("/api/admin/prestataires/" + id + "/valider", {
        method: "PUT",
        headers: authHeaders()
    });

    chargerPrestataires();
    showToast("Prestataire valide");
}

async function chargerCategories() {
    const res = await fetch("/api/admin/categories", {
        headers: authHeaders()
    });

    const data = await res.json();
    const ul = document.getElementById("liste-categories");

    if (!ul) return;

    if (!Array.isArray(data) || data.length == 0) {
        ul.innerHTML = "<li class='p-4 text-slate-400'>Aucune categorie</li>";
        return;
    }

    let html = "";

    data.forEach(function(c) {
        html += "<li class='p-4 border-t border-slate-700 flex justify-between items-center'>";
        html += "<div>";
        html += "<p class='font-semibold'>" + c.nom + "</p>";
        html += "<p class='text-slate-400 text-sm'>" + (c.description || "") + "</p>";
        html += "</div>";
        html += "<button onclick='supprimerCategorie(" + c.id + ")' class='bg-red-600 px-3 py-1 rounded text-sm'>Supprimer</button>";
        html += "</li>";
    });

    ul.innerHTML = html;
}

async function ajouterCategorie() {
    const nom = document.getElementById("cat-nom").value.trim();
    const description = document.getElementById("cat-desc").value.trim();

    if (!nom) {
        showToast("Nom obligatoire");
        return;
    }

    await fetch("/api/admin/categories", {
        method: "POST",
        headers: authHeaders(),
        body: JSON.stringify({
            nom: nom,
            description: description
        })
    });

    document.getElementById("cat-nom").value = "";
    document.getElementById("cat-desc").value = "";

    chargerCategories();
    showToast("Categorie ajoutee");
}

async function supprimerCategorie(id) {
    if (!confirm("Supprimer cette categorie ?")) return;

    await fetch("/api/admin/categories/" + id, {
        method: "DELETE",
        headers: authHeaders()
    });

    chargerCategories();
    showToast("Categorie supprimee");
}

async function chargerEvenements() {
    const res = await fetch("/api/admin/evenements", {
        headers: authHeaders()
    });

    const data = await res.json();
    const ul = document.getElementById("liste-evenements");

    if (!ul) return;

    if (!Array.isArray(data) || data.length == 0) {
        ul.innerHTML = "<li class='p-4 text-slate-400'>Aucun evenement</li>";
        return;
    }

    let html = "";

    data.forEach(function(e) {
        html += "<li class='p-4 border-t border-slate-700 flex justify-between items-center'>";
        html += "<div>";
        html += "<p class='font-semibold'>" + e.titre + "</p>";
        html += "<p class='text-slate-400 text-sm'>" + (e.date_debut ? e.date_debut.substring(0, 10) : "") + " - " + (e.lieu || "") + " - " + e.nombre_places + " places</p>";
        html += "</div>";
        html += "<button onclick='supprimerEvenement(" + e.id + ")' class='bg-red-600 px-3 py-1 rounded text-sm'>Supprimer</button>";
        html += "</li>";
    });

    ul.innerHTML = html;
}

async function ajouterEvenement() {
    const titre = document.getElementById("ev-titre").value.trim();
    const date = document.getElementById("ev-date").value;
    const lieu = document.getElementById("ev-lieu").value.trim();
    const places = document.getElementById("ev-places").value;

    if (!titre || !date) {
        showToast("Titre et date obligatoires");
        return;
    }

    await fetch("/api/admin/evenements", {
        method: "POST",
        headers: authHeaders(),
        body: JSON.stringify({
            titre: titre,
            date_debut: date,
            lieu: lieu,
            nombre_places: parseInt(places) || 20
        })
    });

    document.getElementById("ev-titre").value = "";
    document.getElementById("ev-date").value = "";
    document.getElementById("ev-lieu").value = "";
    document.getElementById("ev-places").value = "";

    chargerEvenements();
    showToast("Evenement ajoute");
}

async function supprimerEvenement(id) {
    if (!confirm("Supprimer cet evenement ?")) return;

    await fetch("/api/admin/evenements/" + id, {
        method: "DELETE",
        headers: authHeaders()
    });

    chargerEvenements();
}

function toast(msg) {
    const t = document.getElementById("toast");
    const text = document.getElementById("toast-msg");

    if (!t || !text) return;

    text.textContent = msg;
    t.classList.remove("hidden");

    setTimeout(function() {
        t.classList.add("hidden");
    }, 3000);
}

function showToast(msg) {
    toast(msg);
}

async function chargerArticles() {
    const res = await fetch("/api/admin/articles", {
        headers: { "Authorization": "Bearer " + token() }
    });
    const data = await res.json();
    const ul = document.getElementById("liste-articles");
    if (!ul) return;
    if (!Array.isArray(data) || data.length == 0) {
        ul.innerHTML = "<li class='p-4 text-slate-400'>Aucun article</li>";
        return;
    }
    let html = "";
    data.forEach(function(a) {
        html += "<li class='p-4 border-t border-slate-700 flex justify-between items-center'>";
        html += "<div>";
        html += "<p class='font-semibold'>" + a.nom + "</p>";
        html += "<p class='text-slate-400 text-sm'>" + a.prix + " € - " + (a.categorie || "Sans categorie") + " - " + (a.disponible ? "Disponible" : "Indisponible") + "</p>";
        html += "</div>";
        html += "<div class='flex gap-2'>";
        html += "<button onclick='editerArticle(" + a.id_article + ")' class='bg-blue-600 px-3 py-1 rounded text-sm'>Editer</button>";
        html += "<button onclick='supprimerArticle(" + a.id_article + ")' class='bg-red-600 px-3 py-1 rounded text-sm'>Supprimer</button>";
        html += "</div>";
        html += "</li>";
    });
    ul.innerHTML = html;
}

async function sauvegarderArticle() {
    const id = document.getElementById("article-edit-id") ? document.getElementById("article-edit-id").value : "";
    const body = {
        nom: document.getElementById("article-nom").value.trim(),
        description: document.getElementById("article-desc").value.trim(),
        prix: parseFloat(document.getElementById("article-prix").value),
        categorie: document.getElementById("article-cat").value.trim(),
        disponible: document.getElementById("article-dispo") ? (document.getElementById("article-dispo").checked ? 1 : 0) : 1
    };
    if (!body.nom || !body.prix) { showToast("Nom et prix obligatoires"); return; }
    const method = id ? "PUT" : "POST";
    const url = id ? "/api/admin/articles/" + id : "/api/admin/articles";
    await fetch(url, { method: method, headers: Object.assign(authHeaders(), {"Content-Type": "application/json"}), body: JSON.stringify(body) });
    chargerArticles();
    showToast(id ? "Article modifie" : "Article cree");
}

async function supprimerArticle(id) {
    if (!confirm("Supprimer cet article ?")) return;
    await fetch("/api/admin/articles/" + id, { method: "DELETE", headers: authHeaders() });
    chargerArticles();
    showToast("Article supprime");
}

function editerArticle(id) {
    showToast("Cliquer sur l'article pour l'editer (id: " + id + ")");
}

async function chargerConseils() {
    const res = await fetch("/api/admin/conseils", {
        headers: { "Authorization": "Bearer " + token() }
    });
    const data = await res.json();
    const ul = document.getElementById("liste-conseils");
    if (!ul) return;
    if (!Array.isArray(data) || data.length == 0) {
        ul.innerHTML = "<li class='p-4 text-slate-400'>Aucun conseil</li>";
        return;
    }
    let html = "";
    data.forEach(function(c) {
        html += "<li class='p-4 border-t border-slate-700 flex justify-between items-center'>";
        html += "<div>";
        html += "<p class='font-semibold'>" + c.titre + "</p>";
        html += "<p class='text-slate-400 text-sm'>" + (c.categorie || "general") + "</p>";
        html += "</div>";
        html += "<button onclick='supprimerConseil(" + c.id + ")' class='bg-red-600 px-3 py-1 rounded text-sm'>Supprimer</button>";
        html += "</li>";
    });
    ul.innerHTML = html;
}

async function ajouterConseil() {
    const titre = document.getElementById("conseil-titre").value.trim();
    const contenu = document.getElementById("conseil-contenu").value.trim();
    const cat = document.getElementById("conseil-cat") ? document.getElementById("conseil-cat").value.trim() : "general";
    if (!titre || !contenu) { showToast("Titre et contenu obligatoires"); return; }
    await fetch("/api/admin/conseils", {
        method: "POST",
        headers: Object.assign(authHeaders(), {"Content-Type": "application/json"}),
        body: JSON.stringify({ titre: titre, contenu: contenu, categorie: cat || "general" })
    });
    document.getElementById("conseil-titre").value = "";
    document.getElementById("conseil-contenu").value = "";
    chargerConseils();
    showToast("Conseil publie");
}

async function supprimerConseil(id) {
    if (!confirm("Supprimer ce conseil ?")) return;
    await fetch("/api/admin/conseils/" + id, { method: "DELETE", headers: authHeaders() });
    chargerConseils();
    showToast("Conseil supprime");
}
