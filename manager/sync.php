<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    http_response_code(403); exit('Solo disponible para administradores');
}

$is_router = ($_SERVER['SERVER_PORT'] ?? '') === '9090';
$steps     = [];
$result    = null;

if ($is_router && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sync') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $result = ['error' => 'CSRF inválido. Recarga la página.'];
    } else {
        set_time_limit(300);

        $root = dirname(__DIR__);
        $tmp  = '/tmp/portafolio_sync.tar.gz';
        $url  = LIVE_SERVER_URL . '/manager/sync_export.php?token=' . urlencode(SYNC_SECRET);

        // 1. Backup local data
        $had_users = file_exists("$root/manager/users.json");
        $had_leads = file_exists("$root/manager/leads.json");
        if ($had_users) @copy("$root/manager/users.json", '/tmp/_sw_users.json');
        if ($had_leads) @copy("$root/manager/leads.json", '/tmp/_sw_leads.json');
        $steps[] = ['ok', 'Respaldo de usuarios y leads completado'];

        // 2. Download tar from live server
        shell_exec("wget -q --no-check-certificate -O " . escapeshellarg($tmp)
            . " " . escapeshellarg($url) . " 2>&1");

        if (!file_exists($tmp) || filesize($tmp) < 1000) {
            $result = ['error' => 'No se pudo descargar el archivo del servidor. Verifica la conexión a internet.'];
        } else {
            $kb = round(filesize($tmp) / 1024);
            $steps[] = ['ok', "Descarga completada ({$kb} KB)"];

            // 3. Extract
            $out = trim((string) shell_exec("tar xzf " . escapeshellarg($tmp)
                . " -C " . escapeshellarg($root) . " 2>&1"));
            if ($out !== '') {
                $result = ['error' => "Error al extraer archivos: $out"];
            } else {
                $steps[] = ['ok', 'Archivos extraídos correctamente'];

                // 4. Restore local data
                if (file_exists('/tmp/_sw_users.json')) @copy('/tmp/_sw_users.json', "$root/manager/users.json");
                if (file_exists('/tmp/_sw_leads.json')) @copy('/tmp/_sw_leads.json', "$root/manager/leads.json");
                if (!file_exists("$root/manager/leads.json"))
                    file_put_contents("$root/manager/leads.json", '[]');
                $steps[] = ['ok', 'Usuarios y leads locales restaurados'];

                // 5. Patch HTTP sessions (cookie_secure off for HTTP local use)
                $f = "$root/manager/auth.php";
                file_put_contents($f, str_replace(
                    "ini_set('session.cookie_secure',   1)",
                    "ini_set('session.cookie_secure',   0)",
                    file_get_contents($f)
                ));
                $f = "$root/lead.php";
                file_put_contents($f, str_replace(
                    "'cookie_secure'   => true",
                    "'cookie_secure'   => false",
                    file_get_contents($f)
                ));
                $steps[] = ['ok', 'Parches HTTP aplicados (cookie_secure)'];

                // 6. Permissions — fix parent dirs so nobody (PHP-FPM) can traverse them
                $usb = dirname(dirname($root));
                shell_exec("chmod a+x " . escapeshellarg($usb) . " " . escapeshellarg("$usb/www") . " 2>/dev/null");
                shell_exec("chmod -R 755 " . escapeshellarg($root) . " 2>&1");
                shell_exec("chmod a+rwx " . escapeshellarg("$usb/tmp") . " " . escapeshellarg("$usb/tmp/sessions") . " " . escapeshellarg("$usb/logs") . " 2>/dev/null");
                shell_exec("chmod 664 " . escapeshellarg("$root/manager/users.json")
                    . " " . escapeshellarg("$root/manager/leads.json") . " 2>/dev/null");
                $steps[] = ['ok', 'Permisos actualizados'];

                // Cleanup
                @unlink($tmp);
                @unlink('/tmp/_sw_users.json');
                @unlink('/tmp/_sw_leads.json');

                file_put_contents(SYNC_STATUS_FILE, json_encode([
                    'last_ok' => date('c'),
                    'source' => LIVE_SERVER_URL,
                    'archive_kb' => $kb,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

                $result = ['ok' => true];
            }
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="robots" content="noindex,nofollow"/>
<title>Sincronizar · Manager</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#F0F2F5;color:#111;font-size:14px}
.hdr{background:#fff;border-bottom:1px solid #E5E7EB;height:56px;display:flex;align-items:center;
  padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.hdr-brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;color:#0D0A07}
.hdr-dot{width:22px;height:22px;background:#BA0C2F;border-radius:5px;flex-shrink:0}
.hdr-sep{width:1px;height:24px;background:#E5E7EB}
.btn-back{font-size:12px;color:#555;text-decoration:none;font-weight:600;padding:5px 10px;
  border-radius:5px;border:1px solid #D1D5DB;background:#fff;cursor:pointer;margin-left:auto}
.btn-back:hover{background:#F3F4F6}
.wrap{max-width:600px;margin:40px auto;padding:0 16px}
.card{background:#fff;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.08);padding:32px}
.card-title{font-size:18px;font-weight:700;color:#0D0A07;margin-bottom:8px}
.card-desc{font-size:13px;color:#666;line-height:1.6;margin-bottom:24px}
.info-box{background:#EFF6FF;border:1px solid #BAE6FD;border-radius:8px;padding:12px 16px;
  font-size:12px;color:#1E40AF;line-height:1.6;margin-bottom:24px}
.btn-sync{width:100%;padding:13px;background:#0369A1;border:none;border-radius:7px;color:#fff;
  font-size:14px;font-weight:700;cursor:pointer;transition:background .15s}
.btn-sync:hover{background:#0284C7}
.btn-sync:disabled{background:#93C5FD;cursor:not-allowed}
.steps{list-style:none;margin-bottom:20px}
.steps li{display:flex;align-items:center;gap:10px;padding:8px 0;
  border-bottom:1px solid #F3F4F6;font-size:13px;color:#374151}
.steps li:last-child{border-bottom:none}
.step-ok{color:#16A34A;font-size:16px;flex-shrink:0}
.step-err{color:#DC2626;font-size:16px;flex-shrink:0}
.result-ok{background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;
  padding:16px 20px;color:#166534;font-weight:600;font-size:14px;margin-bottom:20px;
  display:flex;align-items:center;gap:10px}
.result-err{background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;
  padding:16px 20px;color:#991B1B;font-size:13px;margin-bottom:20px}
.result-err strong{display:block;font-size:14px;margin-bottom:4px}
.btn-row{display:flex;gap:10px;margin-top:16px}
.btn-secondary{flex:1;padding:10px;background:#fff;border:1px solid #D1D5DB;border-radius:7px;
  color:#374151;font-size:13px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;
  transition:background .15s}
.btn-secondary:hover{background:#F3F4F6}
.unavailable{text-align:center;padding:60px 0;color:#9CA3AF}
.unavailable p{font-size:15px;margin-bottom:16px}
</style>
</head>
<body>

<div class="hdr">
  <div class="hdr-brand"><div class="hdr-dot"></div>Portfolio Manager</div>
  <div class="hdr-sep"></div>
  <span style="font-size:12px;color:#888">Sincronización</span>
  <a class="btn-back" href="index.php">← Volver al manager</a>
</div>

<div class="wrap">
  <div class="card">

<?php if (!$is_router): ?>
  <div class="unavailable">
    <p>Esta función solo está disponible desde el enrutador local<br>(puerto 9090).</p>
    <a class="btn-secondary" href="index.php">← Volver al manager</a>
  </div>

<?php elseif ($result !== null): ?>

  <?php if (!empty($result['ok'])): ?>
    <div class="result-ok">✓ Sincronización completada exitosamente</div>
  <?php else: ?>
    <div class="result-err"><strong>Error durante la sincronización</strong><?= htmlspecialchars($result['error']) ?></div>
  <?php endif; ?>

  <?php if ($steps): ?>
  <ul class="steps">
    <?php foreach ($steps as $s): ?>
    <li><span class="step-ok">✓</span><?= htmlspecialchars($s[1]) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <div class="btn-row">
    <a class="btn-secondary" href="index.php">← Volver al manager</a>
    <?php if (empty($result['ok'])): ?>
    <a class="btn-secondary" href="sync.php">↻ Reintentar</a>
    <?php endif; ?>
  </div>

<?php else: ?>

  <h1 class="card-title">Sincronizar con el servidor</h1>
  <p class="card-desc">Descarga los últimos contenidos del servidor web (~144 MB) y los aplica al enrutador local. El proceso tarda entre 1 y 5 minutos según la velocidad de internet — hazlo antes de llegar a la feria.</p>
  <div class="info-box">
    ✓ Los <strong>usuarios</strong> y <strong>leads</strong> capturados localmente se preservarán.<br>
    ✓ Los parches HTTP se aplicarán automáticamente.<br>
    ✓ Los permisos de archivos se corregirán.
  </div>

  <form method="post" onsubmit="document.getElementById('btn-do-sync').disabled=true;document.getElementById('btn-do-sync').textContent='Sincronizando…';">
    <input type="hidden" name="csrf"   value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="sync">
    <button type="submit" class="btn-sync" id="btn-do-sync">↻ Iniciar sincronización</button>
  </form>

  <div class="btn-row" style="margin-top:16px">
    <a class="btn-secondary" href="index.php">← Cancelar</a>
  </div>

<?php endif; ?>

  </div>
</div>
</body>
</html>
