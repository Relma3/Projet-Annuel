const API = "/api";

function token() {
    return localStorage.getItem("token");
}

async function chargerProfil() {
    const res = await fetch(API + "/seniors/me", {
        headers: {
            "Authorization": "Bearer " + token()
        }
    });

    if (res.status == 401) {
        window.location.href = "/connexion.php";
        return;
    }

    const data = await res.json();

    if (data.email) {
        if (document.getElementById("email")) {
            document.getElementById("email").value = data.email;
        }

        if (document.getElementById("nom")) {
            document.getElementById("nom").value = data.nom || "";
        }

        if (document.getElementById("prenom")) {
            document.getElementById("prenom").value = data.prenom || "";
        }

        if (document.getElementById("telephone")) {
            document.getElementById("telephone").value = data.telephone || "";
        }

        if (document.getElementById("adresse")) {
            document.getElementById("adresse").value = data.adresse || "";
        }
    } else {
        const msg = document.getElementById("message");
        if (msg) {
            msg.innerText = data.message || "Erreur chargement profil";
        }
    }
}

async function sauvegarder() {
    const body = {};

    const email = document.getElementById("email");
    const nom = document.getElementById("nom");
    const prenom = document.getElementById("prenom");
    const telephone = document.getElementById("telephone");
    const adresse = document.getElementById("adresse");

    if (email) {
        body.email = email.value;
    }

    if (nom) {
        body.nom = nom.value;
    }

    if (prenom) {
        body.prenom = prenom.value;
    }

    if (telephone) {
        body.telephone = telephone.value;
    }

    if (adresse) {
        body.adresse = adresse.value;
    }

    const res = await fetch(API + "/seniors/me", {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify(body)
    });

    const data = await res.json();
    const msg = document.getElementById("message");

    if (msg) {
        msg.innerText = data.message;
    }
}

chargerProfil();