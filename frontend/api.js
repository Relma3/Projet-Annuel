document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const mot_de_passe = document.getElementById("password").value;

    fetch("http://localhost/PA_2EME_ANNEE/api/index.php/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            email: email,
            mot_de_passe: mot_de_passe
        })
    })
    .then(function (res) {
        return res.json();
    })
    .then(function (data) {
        if (data.token) {
            localStorage.setItem("token", data.token);
            localStorage.setItem("type", data.type_utilisateur);
            document.getElementById("message").innerText = "Connexion reussie";

            if (data.type_utilisateur == "admin") {
                window.location.href = "admin/dashboard.html";
            }

            if (data.type_utilisateur == "senior") {
                window.location.href = "senior/profil.html";
            }

            if (data.type_utilisateur == "prestataire") {
                window.location.href = "prestataire/profil.html";
            }
        } else {
            document.getElementById("message").innerText = data.message;
        }
    })
    .catch(function () {
        document.getElementById("message").innerText = "Erreur de connexion a l API";
    });
});