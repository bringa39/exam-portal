<?php
// Shared header for all student-facing pages
// Usage: <?php $pageTitle = 'Registration'; include __DIR__ . '/includes/header.php'; ?>
$siteName = 'Exam Portal';
$pageTitle = $pageTitle ?? $siteName;
?>
<div class="site-header">
    <div class="site-header-inner">
        <div class="site-header-left">
            <span class="site-logo"><?= $siteName ?></span>
        </div>
        <div class="site-header-right">
            <div class="lang-dropdown" id="langDropdown">
                <button class="lang-btn" onclick="toggleLangMenu()" id="langBtn">
                    <span class="lang-flag" id="langFlag"></span>
                    <span class="lang-name" id="langName">English</span>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-left:4px"><path d="M3 5L6 8L9 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <div class="lang-menu" id="langMenu"></div>
            </div>
        </div>
    </div>
</div>
<style>
.site-header{background:linear-gradient(135deg,#0f172a,#1e3a8a);padding:0;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.site-header-inner{max-width:1200px;margin:0 auto;padding:12px 24px;display:flex;justify-content:space-between;align-items:center}
.site-header-left{display:flex;align-items:center;gap:12px}
.site-logo{color:#fff;font-weight:700;font-size:1.05rem;letter-spacing:-.3px}
.site-header-right{display:flex;align-items:center;gap:12px}

/* Language dropdown */
.lang-dropdown{position:relative}
.lang-btn{display:flex;align-items:center;gap:6px;padding:6px 12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:8px;color:#fff;font-size:.82rem;font-weight:500;cursor:pointer;font-family:inherit;transition:all .15s;backdrop-filter:blur(4px)}
.lang-btn:hover{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.25)}
.lang-flag{font-size:1.1rem;line-height:1}
.lang-menu{display:none;position:absolute;top:calc(100% + 6px);right:0;background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);min-width:180px;overflow:hidden;z-index:200;border:1px solid #e2e8f0}
.lang-menu.open{display:block}
.lang-menu a{display:flex;align-items:center;gap:10px;padding:10px 16px;color:#1e293b;text-decoration:none;font-size:.88rem;transition:background .1s;cursor:pointer}
.lang-menu a:hover{background:#f1f5f9}
.lang-menu a.active{background:#eff6ff;color:#2563eb;font-weight:600}
.lang-menu a .lm-flag{font-size:1.15rem}
.lang-menu a .lm-name{flex:1}
.lang-menu a .lm-check{color:#2563eb;font-weight:700;font-size:.9rem}

@media(max-width:480px){
    .site-header-inner{padding:10px 16px}
    .lang-name{display:none}
    .lang-btn{padding:6px 8px}
}
</style>
