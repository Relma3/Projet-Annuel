const API = "http://localhost/PA_2EME_ANNEE/api/index.php";

function token() { return localStorage.getItem("token"); }

async function appel(route) {
    const res = await fetch(API + route, {
        headers: { "Authorization": "Bearer " + token() }
    });
    return res.json();
}

async function chargerSeniors() {
    const data = await appel("/admin/seniors");
    const ul = document.getElementById("liste-seniors");
    ul.innerHTML = "";
    data.forEach(s => {
        const li = document.createElement("li");
        li.textContent = s.email + " — " + s.created_at;
        ul.appendChild(li);
    });
}

async function chargerPrestataires() {
    const data = await appel("/admin/prestataires");
    const ul = document.getElementById("liste-prestataires");
    ul.innerHTML = "";
    data.forEach(p => {
        const li = document.createElement("li");
        li.textContent = p.email + " — " + p.created_at;
        ul.appendChild(li);
    });
}
