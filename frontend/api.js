document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const mot_de_passe = document.getElementById("password").value;

    fetch("/api/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            email: email,
            mot_de_passe: mot_de_passe
        })
    })
    .then(function(res) {
        return res.json();
    })
    .then(function(data) {
        if (data.token) {
            localStorage.setItem("token", data.token);
            localStorage.setItem("type", data.type_utilisateur);
            localStorage.setItem("id_utilisateur", data.id_utilisateur);
            localStorage.setItem("prenom", data.prenom || "");

            if (data.type_utilisateur == "admin") {
                window.location.href = "/frontend/admin/dashboard.html?token=" + encodeURIComponent(data.token);
            } else if (data.type_utilisateur == "senior") {
                window.location.href = "/dashboardS.php";
            } else if (data.type_utilisateur == "prestataire") {
                window.location.href = "/dashboardP.php";
            }
        } else {
            document.getElementById("message").innerText = data.message || "Erreur de connexion";
        }
    })
    .catch(function() {
        document.getElementById("message").innerText = "Impossible de joindre le serveur";
    });
});