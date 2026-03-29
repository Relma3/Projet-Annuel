const API = "/api/admin.php";

// Au chargement, récupère le token depuis l'url et le stocke en local
(function() {
    const params = new URLSearchParams(window.location.search);
    const tokenFromUrl = params.get("token");
    if (tokenFromUrl) {
        localStorage.setItem("sh_admin_token", tokenFromUrl);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
})();

function token() {
    return localStorage.getItem("sh_admin_token");
}

async function chargerSeniors() {
    try {
        const res = await fetch(API + "?action=seniors", {
            headers: { "Authorization": "Bearer " + token() }
        });
        const data = await res.json();
        const ul = document.getElementById("liste-seniors");
        ul.innerHTML = "";
        if (!Array.isArray(data)) {
            ul.innerHTML = "<li style='color:red'>Erreur chargement</li>";
            return;
        }
        data.forEach(s => {
            const li = document.createElement("li");
            li.textContent = s.email + (s.prenom ? " — " + s.prenom : "");
            ul.appendChild(li);
        });
    } catch(e) {
        console.error("Erreur seniors:", e);
    }
}

async function chargerPrestataires() {
    try {
        const res = await fetch(API + "?action=prestataires", {
            headers: { "Authorization": "Bearer " + token() }
        });
        const data = await res.json();
        const ul = document.getElementById("liste-prestataires");
        ul.innerHTML = "";
        if (!Array.isArray(data)) {
            ul.innerHTML = "<li style='color:red'>Erreur chargement</li>";
            return;
        }
        data.forEach(p => {
            const li = document.createElement("li");
            li.textContent = p.email + (p.statut ? " [" + p.statut + "]" : "");
            ul.appendChild(li);
        });
    } catch(e) {
        console.error("Erreur prestataires:", e);
    }
}

async function chargerCategories() {
    try {
        const res = await fetch(API + "?action=categories", {
            headers: { "Authorization": "Bearer " + token() }
        });
        const data = await res.json();
        const ul = document.getElementById("liste-categories");
        ul.innerHTML = "";
        if (!Array.isArray(data)) {
            ul.innerHTML = "<li style='color:red'>Erreur chargement</li>";
            return;
        }
        data.forEach(c => {
            const li = document.createElement("li");
            li.innerHTML = c.nom + " <button onclick='supprimerCategorie(" + c.id + ")'>X</button>";
            ul.appendChild(li);
        });
    } catch(e) {
        console.error("Erreur categories:", e);
    }
}

async function ajouterCategorie() {
    const nom         = document.getElementById("cat-nom").value;
    const description = document.getElementById("cat-desc").value;
    await fetch(API + "?action=categories", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify({ nom, description })
    });
    chargerCategories();
}

async function supprimerCategorie(id) {
    await fetch(API + "?action=categories&id=" + id, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + token() }
    });
    chargerCategories();
}

async function chargerEvenements() {
    try {
        const res = await fetch(API + "?action=evenements", {
            headers: { "Authorization": "Bearer " + token() }
        });
        const data = await res.json();
        const ul = document.getElementById("liste-evenements");
        ul.innerHTML = "";
        if (!Array.isArray(data)) {
            ul.innerHTML = "<li style='color:red'>Erreur chargement</li>";
            return;
        }
        data.forEach(e => {
            const li = document.createElement("li");
            li.innerHTML = e.titre + " — " + (e.date_debut || "") +
                " <button onclick='supprimerEvenement(" + e.id + ")'>X</button>";
            ul.appendChild(li);
        });
    } catch(e) {
        console.error("Erreur evenements:", e);
    }
}

async function ajouterEvenement() {
    const titre  = document.getElementById("ev-titre").value;
    const date   = document.getElementById("ev-date").value;
    const lieu   = document.getElementById("ev-lieu").value;
    const places = document.getElementById("ev-places").value;
    await fetch(API + "?action=evenements", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify({ titre, date_debut: date, lieu, nombre_places: places })
    });
    chargerEvenements();
}

async function supprimerEvenement(id) {
    await fetch(API + "?action=evenements&id=" + id, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + token() }
    });
    chargerEvenements();
}