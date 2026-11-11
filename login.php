<?php
require_once __DIR__ . '/portfolio_auth.php';
portfolio_session_start();

if (!empty($_SESSION['portfolio_user']) || !empty($_SESSION['manager_user'])) {
    header('Location: /'); exit;
}

$error_key = '';
$expired   = isset($_GET['expired']);
$next      = $_GET['next'] ?? '/';
$next      = '/' . ltrim(parse_url($next, PHP_URL_PATH) ?? '', '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $next = $_POST['next'] ?? '/';
    if (!str_starts_with($next, '/')) $next = '/';

    if (portfolio_is_locked_out($user)) {
        $error_key = 'lockout';
    } else {
        $found = portfolio_find_user($user);
        if ($found && password_verify($pass, $found['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['portfolio_user'] = $found['username'];
            $_SESSION['portfolio_time'] = time();
            portfolio_clear_attempts($user);
            header('Location: ' . $next); exit;
        } else {
            portfolio_record_attempt($user);
            $error_key = 'invalid';
            usleep(500000);
        }
    }
}

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title data-i18n-title>Acceso · Portafolio LATAM 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap" />
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Roboto', sans-serif;
    background: #F5F6F8;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    -webkit-font-smoothing: antialiased;
  }

  .card {
    background: #fff;
    border: 1px solid #EBEBEB;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
    padding: 48px 44px;
    width: 100%;
    max-width: 400px;
  }

  /* ── top row: brand + lang switcher ── */
  .card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 36px;
    gap: 12px;
  }
  .brand { display: flex; align-items: center; gap: 14px; }
  .brand-dot {
    width: 30px; height: 30px;
    background: #BA0C2F;
    border-radius: 7px;
    flex-shrink: 0;
  }
  .brand-name {
    font-size: 13px; font-weight: 700;
    color: #0D0A07; letter-spacing: .3px;
  }
  .brand-sub {
    font-family: 'Roboto Mono', monospace;
    font-size: 10px; color: #AAA;
    letter-spacing: 1.5px; text-transform: uppercase;
    margin-top: 2px;
  }

  .lang-switcher { display: flex; gap: 3px; flex-shrink: 0; padding-top: 2px; }
  .lang-btn {
    background: transparent;
    border: 1px solid #EBEBEB;
    color: #999;
    font-family: 'Roboto Mono', monospace;
    font-size: 10px; font-weight: 600;
    letter-spacing: 1px; text-transform: uppercase;
    padding: 4px 9px; border-radius: 3px;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
  }
  .lang-btn.active { background: #0D0A07; color: #fff; border-color: #0D0A07; }

  /* ── lock badge ── */
  .lock-badge {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 20px;
  }
  .lock-badge svg { color: #BA0C2F; }
  .lock-label {
    font-family: 'Roboto Mono', monospace;
    font-size: 10px; letter-spacing: 2px;
    text-transform: uppercase; color: #BA0C2F;
  }

  h1 {
    font-size: 24px; font-weight: 700;
    color: #0D0A07; margin-bottom: 6px; letter-spacing: -.3px;
  }
  .sub { font-size: 13px; color: #888; margin-bottom: 28px; line-height: 1.5; }

  label {
    display: block;
    font-family: 'Roboto Mono', monospace;
    font-size: 10px; font-weight: 600;
    color: #666; letter-spacing: 1px;
    text-transform: uppercase; margin-bottom: 6px;
  }

  input[type=text], input[type=password] {
    width: 100%; padding: 11px 14px;
    border: 1px solid #DCDCDC; border-radius: 7px;
    font-size: 14px; font-family: 'Roboto', sans-serif;
    color: #0D0A07; background: #FAFAFA;
    transition: border-color .15s, background .15s;
    margin-bottom: 18px;
  }
  input:focus { outline: none; border-color: #BA0C2F; background: #fff; }

  .btn {
    width: 100%; padding: 13px;
    background: #BA0C2F; color: #fff;
    border: none; border-radius: 7px;
    font-family: 'Roboto', sans-serif;
    font-size: 14px; font-weight: 600;
    letter-spacing: .3px; cursor: pointer;
    transition: background .15s; margin-top: 4px;
  }
  .btn:hover { background: #9a0a27; }

  .alert {
    padding: 11px 14px; border-radius: 7px;
    font-size: 13px; margin-bottom: 18px; line-height: 1.45;
  }
  .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
  .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

  .footer-note {
    text-align: center; margin-top: 28px;
    font-family: 'Roboto Mono', monospace;
    font-size: 10px; letter-spacing: 1px;
    color: #BBBBBB; text-transform: uppercase;
  }
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

  <div class="lock-badge">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    <span class="lock-label" data-i18n="lock-label">Acceso restringido</span>
  </div>

  <h1 data-i18n="heading">Iniciar sesión</h1>
  <p class="sub" data-i18n="sub">Ingrese sus credenciales para acceder al portafolio.</p>

  <?php if ($expired): ?>
    <div class="alert alert-warning" data-i18n="expired">Su sesión expiró. Por favor inicie sesión nuevamente.</div>
  <?php endif; ?>
  <?php if ($error_key): ?>
    <div class="alert alert-error" data-i18n="error-<?= $error_key ?>">
      <?php if ($error_key === 'lockout'): ?>
        Demasiados intentos fallidos. Intente nuevamente en 15 minutos.
      <?php else: ?>
        Usuario o contraseña incorrectos.
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>" />
    <label for="username" data-i18n="label-user">Usuario</label>
    <input type="text" id="username" name="username" required autofocus
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
    <label for="password" data-i18n="label-pass">Contraseña</label>
    <input type="password" id="password" name="password" required />
    <button type="submit" class="btn" data-i18n="btn">Ingresar →</button>
  </form>

  <div class="footer-note">Portfolio 2026 · LATAM · Skywave / VIACHAIN</div>
</div>

<script>
const LANG_KEY = 'pf_index_lang';

const tr = {
  es: {
    title:        'Acceso · Portafolio LATAM 2026',
    'lock-label': 'Acceso restringido',
    heading:      'Iniciar sesión',
    sub:          'Ingrese sus credenciales para acceder al portafolio.',
    expired:      'Su sesión expiró. Por favor inicie sesión nuevamente.',
    'error-invalid': 'Usuario o contraseña incorrectos.',
    'error-lockout': 'Demasiados intentos fallidos. Intente nuevamente en 15 minutos.',
    'label-user': 'Usuario',
    'label-pass': 'Contraseña',
    btn:          'Ingresar →',
  },
  en: {
    title:        'Access · Portfolio LATAM 2026',
    'lock-label': 'Restricted access',
    heading:      'Sign in',
    sub:          'Enter your credentials to access the portfolio.',
    expired:      'Your session expired. Please sign in again.',
    'error-invalid': 'Invalid credentials.',
    'error-lockout': 'Too many failed attempts. Try again in 15 minutes.',
    'label-user': 'Username',
    'label-pass': 'Password',
    btn:          'Sign in →',
  },
  pt: {
    title:        'Acesso · Portfólio LATAM 2026',
    'lock-label': 'Acesso restrito',
    heading:      'Entrar',
    sub:          'Insira suas credenciais para acessar o portfólio.',
    expired:      'Sua sessão expirou. Por favor entre novamente.',
    'error-invalid': 'Usuário ou senha incorretos.',
    'error-lockout': 'Muitas tentativas falhas. Tente novamente em 15 minutos.',
    'label-user': 'Usuário',
    'label-pass': 'Senha',
    btn:          'Entrar →',
  },
};

function applyLang(lang) {
  const t = tr[lang] || tr.es;
  document.title = t.title;
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key] !== undefined) el.textContent = t[key];
  });
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.lang === lang);
  });
  localStorage.setItem(LANG_KEY, lang);
}

document.querySelectorAll('.lang-btn').forEach(btn => {
  btn.addEventListener('click', () => applyLang(btn.dataset.lang));
});

applyLang(localStorage.getItem(LANG_KEY) || 'es');
</script>
</body>
</html>
