function initTutoriel() {
    if (tutorielDejavu) return;

    const etapes = [
        {
            titre: "Bienvenue sur Silver Happy!",
            texte: "Votre espace personnel pour bien vivre après 60 ans. Ce guide rapide vous présente les fonctionnalités essentielles."
        },
        {
            titre: "Vos rendez-vous",
            texte: "Dans l'onglet \"Mes RDV\", retrouvez tous vos services réservés, vos télé-consultations médicales et vos événements à venir."
        },
        {
            titre: "La boutique",
            texte: "Accédez à notre sélection de produits adaptés aux seniors directement depuis le menu en haut de page."
        },
        {
            titre: "La messagerie",
            texte: "Vous pouvez contacter à tout moment l'équipe Silver Happy via l'onglet Messagerie. Nous vous répondons rapidement."
        },
        {
            titre: "C'est parti !",
            texte: "Vous êtes prêt à utiliser votre espace Silver Happy. Bienvenue dans notre communauté !"
        }
    ];

    let i = 0;

    // Bloquer le scroll
    document.body.style.overflow = 'hidden';

    const overlay = document.createElement("div");
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.75);
        display: flex; justify-content: center; align-items: center;
        z-index: 99999;
    `;

    const box = document.createElement("div");
    box.style.cssText = `
        background: white; padding: 40px 36px;
        border-radius: 24px; width: 90%; max-width: 520px;
        text-align: center; font-family: 'DM Sans', sans-serif;
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    function afficherEtape() {
        const estDernier = (i === etapes.length - 1);

        box.innerHTML = `
            <p style="font-size:13px; color:#aaa; font-weight:700; letter-spacing:2px; margin-bottom:20px; text-transform:uppercase;">
                Étape ${i + 1} / ${etapes.length}
            </p>
            <div style="width:100%; background:#f0f0f0; border-radius:99px; height:6px; margin-bottom:28px;">
                <div style="width:${((i + 1) / etapes.length) * 100}%; background:#E37A55; height:6px; border-radius:99px; transition: width 0.3s;"></div>
            </div>
            <h2 style="font-size:26px; color:#E37A55; margin-bottom:16px; font-weight:700;">${etapes[i].titre}</h2>
            <p style="font-size:18px; color:#555; line-height:1.6; margin-bottom:32px;">${etapes[i].texte}</p>
            <button id="btn-tutoriel" style="
                background:#E37A55; color:white; border:none;
                padding:16px 40px; border-radius:12px;
                font-size:18px; font-weight:700; cursor:pointer;
                width: 100%;
            ">${estDernier ? 'Commencer !' : 'Suivant →'}</button>
        `;

        document.getElementById('btn-tutoriel').onclick = function () {
            if (estDernier) {
                fetch('/update_senior.php?action=tutoriel', { method: 'POST' });
                document.body.style.overflow = '';
                overlay.remove();
            } else {
                i++;
                afficherEtape();
            }
        };
    }

    afficherEtape();
}

document.addEventListener("DOMContentLoaded", initTutoriel);