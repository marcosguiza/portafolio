<?php
define('PORTFOLIO_USERS_FILE',       __DIR__ . '/manager/users.json');
define('PORTFOLIO_SITE_ACTIVE_FILE', __DIR__ . '/manager/site_active');
define('PORTFOLIO_SESSION_TIMEOUT',  7200);   // 2 hours
define('PORTFOLIO_MAX_ATTEMPTS',     5);
define('PORTFOLIO_LOCKOUT_SECONDS',  900);    // 15 min

function portfolio_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure',   1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function portfolio_is_site_active(): bool {
    return file_exists(PORTFOLIO_SITE_ACTIVE_FILE);
}

function require_portfolio_auth(): void {
    portfolio_session_start();

    // Manager sessions always bypass the gate (so admin can preview before going live)
    $manager_ok = !empty($_SESSION['manager_user']) && !empty($_SESSION['manager_time'])
                  && (time() - $_SESSION['manager_time'] <= PORTFOLIO_SESSION_TIMEOUT);
    if ($manager_ok) {
        $_SESSION['manager_time'] = time();
        return;
    }

    // If site is OFF, show maintenance page to everyone else
    if (!portfolio_is_site_active()) {
        portfolio_show_offline();
    }

    // Normal portfolio auth
    if (empty($_SESSION['portfolio_user']) || empty($_SESSION['portfolio_time'])) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: /login.php?next=' . $next);
        exit;
    }

    if (time() - $_SESSION['portfolio_time'] > PORTFOLIO_SESSION_TIMEOUT) {
        session_destroy();
        header('Location: /login.php?expired=1');
        exit;
    }

    $_SESSION['portfolio_time'] = time();
}

function portfolio_show_offline(): never {
    http_response_code(503);
    header('Retry-After: 3600');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title>No disponible</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap" />
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Roboto',sans-serif;background:#F5F6F8;min-height:100vh;
    display:flex;align-items:center;justify-content:center;padding:24px;
    -webkit-font-smoothing:antialiased}
  .card{background:#fff;border:1px solid #EBEBEB;border-radius:12px;
    box-shadow:0 4px 24px rgba(0,0,0,.07);padding:48px 44px;width:100%;max-width:400px}
  .card-top{display:flex;align-items:flex-start;justify-content:space-between;
    margin-bottom:40px;gap:12px}
  .brand{display:flex;align-items:center;gap:14px}
  .brand-dot{width:30px;height:30px;background:#BA0C2F;border-radius:7px;flex-shrink:0}
  .brand-name{font-size:13px;font-weight:700;color:#0D0A07;letter-spacing:.3px}
  .brand-sub{font-family:'Roboto Mono',monospace;font-size:10px;color:#AAA;
    letter-spacing:1.5px;text-transform:uppercase;margin-top:2px}
  .lang-switcher{display:flex;gap:3px;flex-shrink:0;padding-top:2px}
  .lang-btn{background:transparent;border:1px solid #EBEBEB;color:#999;
    font-family:'Roboto Mono',monospace;font-size:10px;font-weight:600;
    letter-spacing:1px;text-transform:uppercase;padding:4px 9px;border-radius:3px;
    cursor:pointer;transition:background .15s,color .15s,border-color .15s}
  .lang-btn.active{background:#0D0A07;color:#fff;border-color:#0D0A07}
  .icon-wrap{width:52px;height:52px;background:#F5F6F8;border-radius:12px;
    display:flex;align-items:center;justify-content:center;margin-bottom:20px}
  h1{font-size:22px;font-weight:700;color:#0D0A07;margin-bottom:8px;letter-spacing:-.3px}
  .sub{font-size:13px;color:#888;line-height:1.6}
  .footer-note{text-align:center;margin-top:36px;font-family:'Roboto Mono',monospace;
    font-size:10px;letter-spacing:1px;color:#BBBBBB;text-transform:uppercase}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <div class="brand">
      <div class="brand-dot"></div>
      <div>
        <div class="brand-name">Skywave / VIACHAIN</div>
        <div class="brand-sub">LATAM · 2026</div>
      </div>
    </div>
    <div class="lang-switcher">
      <button class="lang-btn" data-lang="es">ES</button>
      <button class="lang-btn" data-lang="en">EN</button>
      <button class="lang-btn" data-lang="pt">PT</button>
    </div>
  </div>

  <div class="icon-wrap">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#BA0C2F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
  </div>

  <h1 data-i18n="heading">No disponible</h1>
  <p class="sub" data-i18n="sub">Este portafolio no está activo en este momento. Estará disponible durante el evento.</p>

  <div class="footer-note">Portfolio 2026 · LATAM · Skywave / VIACHAIN</div>
</div>

<script>
const LANG_KEY = 'pf_index_lang';
const tr = {
  es: { heading: 'No disponible',   sub: 'Este portafolio no está activo en este momento. Estará disponible durante el evento.' },
  en: { heading: 'Not available',   sub: 'This portfolio is not active at the moment. It will be available during the event.' },
  pt: { heading: 'Não disponível',  sub: 'Este portfólio não está ativo no momento. Estará disponível durante o evento.' },
};
function applyLang(lang) {
  const t = tr[lang] || tr.es;
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const k = el.getAttribute('data-i18n');
    if (t[k]) el.textContent = t[k];
  });
  document.querySelectorAll('.lang-btn').forEach(b => b.classList.toggle('active', b.dataset.lang === lang));
  localStorage.setItem(LANG_KEY, lang);
}
document.querySelectorAll('.lang-btn').forEach(b => b.addEventListener('click', () => applyLang(b.dataset.lang)));
applyLang(localStorage.getItem(LANG_KEY) || 'es');
</script>
</body>
</html>
    <?php
    exit;
}

function portfolio_load_users(): array {
    $raw = @file_get_contents(PORTFOLIO_USERS_FILE);
    return $raw ? (json_decode($raw, true)['users'] ?? []) : [];
}

function portfolio_find_user(string $username): ?array {
    foreach (portfolio_load_users() as $u) {
        if ($u['username'] === $username) return $u;
    }
    return null;
}

function portfolio_is_locked_out(string $username): bool {
    $key = 'pf_lock_' . md5($username);
    if (empty($_SESSION[$key])) return false;
    if ($_SESSION[$key]['until'] < time()) {
        unset($_SESSION[$key]);
        return false;
    }
    return $_SESSION[$key]['attempts'] >= PORTFOLIO_MAX_ATTEMPTS;
}

function portfolio_record_attempt(string $username): void {
    $key = 'pf_lock_' . md5($username);
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'until' => time() + PORTFOLIO_LOCKOUT_SECONDS];
    }
    $_SESSION[$key]['attempts']++;
    $_SESSION[$key]['until'] = time() + PORTFOLIO_LOCKOUT_SECONDS;
}

function portfolio_clear_attempts(string $username): void {
    unset($_SESSION['pf_lock_' . md5($username)]);
}
