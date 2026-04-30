<style>
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
    localStorage.setItem('sh_zoom', zoomActuel);
}

function changerZoom(direction) {
    zoomActuel = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, zoomActuel + direction * ZOOM_STEP));
    zoomActuel = Math.round(zoomActuel * 100) / 100;
    appliquerZoom();
}

document.addEventListener('DOMContentLoaded', function() {
    appliquerZoom();
});
</script>