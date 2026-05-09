const LANG_KEY = 'sh_lang';

async function loadLang(lang) {
    try {
        const res  = await fetch('/lang/' + lang + '.json');
        const data = await res.json();
        document.querySelectorAll('[data-i18n]').forEach(function(el) {
            const key = el.getAttribute('data-i18n');
            if (data[key]) el.textContent = data[key];
        });
        localStorage.setItem(LANG_KEY, lang);
        const sel = document.getElementById('lang-selector');
        if (sel) sel.value = lang;
    } catch(e) {
        console.error('Erreur langue :', e);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem(LANG_KEY) || 'fr';
    loadLang(saved);
    const sel = document.getElementById('lang-selector');
    if (sel) sel.addEventListener('change', function() { loadLang(this.value); });
});
