<!-- Barre accessibilité seniors - fixe en bas à droite -->
<div id="barre-accessibilite" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3">

    <!-- Zoom + -->
    <button onclick="changerZoom(1)"
        id="btn-zoom-plus"
        class="bg-orange-500 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl hover:scale-110 transition-transform"
        title="Agrandir la page">
        <i class="fa-solid fa-magnifying-glass-plus"></i>
    </button>

    <!-- Zoom - -->
    <button onclick="changerZoom(-1)"
        id="btn-zoom-moins"
        class="bg-orange-400 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl hover:scale-110 transition-transform"
        title="Réduire la page">
        <i class="fa-solid fa-magnifying-glass-minus"></i>
    </button>

    <!-- Réinitialiser -->
    <button onclick="reinitZoom()"
        class="bg-slate-500 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-xl hover:scale-110 transition-transform"
        title="Taille normale">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

    <!-- Contraste -->
    <button onclick="toggleContraste()"
        id="btn-contraste"
        class="bg-slate-800 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-xl hover:scale-110 transition-transform"
        title="Contraste élevé">
        <i class="fa-solid fa-circle-half-stroke"></i>
    </button>
</div>

<style>
/* Position fixe de la barre, jamais bougée par le zoom */
#barre-accessibilite {
    position: fixed !important;
    bottom: 1.5rem !important;
    right: 1.5rem !important;
    transform: none !important;
    zoom: 1 !important;
}

/* Contraste élevé */
body.contrast-high {
    filter: contrast(1.5) brightness(1.05) !important;
}
body.contrast-high img {
    filter: contrast(0.9);
}
</style>

<script>
// zoom de page entière 
const ZOOM_MIN  = 0.8;
const ZOOM_MAX  = 2.0;
const ZOOM_STEP = 0.15;

let zoomActuel = parseFloat(localStorage.getItem('sh_zoom') || '1');

function appliquerZoom() {
    // Zoom sur le html entier SAUF la barre d'accessibilité
    document.documentElement.style.zoom = zoomActuel;

    // La barre reste toujours à sa taille et position d'origine
    const barre = document.getElementById('barre-accessibilite');
    if (barre) {
        barre.style.zoom = (1 / zoomActuel).toFixed(4);
        barre.style.right  = '1.5rem';
        barre.style.bottom = '1.5rem';
    }

    // Mise à jour visuelle des boutons
    document.getElementById('btn-zoom-plus').disabled  = zoomActuel >= ZOOM_MAX;
    document.getElementById('btn-zoom-moins').disabled = zoomActuel <= ZOOM_MIN;

    localStorage.setItem('sh_zoom', zoomActuel);
}

function changerZoom(direction) {
    zoomActuel = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, zoomActuel + direction * ZOOM_STEP));
    zoomActuel = Math.round(zoomActuel * 100) / 100;
    appliquerZoom();
}

function reinitZoom() {
    zoomActuel = 1;
    appliquerZoom();
}

// contraste
function toggleContraste() {
    document.body.classList.toggle('contrast-high');
    const actif = document.body.classList.contains('contrast-high');
    localStorage.setItem('sh_contrast', actif ? '1' : '0');

    // Feedback visuel sur le bouton
    const btn = document.getElementById('btn-contraste');
    btn.style.background = actif ? '#f97316' : '';
}

// restauration au changement 
    document.addEventListener('DOMContentLoaded', function() {
    appliquerZoom();
    if (localStorage.getItem('sh_contrast') === '1') {
        document.body.classList.add('contrast-high');
        document.getElementById('btn-contraste').style.background = '#f97316';
    }
});
</script>