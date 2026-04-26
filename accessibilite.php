<!-- Boutons loupe seniors -->
<div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3">
    <button onclick="changerTaille()" 
        class="bg-orange-500 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl hover:scale-110 transition-transform"
        title="Agrandir le texte">
        <i class="fa-solid fa-magnifying-glass-plus"></i>
    </button>
    <button onclick="reinitTaille()"
        class="bg-slate-500 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-xl hover:scale-110 transition-transform"
        title="Taille normale">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
    <button onclick="toggleContraste()"
        class="bg-slate-800 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-xl hover:scale-110 transition-transform"
        title="Contraste élevé">
        <i class="fa-solid fa-circle-half-stroke"></i>
    </button>
</div>

<style>
body.font-large { font-size: 120% !important; }
body.font-xlarge { font-size: 145% !important; }
body.contrast-high { filter: contrast(1.4) !important; }
</style>

<script>
const niveaux = ['', 'font-large', 'font-xlarge'];
let niveauActuel = parseInt(localStorage.getItem('sh_font') || '0');
function appliquerTaille() {
    document.body.classList.remove('font-large', 'font-xlarge');
    if (niveaux[niveauActuel]) document.body.classList.add(niveaux[niveauActuel]);
}
function changerTaille() {
    niveauActuel = (niveauActuel + 1) % niveaux.length;
    localStorage.setItem('sh_font', niveauActuel);
    appliquerTaille();
}
function reinitTaille() {
    niveauActuel = 0;
    localStorage.setItem('sh_font', 0);
    appliquerTaille();
}
function toggleContraste() {
    document.body.classList.toggle('contrast-high');
    localStorage.setItem('sh_contrast', document.body.classList.contains('contrast-high') ? '1' : '0');
}
appliquerTaille();
if (localStorage.getItem('sh_contrast') === '1') document.body.classList.add('contrast-high');
</script>

body {
    font-size: 18px !important;
    line-height: 1.7 !important;
}