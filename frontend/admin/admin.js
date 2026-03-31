const API = "/api/admin.php";

const params = new URLSearchParams(window.location.search);
const tokenFromUrl = params.get("token");

if (tokenFromUrl) {
    localStorage.setItem("sh_admin_token", tokenFromUrl);
    window.history.replaceState({}, document.title, window.location.pathname);
}

function token() {
    return localStorage.getItem("sh_admin_token");
}

async function chargerSeniors() {
    const res = await fetch(API + "?action=seniors", {
        headers: { "Authorization": "Bearer " + token() }
    });

    const data = await res.json();
    const ul = document.getElementById("liste-seniors");
    ul.innerHTML = "";

    if (!Array.isArray(data)) {
        ul.innerHTML = "<li>Erreur chargement</li>";
        return;
    }

    data.forEach(function(s) {
        const li = document.createElement("li");
        li.textContent = s.email;
        ul.appendChild(li);
    });
}

async function chargerPrestataires() {
    const res = await fetch(API + "?action=prestataires", {
        headers: { "Authorization": "Bearer " + token() }
    });

    const data = await res.json();
    const ul = document.getElementById("liste-prestataires");
    ul.innerHTML = "";

    if (!Array.isArray(data)) {
        ul.innerHTML = "<li>Erreur chargement</li>";
        return;
    }

    data.forEach(function(p) {
        const li = document.createElement("li");
        li.textContent = p.email + " [" + p.statut + "]";
        ul.appendChild(li);
    });
}

async function chargerCategories() {
    const res = await fetch(API + "?action=categories", {
        headers: { "Authorization": "Bearer " + token() }
    });

    const data = await res.json();
    const ul = document.getElementById("liste-categories");
    ul.innerHTML = "";

    if (!Array.isArray(data)) {
        ul.innerHTML = "<li>Erreur chargement</li>";
        return;
    }

    data.forEach(function(c) {
        const li = document.createElement("li");
        li.innerHTML = c.nom + " <button onclick='supprimerCategorie(" + c.id + ")'>X</button>";
        ul.appendChild(li);
    });
}

async function ajouterCategorie() {
    const nom = document.getElementById("cat-nom").value;
    const description = document.getElementById("cat-desc").value;

    await fetch(API + "?action=categories", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify({
            nom: nom,
            description: description
        })
    });

    chargerCategories();
}

async function supprimerCategorie(id) {
    await fetch(API + "?action=categories&id=" + id, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + token()
        }
    });

    chargerCategories();
}

async function chargerEvenements() {
    const res = await fetch(API + "?action=evenements", {
        headers: { "Authorization": "Bearer " + token() }
    });

    const data = await res.json();
    const ul = document.getElementById("liste-evenements");
    ul.innerHTML = "";

    if (!Array.isArray(data)) {
        ul.innerHTML = "<li>Erreur chargement</li>";
        return;
    }

    data.forEach(function(e) {
        const li = document.createElement("li");
        li.innerHTML = e.titre + " - " + (e.date_debut || "") + " <button onclick='supprimerEvenement(" + e.id + ")'>X</button>";
        ul.appendChild(li);
    });
}

async function ajouterEvenement() {
    const titre = document.getElementById("ev-titre").value;
    const date = document.getElementById("ev-date").value;
    const lieu = document.getElementById("ev-lieu").value;
    const places = document.getElementById("ev-places").value;

    await fetch(API + "?action=evenements", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify({
            titre: titre,
            date_debut: date,
            lieu: lieu,
            nombre_places: places
        })
    });

    chargerEvenements();
}

async function supprimerEvenement(id) {
    await fetch(API + "?action=evenements&id=" + id, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + token()
        }
    });

    chargerEvenements();
}