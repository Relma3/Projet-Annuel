<!-- Boutons accessibilité flottants -->
<div id="barre-accessibilite" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3">

    <button onclick="changerZoom(1)" id="btn-zoom-plus"
        class="bg-orange-500 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl hover:scale-110 transition-transform"
        title="Agrandir la page">
        <i class="fa-solid fa-magnifying-glass-plus"></i>
    </button>

    <button onclick="changerZoom(-1)" id="btn-zoom-moins"
        class="bg-orange-400 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl hover:scale-110 transition-transform"
        title="Réduire la page">
        <i class="fa-solid fa-magnifying-glass-minus"></i>
    </button>

    <button onclick="toggleContraste()" id="btn-contraste"
        class="bg-slate-800 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-xl hover:scale-110 transition-transform"
        title="Contraste élevé">
        <i class="fa-solid fa-circle-half-stroke"></i>
    </button>
</div>

<style>
#barre-accessibilite {
    position: fixed !important;
    bottom: 1.5rem !important;
    right: 1.5rem !important;
}
body.contrast-high {
    filter: contrast(1.5) brightness(1.05) !important;
}
body.contrast-high img {
    filter: contrast(0.9);
}
</style>

<script>
const ZOOM_MIN  = 0.8;
const ZOOM_MAX  = 1.8;
const ZOOM_STEP = 0.1;

let zoomActuel = parseFloat(localStorage.getItem('sh_zoom') || '1');

function appliquerZoom() {
    document.documentElement.style.zoom = zoomActuel;

    const barre = document.getElementById('barre-accessibilite');
    if (barre) barre.style.zoom = (1 / zoomActuel).toFixed(4);

    const btnPlus  = document.getElementById('btn-zoom-plus');
    const btnMoins = document.getElementById('btn-zoom-moins');
    if (btnPlus)  btnPlus.disabled  = zoomActuel >= ZOOM_MAX;
    if (btnMoins) btnMoins.disabled = zoomActuel <= ZOOM_MIN;

    localStorage.setItem('sh_zoom', zoomActuel);
}

function changerZoom(direction) {
    zoomActuel = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, zoomActuel + direction * ZOOM_STEP));
    zoomActuel = Math.round(zoomActuel * 100) / 100;
    appliquerZoom();
}

function toggleContraste() {
    document.body.classList.toggle('contrast-high');
    const actif = document.body.classList.contains('contrast-high');
    localStorage.setItem('sh_contrast', actif ? '1' : '0');
    document.getElementById('btn-contraste').style.background = actif ? '#f97316' : '';
}

document.addEventListener('DOMContentLoaded', function() {
    appliquerZoom();
    if (localStorage.getItem('sh_contrast') === '1') {
        document.body.classList.add('contrast-high');
        document.getElementById('btn-contraste').style.background = '#f97316';
    }
});
</script>