const API = "http://localhost/PA_2EME_ANNEE/api/index.php";

function token() {
    return localStorage.getItem("token");
}

async function chargerProfil() {
    const res = await fetch(API + "/seniors/me", {
        headers: {
            "Authorization": "Bearer " + token()
        }
    });

    const data = await res.json();

    if (data.email) {
        document.getElementById("email").value = data.email;
    } else {
        document.getElementById("message").innerText = data.message;
    }
}

async function sauvegarder() {
    const email = document.getElementById("email").value;

    const res = await fetch(API + "/seniors/me", {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token()
        },
        body: JSON.stringify({ email })
    });

    const data = await res.json();
    document.getElementById("message").innerText = data.message;
}

chargerProfil();