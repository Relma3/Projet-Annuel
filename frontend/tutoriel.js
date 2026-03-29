function initTutoriel() {
    if (localStorage.getItem("tutoriel_vu")) {
        return;
    }

    const etapes = [
        {
            titre: "Bienvenue sur Silver Happy",
            texte: "Votre espace personnel pour bien vivre apres 60 ans."
        },
        {
            titre: "Votre planning",
            texte: "Ici vous pouvez voir vos rendez-vous et vos activites."
        },
        {
            titre: "La boutique",
            texte: "Vous pouvez voir les produits proposes sur le site."
        },
        {
            titre: "La messagerie",
            texte: "Vous pouvez contacter l equipe Silver Happy."
        },
        {
            titre: "C est bon",
            texte: "Vous pouvez maintenant utiliser le site."
        }
    ];

    let i = 0;

    const overlay = document.createElement("div");
    overlay.style.position = "fixed";
    overlay.style.top = "0";
    overlay.style.left = "0";
    overlay.style.width = "100%";
    overlay.style.height = "100%";
    overlay.style.background = "rgba(0,0,0,0.8)";
    overlay.style.display = "flex";
    overlay.style.justifyContent = "center";
    overlay.style.alignItems = "center";
    overlay.style.zIndex = "9999";

    const box = document.createElement("div");
    box.style.background = "white";
    box.style.padding = "30px";
    box.style.borderRadius = "20px";
    box.style.width = "80%";
    box.style.maxWidth = "500px";
    box.style.textAlign = "center";

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    function afficherEtape() {
        box.innerHTML = "";

        const titre = document.createElement("h2");
        titre.textContent = etapes[i].titre;
        titre.style.fontSize = "28px";
        titre.style.color = "#E07B54";
        titre.style.marginBottom = "20px";

        const texte = document.createElement("p");
        texte.textContent = etapes[i].texte;
        texte.style.fontSize = "20px";
        texte.style.color = "#555";
        texte.style.marginBottom = "20px";

        const btn = document.createElement("button");
        btn.style.background = "#E07B54";
        btn.style.color = "white";
        btn.style.border = "none";
        btn.style.padding = "15px 30px";
        btn.style.borderRadius = "10px";
        btn.style.fontSize = "18px";
        btn.style.cursor = "pointer";

        if (i == etapes.length - 1) {
            btn.textContent = "Commencer";
        } else {
            btn.textContent = "Suivant";
        }

        btn.onclick = function () {
            if (i == etapes.length - 1) {
                localStorage.setItem("tutoriel_vu", "1");
                overlay.remove();
            } else {
                i++;
                afficherEtape();
            }
        };

        box.appendChild(titre);
        box.appendChild(texte);
        box.appendChild(btn);
    }

    afficherEtape();
}

document.addEventListener("DOMContentLoaded", initTutoriel);