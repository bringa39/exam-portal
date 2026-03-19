<div class="site-footer">
    <span><?= $siteName ?? 'Exam Portal' ?> &mdash; <span data-i18n="footer_text">Secure Online Examination System</span></span>
</div>
<style>
.site-footer{text-align:center;padding:24px 16px;color:#94a3b8;font-size:.8rem;background:#f8fafc;border-top:1px solid #e2e8f0;margin-top:40px}
</style>
<script src="assets/js/i18n.js"></script>
<script>
// ---- Language dropdown logic ----
const _langData = {
    en: {flag: '\ud83c\uddec\ud83c\udde7', name: 'English'},
    fr: {flag: '\ud83c\uddeb\ud83c\uddf7', name: 'Fran\u00e7ais'},
    es: {flag: '\ud83c\uddea\ud83c\uddf8', name: 'Espa\u00f1ol'},
    ar: {flag: '\ud83c\uddf8\ud83c\udde6', name: '\u0627\u0644\u0639\u0631\u0628\u064a\u0629'},
    de: {flag: '\ud83c\udde9\ud83c\uddea', name: 'Deutsch'},
    pt: {flag: '\ud83c\udde7\ud83c\uddf7', name: 'Portugu\u00eas'},
    tr: {flag: '\ud83c\uddf9\ud83c\uddf7', name: 'T\u00fcrk\u00e7e'},
    it: {flag: '\ud83c\uddee\ud83c\uddf9', name: 'Italiano'},
    ru: {flag: '\ud83c\uddf7\ud83c\uddfa', name: '\u0420\u0443\u0441\u0441\u043a\u0438\u0439'},
    zh: {flag: '\ud83c\udde8\ud83c\uddf3', name: '\u4e2d\u6587'},
};

function updateLangBtn() {
    const lang = i18n.getLang();
    const d = _langData[lang] || _langData.en;
    document.getElementById('langFlag').textContent = d.flag;
    document.getElementById('langName').textContent = d.name;
}

function buildLangMenu() {
    const menu = document.getElementById('langMenu');
    if (!menu) return;
    menu.innerHTML = '';
    const current = i18n.getLang();
    i18n.getSupported().forEach(code => {
        const d = _langData[code];
        if (!d) return;
        const a = document.createElement('a');
        a.innerHTML = `<span class="lm-flag">${d.flag}</span><span class="lm-name">${d.name}</span>${code === current ? '<span class="lm-check">\u2713</span>' : ''}`;
        if (code === current) a.classList.add('active');
        a.onclick = () => {
            i18n.setLang(code);
            updateLangBtn();
            buildLangMenu();
            document.getElementById('langMenu').classList.remove('open');
        };
        menu.appendChild(a);
    });
}

function toggleLangMenu() {
    document.getElementById('langMenu').classList.toggle('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.lang-dropdown')) {
        document.getElementById('langMenu')?.classList.remove('open');
    }
});

updateLangBtn();
buildLangMenu();
</script>
