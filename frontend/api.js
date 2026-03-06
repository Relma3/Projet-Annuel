document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const mot_de_passe = document.getElementById("password").value;

    fetch("http://localhost/PA_2EME_ANNEE/api/index.php/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: email, mot_de_passe: mot_de_passe })
    })
    .then(res => res.json())
    .then(data => {
        if (data.token) {
            localStorage.setItem("token", data.token);
            localStorage.setItem("type", data.type_utilisateur);
            document.getElementById("message").innerText = "Connexion réussie";
            window.location.href = "admin/dashboard.html";        
        } else {
            document.getElementById("message").innerText = data.message;
        }
    })
    .catch(() => {
        document.getElementById("message").innerText = "Erreur de connexion à l'API";
    });
});
