<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();

$data     = load_data();
$isAdmin  = ($_SESSION['manager_role'] ?? '') === 'admin';
$me       = $_SESSION['manager_user'] ?? '';
$csrf     = csrf_token();
$users    = $isAdmin ? load_users() : [];
// Strip hashes before embedding in page
$safeUsers = array_map(function($u){ unset($u['password_hash']); return $u; }, $users);
$leads       = $isAdmin ? load_leads() : [];
$is_router   = ($_SERVER['SERVER_PORT'] ?? '') === '9090';
$site_active = file_exists(SITE_ACTIVE_FILE);

function manager_catalog_diag(string $brand): array {
    $root = dirname(__DIR__);
    $path = __DIR__ . "/{$brand}_catalog.json";
    $cat = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    $cat = is_array($cat) ? $cat : [];
    $missing = [];
    $check = function($rel) use ($root, &$missing) {
        if (!is_string($rel) || trim($rel) === '' || preg_match('#^https?://#', $rel)) return;
        $clean = rawurldecode(ltrim(strtok($rel, '?'), '/'));
        if (!file_exists($root . '/' . $clean)) $missing[] = $rel;
    };
    foreach (($cat['products'] ?? []) as $p) $check($p['img'] ?? '');
    foreach (($cat['accessories'] ?? []) as $a) {
        $check($a['img'] ?? '');
        $check($a['datasheet'] ?? '');
    }
    foreach (['datasheets','hardwareGuides','installGuides'] as $key) {
        foreach (($cat[$key] ?? []) as $v) {
            $vals = is_array($v) && array_is_list($v) ? $v : [$v];
            foreach ($vals as $doc) {
                if (is_array($doc)) {
                    foreach (['es','en','pt'] as $l) $check($doc[$l] ?? '');
                }
            }
        }
    }
    return [
        'json' => file_exists($path),
        'products' => count($cat['products'] ?? []),
        'accessories' => count($cat['accessories'] ?? []),
        'has_hero' => isset($cat['hero'], $cat['closing']),
        'missing_assets' => array_values(array_unique($missing)),
    ];
}

$sw_content = @file_get_contents(dirname(__DIR__) . '/sw.js') ?: '';
preg_match("/CACHE_VERSION\s*=\s*'([^']+)'/", $sw_content, $sw_match);
$sync_status = file_exists(SYNC_STATUS_FILE) ? (json_decode(file_get_contents(SYNC_STATUS_FILE), true) ?: []) : [];
$settings_file = __DIR__ . '/settings.json';
$settings  = file_exists($settings_file) ? (json_decode(file_get_contents($settings_file), true) ?? []) : [];
$docs_code = $settings['docs_access_code'] ?? '';

$diag = [
    'is_router' => $is_router,
    'site_active' => $site_active,
    'live_url' => LIVE_SERVER_URL,
    'sync_last_ok' => $sync_status['last_ok'] ?? null,
    'sync_archive_kb' => $sync_status['archive_kb'] ?? null,
    'cache_version' => $sw_match[1] ?? 'desconocida',
    'files' => [
        'sw' => file_exists(dirname(__DIR__) . '/sw.js'),
        'data' => file_exists(dirname(__DIR__) . '/data.js'),
        'viachain_data' => file_exists(dirname(__DIR__) . '/viachain_data.js'),
        'skywave_html' => file_exists(dirname(__DIR__) . '/Portafolio Skywave.html'),
        'viachain_html' => file_exists(dirname(__DIR__) . '/Portafolio Viachain.html'),
    ],
    'catalogs' => [
        'skywave' => manager_catalog_diag('skywave'),
        'viachain' => manager_catalog_diag('viachain'),
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title>Manager · Dashboard</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#F0F2F5;color:#111;font-size:14px}
/* Header */
.hdr{background:#fff;border-bottom:1px solid #E5E7EB;height:56px;display:flex;align-items:center;
  padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.hdr-brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;color:#0D0A07}
.hdr-dot{width:22px;height:22px;background:#BA0C2F;border-radius:5px;flex-shrink:0}
.hdr-sep{width:1px;height:24px;background:#E5E7EB}
.hdr-user{font-size:12px;color:#666;margin-left:auto}
.btn-logout{font-size:12px;color:#BA0C2F;text-decoration:none;font-weight:600;padding:5px 10px;
  border-radius:5px;border:1px solid #f5c0ca;background:#fff5f6;cursor:pointer}
.btn-logout:hover{background:#fef2f2}
.btn-sync{font-size:12px;color:#0369A1;text-decoration:none;font-weight:600;padding:5px 10px;
  border-radius:5px;border:1px solid #BAE6FD;background:#EFF6FF;cursor:pointer}
.btn-sync:hover{background:#DBEAFE}
/* Site on/off toggle */
.site-widget{display:flex;align-items:center;gap:8px;padding:5px 12px;border-radius:6px;
  border:1px solid #E5E7EB;background:#F9FAFB}
.site-indicator{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;white-space:nowrap}
.site-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.site-dot.on{background:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.2)}
.site-dot.off{background:#9CA3AF}
.site-label-on{color:#15803d}
.site-label-off{color:#6B7280}
.btn-site{font-size:11px;font-weight:700;padding:4px 11px;border-radius:4px;cursor:pointer;
  letter-spacing:.3px;border:none;transition:background .15s;white-space:nowrap}
.btn-site.activate{background:#BA0C2F;color:#fff}
.btn-site.activate:hover{background:#9a0a27}
.btn-site.deactivate{background:#F3F4F6;color:#374151;border:1px solid #D1D5DB}
.btn-site.deactivate:hover{background:#E5E7EB}
/* Layout */
.wrap{max-width:1100px;margin:0 auto;padding:24px 16px}
/* Tabs */
.tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #E5E7EB;padding-bottom:0}
.tab-btn{padding:10px 20px;background:none;border:none;cursor:pointer;font-size:13px;font-weight:600;
  color:#666;letter-spacing:.3px;border-bottom:2px solid transparent;margin-bottom:-2px;transition:color .15s}
.tab-btn.active{color:#BA0C2F;border-bottom-color:#BA0C2F}
.tab-btn:hover:not(.active){color:#333}
/* Section header */
.sec-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.sec-hdr h2{font-size:16px;font-weight:700;color:#0D0A07}
.btn-add{background:#BA0C2F;color:#fff;border:none;border-radius:6px;padding:8px 18px;
  font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;transition:background .15s}
.btn-add:hover{background:#9a0a27}
/* Table */
.tbl-wrap{background:#fff;border-radius:10px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden}
table{width:100%;border-collapse:collapse}
th{background:#F8F9FA;font-size:11px;font-weight:700;color:#555;text-transform:uppercase;
  letter-spacing:.5px;padding:10px 16px;text-align:left;border-bottom:1px solid #E5E7EB}
td{padding:12px 16px;border-bottom:1px solid #F3F4F6;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#FAFAFA}
.badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;
  letter-spacing:.3px;text-transform:uppercase}
.badge-sky{background:#EFF6FF;color:#1E40AF}
.badge-via{background:#F0FDF4;color:#166534}
.badge-orb{background:#FFF7ED;color:#9A3412}
.badge-on{background:#F0FDF4;color:#166534}
.badge-off{background:#F9FAFB;color:#6B7280}
.thumb{width:48px;height:36px;object-fit:cover;border-radius:4px;border:1px solid #E5E7EB}
.thumb-ph{width:48px;height:36px;background:#F3F4F6;border-radius:4px;border:1px solid #E5E7EB;
  display:flex;align-items:center;justify-content:center;color:#CCC;font-size:18px}
/* Row actions */
.row-actions{display:flex;gap:8px}
.btn-edit,.btn-del{padding:4px 12px;border-radius:5px;font-size:12px;font-weight:600;
  cursor:pointer;transition:background .15s;border:1px solid}
.btn-edit{background:#F0F9FF;color:#0369A1;border-color:#BAE6FD}
.btn-edit:hover{background:#E0F2FE}
.btn-del{background:#FFF5F5;color:#B91C1C;border-color:#FECACA}
.btn-del:hover{background:#FEF2F2}
/* Empty state */
.empty{padding:48px;text-align:center;color:#9CA3AF;font-size:14px}
.empty-icon{font-size:36px;margin-bottom:8px}
/* Modal */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;
  display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
.modal-backdrop.hidden{display:none}
.modal{background:#fff;border-radius:12px;box-shadow:0 8px 40px rgba(0,0,0,.18);
  width:100%;max-width:620px;overflow:hidden;flex-shrink:0}
.modal-hdr{padding:20px 24px 16px;border-bottom:1px solid #E5E7EB;display:flex;
  align-items:center;justify-content:space-between}
.modal-hdr h3{font-size:15px;font-weight:700;color:#0D0A07}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:#888;line-height:1;
  padding:2px 6px;border-radius:4px}
.modal-close:hover{background:#F3F4F6;color:#333}
.modal-body{padding:20px 24px}
.modal-footer{padding:16px 24px;border-top:1px solid #E5E7EB;display:flex;
  align-items:center;justify-content:flex-end;gap:10px}
/* Form */
.form-row{margin-bottom:16px}
.form-row label{display:block;font-size:11px;font-weight:700;color:#555;
  text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
input[type=text],input[type=date],input[type=password],select,textarea{
  width:100%;padding:9px 12px;border:1px solid #D1D5DB;border-radius:6px;
  font-size:13px;font-family:inherit;background:#fff;transition:border-color .15s}
input:focus,select:focus,textarea:focus{outline:none;border-color:#BA0C2F}
textarea{resize:vertical;min-height:80px}
.form-2col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-check{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#333}
.form-check input[type=checkbox]{width:16px;height:16px;accent-color:#BA0C2F;cursor:pointer}
/* Language tabs */
.lang-tabs{display:flex;gap:4px;margin-bottom:12px}
.lang-tab{padding:5px 14px;border-radius:20px;border:1px solid #D1D5DB;background:#fff;
  font-size:12px;font-weight:600;cursor:pointer;letter-spacing:.5px;transition:all .15s}
.lang-tab.active{background:#BA0C2F;color:#fff;border-color:#BA0C2F}
/* Upload field */
.upload-row{display:flex;gap:8px;align-items:center}
.upload-row input[type=text]{flex:1}
.btn-upload{padding:8px 14px;background:#F3F4F6;border:1px solid #D1D5DB;border-radius:6px;
  font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;transition:background .15s}
.btn-upload:hover{background:#E5E7EB}
.upload-status{font-size:11px;color:#6B7280;margin-top:4px}
.upload-status.ok{color:#166534}
.upload-status.err{color:#B91C1C}
/* Preview image */
.img-preview{width:80px;height:60px;object-fit:cover;border-radius:5px;
  border:1px solid #E5E7EB;margin-top:6px;display:block}
/* Confirm overlay */
.confirm-box{background:#fff;border-radius:10px;padding:24px;max-width:360px;
  box-shadow:0 4px 24px rgba(0,0,0,.15);margin:auto;margin-top:80px}
.confirm-box h3{font-size:15px;font-weight:700;margin-bottom:8px;color:#0D0A07}
.confirm-box p{font-size:13px;color:#555;margin-bottom:20px}
.confirm-btns{display:flex;gap:10px;justify-content:flex-end}
.btn-cancel{padding:8px 18px;border-radius:6px;border:1px solid #D1D5DB;background:#fff;
  font-size:13px;font-weight:600;cursor:pointer;color:#555}
.btn-cancel:hover{background:#F3F4F6}
.btn-confirm-del{padding:8px 18px;border-radius:6px;border:none;background:#DC2626;
  color:#fff;font-size:13px;font-weight:600;cursor:pointer}
.btn-confirm-del:hover{background:#B91C1C}
.btn-save{background:#BA0C2F;color:#fff;border:none;border-radius:6px;padding:9px 22px;
  font-size:13px;font-weight:600;cursor:pointer;transition:background .15s}
.btn-save:hover{background:#9a0a27}
.btn-save:disabled{opacity:.6;cursor:not-allowed}
.error-msg{background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;padding:10px 14px;
  border-radius:6px;font-size:13px;margin-bottom:14px}
.diag-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
.diag-card{background:#fff;border-radius:10px;box-shadow:0 1px 8px rgba(0,0,0,.07);padding:18px}
.diag-title{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#6B7280;font-weight:800;margin-bottom:8px}
.diag-value{font-size:20px;font-weight:750;color:#111827}
.diag-note{font-size:12px;color:#6B7280;line-height:1.5;margin-top:6px}
.check-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.check-item{background:#fff;border:1px solid #E5E7EB;border-radius:8px;padding:12px 14px;display:flex;gap:10px;align-items:flex-start}
.check-mark{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}
.check-mark.ok{background:#DCFCE7;color:#166534}.check-mark.warn{background:#FEF3C7;color:#92400E}.check-mark.err{background:#FEE2E2;color:#991B1B}
.diag-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
</style>
</head>
<body>

<div class="hdr">
  <div class="hdr-brand"><div class="hdr-dot"></div>Portfolio Manager</div>
  <div class="hdr-sep"></div>
  <span style="font-size:12px;color:#888">LATAM 2026</span>
  <span class="hdr-user">@<?= htmlspecialchars($me) ?> &middot; <?= $isAdmin ? 'Admin' : 'Editor' ?></span>
  <?php if ($isAdmin && $is_router): ?>
  <a class="btn-sync" href="sync.php">↻ Sincronizar</a>
  <?php endif; ?>

  <!-- Site on/off toggle -->
  <div class="site-widget" id="site-widget">
    <div class="site-indicator">
      <span class="site-dot <?= $site_active ? 'on' : 'off' ?>" id="site-dot"></span>
      <span id="site-label" class="<?= $site_active ? 'site-label-on' : 'site-label-off' ?>">
        <?= $site_active ? 'EN VIVO' : 'APAGADO' ?>
      </span>
    </div>
    <button id="btn-site-toggle"
            class="btn-site <?= $site_active ? 'deactivate' : 'activate' ?>"
            onclick="toggleSite()">
      <?= $site_active ? 'Apagar' : 'Activar' ?>
    </button>
  </div>

  <a class="btn-logout" href="logout.php">Cerrar sesión</a>
</div>

<div class="wrap">
  <div class="tabs" id="tab-bar">
    <button class="tab-btn active" data-tab="casestudies" onclick="switchTab('casestudies')">Casos de Estudio</button>
    <button class="tab-btn" data-tab="gallery" onclick="switchTab('gallery')">Galería</button>
    <button class="tab-btn" data-tab="videos" onclick="switchTab('videos')">Videos</button>
    <?php if ($isAdmin): ?>
    <button class="tab-btn" data-tab="users" onclick="switchTab('users')">Usuarios</button>
    <button class="tab-btn" data-tab="leads" onclick="switchTab('leads')">Leads<?php if (count($leads)>0): ?> <span id="leads-count-badge" style="background:#BA0C2F;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?= count($leads) ?></span><?php endif; ?></button>
    <button class="tab-btn" data-tab="catalog" onclick="switchTab('catalog')">Catálogo</button>
    <button class="tab-btn" data-tab="diagnostics" onclick="switchTab('diagnostics')">Estado</button>
    <button class="tab-btn" data-tab="settings" onclick="switchTab('settings')">Configuración</button>
    <?php endif; ?>
  </div>
  <div id="tab-content"></div>
</div>

<div class="modal-backdrop hidden" id="modal-backdrop" onclick="handleBackdropClick(event)">
  <div class="modal" id="modal-box"></div>
</div>

<script>
const CSRF       = <?= json_encode($csrf) ?>;
const IS_ADMIN   = <?= $isAdmin ? 'true' : 'false' ?>;
const ME         = <?= json_encode($me) ?>;
let   SITE_ACTIVE = <?= $site_active ? 'true' : 'false' ?>;
const INDUSTRIES = <?= json_encode(INDUSTRIES) ?>;
const BRANDS     = <?= json_encode(BRANDS) ?>;
const TERMINALS  = <?= json_encode(TERMINALS) ?>;
const DIAG       = <?= json_encode($diag, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES) ?>;
const DOCS_CODE  = <?= json_encode($docs_code) ?>;

async function toggleSite() {
  const turning_on = !SITE_ACTIVE;
  const msg = turning_on
    ? '¿Activar el portafolio? Será visible para todos los visitantes.'
    : '¿Apagar el portafolio? Los visitantes verán una página de "no disponible".';
  if (!confirm(msg)) return;

  const btn = document.getElementById('btn-site-toggle');
  btn.disabled = true;

  try {
    const r = await fetch('site_toggle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf: CSRF }),
    });
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Error');

    SITE_ACTIVE = j.active;
    document.getElementById('site-dot').className   = 'site-dot ' + (j.active ? 'on' : 'off');
    document.getElementById('site-label').className = j.active ? 'site-label-on' : 'site-label-off';
    document.getElementById('site-label').textContent = j.active ? 'EN VIVO' : 'APAGADO';
    btn.className   = 'btn-site ' + (j.active ? 'deactivate' : 'activate');
    btn.textContent = j.active ? 'Apagar' : 'Activar';
  } catch(e) {
    alert('Error al cambiar estado: ' + e.message);
  } finally {
    btn.disabled = false;
  }
}

let S = {
  data:    <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS) ?>,
  users:   <?= json_encode($safeUsers, JSON_HEX_TAG | JSON_HEX_APOS) ?>,
  leads:   <?= json_encode($leads, JSON_HEX_TAG | JSON_HEX_APOS) ?>,
  tab:     'casestudies',
  modal:   null,
  formLang:'es',
};

// Cache keyed by id — safe for onclick references (no JSON in HTML attrs)
let _cache = {};
function rebuildCache(){
  _cache = {};
  [...(S.data.casestudies||[]),...(S.data.gallery||[]),...(S.data.videos||[]),...(S.users||[])]
    .forEach(x => { if (x && x.id) _cache[x.id] = x; });
}
function openEditById(type, id){
  const item = _cache[id];
  if (!item) return;
  S.modal = { type, item };
  S.formLang = 'es';
  showModal();
}

/* ─── UTILS ─── */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function brandBadge(b){
  const cls={'skywave':'badge-sky','viachain':'badge-via','orbcomm':'badge-orb'}[b]||'badge-off';
  return `<span class="badge ${cls}">${esc(BRANDS[b]||b)}</span>`;
}
function activeBadge(a){ return a ? '<span class="badge badge-on">Activo</span>' : '<span class="badge badge-off">Inactivo</span>'; }
function t(o,l){ return (o&&o[l]) || (o&&o.es) || ''; }
function industryLabel(k){ return INDUSTRIES[k]||k||'—'; }
function today(){ return new Date().toISOString().slice(0,10); }

/* ─── TAB SWITCHING ─── */
function switchTab(tab){
  S.tab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
  renderTabContent();
}

/* ─── RENDER ─── */
function renderTabContent(){
  rebuildCache();
  const el = document.getElementById('tab-content');
  switch(S.tab){
    case 'casestudies': el.innerHTML = renderCases(); break;
    case 'gallery':     el.innerHTML = renderGallery(); break;
    case 'videos':      el.innerHTML = renderVideos(); break;
    case 'users':       el.innerHTML = renderUsers(); break;
    case 'leads':       el.innerHTML = renderLeads(); break;
    case 'catalog':     el.innerHTML = renderCatalog(); break;
    case 'diagnostics': el.innerHTML = renderDiagnostics(); break;
    case 'settings':    el.innerHTML = renderSettings();    break;
  }
}

function renderCases(){
  const items = S.data.casestudies || [];
  const rows = items.length ? items.map(cs => `
    <tr>
      <td>${cs.cover ? `<img class="thumb" src="${esc(cs.cover)}" onerror="this.style.display='none'">` : '<div class="thumb-ph">—</div>'}</td>
      <td><strong>${esc(t(cs.title,'es'))}</strong><br><span style="color:#888;font-size:12px">${esc(cs.id)}</span></td>
      <td>${brandBadge(cs.brand)}</td>
      <td style="font-size:12px">${esc(industryLabel(cs.industry))}</td>
      <td style="font-size:12px">${['es','en','pt'].filter(l=>cs.pdf&&cs.pdf[l]).map(l=>l.toUpperCase()).join(' ')||'—'}</td>
      <td>${activeBadge(cs.active)}</td>
      <td style="font-size:12px;color:#888">${esc(cs.date||'')}</td>
      <td><div class="row-actions">
        <button class="btn-edit" onclick="openEditById('case','${cs.id}')">Editar</button>
        <button class="btn-del"  onclick="confirmDelete('case','${esc(cs.id)}','${esc(t(cs.title,'es'))}')">Eliminar</button>
      </div></td>
    </tr>`).join('') : `<tr><td colspan="8"><div class="empty"><div class="empty-icon">📄</div>Sin casos de estudio todavía</div></td></tr>`;
  return `<div class="sec-hdr"><h2>Casos de Estudio <span style="color:#888;font-weight:400">(${items.length})</span></h2>
    <button class="btn-add" onclick="openAdd('case')">+ Agregar caso</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Portada</th><th>Título</th><th>Marca</th><th>Industria</th><th>PDFs</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

function renderGallery(){
  const items = S.data.gallery || [];
  const rows = items.length ? items.map(g => {
    const imgs = g.images || (g.image ? [g.image] : []);
    const thumb = imgs[0] || '';
    const countBadge = imgs.length > 1 ? `<span style="font-size:10px;background:#E0E7FF;color:#3730A3;border-radius:10px;padding:1px 6px;margin-left:6px;font-weight:700">${imgs.length} fotos</span>` : '';
    return `
    <tr>
      <td>${thumb ? `<img class="thumb" src="${esc(thumb)}" onerror="this.style.display='none'">` : '<div class="thumb-ph">—</div>'}</td>
      <td><strong>${esc(t(g.title,'es'))}</strong>${countBadge}<br><span style="color:#888;font-size:12px">${esc(g.id)}</span></td>
      <td>${brandBadge(g.brand)}</td>
      <td style="font-size:12px">${esc(industryLabel(g.industry))}</td>
      <td style="font-size:12px">${esc(g.device||'—')}</td>
      <td>${activeBadge(g.active)}</td>
      <td><div class="row-actions">
        <button class="btn-edit" onclick="openEditById('gallery','${g.id}')">Editar</button>
        <button class="btn-del"  onclick="confirmDelete('gallery','${esc(g.id)}','${esc(t(g.title,'es'))}')">Eliminar</button>
      </div></td>
    </tr>`;
  }).join('') : `<tr><td colspan="7"><div class="empty"><div class="empty-icon">🖼</div>Sin imágenes en la galería todavía</div></td></tr>`;
  return `<div class="sec-hdr"><h2>Galería <span style="color:#888;font-weight:400">(${items.length})</span></h2>
    <button class="btn-add" onclick="openAdd('gallery')">+ Agregar imagen</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Imagen</th><th>Título</th><th>Marca</th><th>Industria</th><th>Dispositivo</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

function renderVideos(){
  const items = S.data.videos || [];
  const rows = items.length ? items.map(v => `
    <tr>
      <td><img class="thumb" src="https://img.youtube.com/vi/${esc(v.youtube_id)}/default.jpg" onerror="this.style.display='none'"></td>
      <td><strong>${esc(t(v.title,'es'))}</strong><br><span style="color:#888;font-size:12px">${esc(v.id)}</span></td>
      <td>${brandBadge(v.brand)}</td>
      <td style="font-size:12px;font-family:monospace">${esc(v.youtube_id)}</td>
      <td>${activeBadge(v.active)}</td>
      <td style="font-size:12px;color:#888">${esc(v.date||'')}</td>
      <td><div class="row-actions">
        <button class="btn-edit" onclick="openEditById('video','${v.id}')">Editar</button>
        <button class="btn-del"  onclick="confirmDelete('video','${esc(v.id)}','${esc(t(v.title,'es'))}')">Eliminar</button>
      </div></td>
    </tr>`).join('') : `<tr><td colspan="7"><div class="empty"><div class="empty-icon">▶️</div>Sin videos todavía</div></td></tr>`;
  return `<div class="sec-hdr"><h2>Videos <span style="color:#888;font-weight:400">(${items.length})</span></h2>
    <button class="btn-add" onclick="openAdd('video')">+ Agregar video</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Miniatura</th><th>Título</th><th>Marca</th><th>YouTube ID</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

function renderUsers(){
  if (!IS_ADMIN) return '<div class="empty">Sin acceso</div>';
  const users = S.users || [];
  const rows = users.length ? users.map(u => `
    <tr>
      <td><strong>${esc(u.username)}</strong></td>
      <td><span class="badge ${u.role==='admin'?'badge-sky':'badge-off'}">${esc(u.role)}</span></td>
      <td style="font-size:12px;color:#888">${esc(u.created||'')}</td>
      <td><div class="row-actions">
        <button class="btn-edit" onclick="openEditById('user','${u.id}')">Editar</button>
        ${u.username !== ME ? `<button class="btn-del" onclick="confirmDelete('user','${esc(u.id)}','${esc(u.username)}')">Eliminar</button>` : ''}
      </div></td>
    </tr>`).join('') : '';
  return `<div class="sec-hdr"><h2>Usuarios <span style="color:#888;font-weight:400">(${users.length})</span></h2>
    <button class="btn-add" onclick="openAdd('user')">+ Agregar usuario</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Usuario</th><th>Rol</th><th>Creado</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

function renderLeads(){
  if (!IS_ADMIN) return '<div class="empty">Sin acceso</div>';
  const leads = S.leads || [];
  const csvUrl = 'leads_export.php?csrf=' + encodeURIComponent(CSRF);
  const rows = leads.length ? leads.slice().reverse().map(l => `
    <tr>
      <td style="width:56px;padding:8px 10px">
        ${l.card_image
          ? `<a href="${esc(l.card_image)}" target="_blank" rel="noopener" title="Ver tarjeta capturada">
               <img class="thumb" src="${esc(l.card_image)}" onerror="this.style.display='none'" style="cursor:pointer">
             </a>`
          : '<div class="thumb-ph" style="font-size:13px" title="Sin foto">—</div>'}
      </td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${esc(l.name)}">
        <strong>${esc(l.name)}</strong><br>
        <span style="font-size:11px;color:#888">${esc(l.jobtitle||'')}</span>
        ${l.source === 'offline_sync' ? '<br><span style="font-size:9px;background:#FEF3C7;color:#92400E;border-radius:3px;padding:1px 5px;font-weight:600">OFFLINE</span>' : ''}
      </td>
      <td style="font-size:12px">${esc(l.company||'—')}</td>
      <td style="font-size:12px">${esc(l.email||'')}</td>
      <td style="font-size:12px;color:#888">${esc(l.phone||'—')}</td>
      <td style="font-size:11px;max-width:160px">${(l.terminals||[]).map(t=>`<span class="badge badge-off" style="margin:1px">${esc(t)}</span>`).join('')||'—'}</td>
      <td style="font-size:12px;color:#555;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${esc(l.other_interest||'')}">${esc(l.other_interest||'—')}</td>
      <td><span class="badge badge-off" style="text-transform:uppercase">${esc(l.lang||'')}</span></td>
      <td style="font-size:11px;color:#888;white-space:nowrap">${esc((l.created||'').slice(0,10))}</td>
      <td><button class="btn-del" onclick="confirmDeleteLead('${esc(l.id)}','${esc(l.name)}')">Eliminar</button></td>
    </tr>`).join('') :
    '<tr><td colspan="10" style="padding:40px;text-align:center;color:#9CA3AF">Sin leads registrados</td></tr>';
  return `<div class="sec-hdr">
    <h2>Leads <span style="color:#888;font-weight:400">(${leads.length})</span></h2>
    <div style="display:flex;gap:8px;align-items:center">
      <a href="${esc(csvUrl)}" class="btn-add" style="text-decoration:none;font-size:12px;padding:7px 14px">↓ Exportar CSV</a>
    </div></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Tarjeta</th><th>Nombre</th><th>Empresa</th><th>Email</th><th>Teléfono</th><th>Terminales</th><th>Otros</th><th>Idioma</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

function renderDiagnostics(){
  if (!IS_ADMIN) return '<div class="empty">Sin acceso</div>';
  const sw = DIAG.cache_version || 'desconocida';
  const lastSync = DIAG.sync_last_ok ? new Date(DIAG.sync_last_ok).toLocaleString() : 'Sin registro';
  const mk = (ok, label, note, warn=false) => `
    <div class="check-item">
      <span class="check-mark ${ok ? 'ok' : (warn ? 'warn' : 'err')}">${ok ? '✓' : (warn ? '!' : '×')}</span>
      <div><strong>${esc(label)}</strong><div class="diag-note">${esc(note || '')}</div></div>
    </div>`;
  const missingSky = DIAG.catalogs.skywave.missing_assets || [];
  const missingVia = DIAG.catalogs.viachain.missing_assets || [];
  const missing = missingSky.length + missingVia.length;
  const checks = [
    mk(DIAG.files.sw, 'Service worker presente', `Versión actual: ${sw}`),
    mk(DIAG.files.data && DIAG.files.viachain_data, 'Datos generados', 'data.js y viachain_data.js disponibles'),
    mk(DIAG.files.skywave_html && DIAG.files.viachain_html, 'Portafolios disponibles', 'HTML Skywave y VIACHAIN presentes'),
    mk(DIAG.catalogs.skywave.json && DIAG.catalogs.viachain.json, 'Catálogos JSON', 'Skywave y VIACHAIN cargados'),
    mk(DIAG.catalogs.skywave.has_hero && DIAG.catalogs.viachain.has_hero, 'Hero/cierre editable', 'Bloques editoriales configurados en ambos catálogos'),
    mk(missing === 0, 'Assets de catálogo', missing ? `${missing} ruta(s) no encontradas` : 'Imágenes y documentos referenciados existen', missing > 0),
    mk(DIAG.is_router, 'Modo router local', DIAG.is_router ? 'Ejecutando en puerto 9090' : 'No estás en el puerto local 9090', !DIAG.is_router),
    mk(true, 'Videos online-only', 'YouTube no se precachea ni se descarga para proteger espacio'),
  ].join('');

  const missHtml = missing ? `<div class="diag-card" style="grid-column:1/-1">
    <div class="diag-title">Rutas faltantes</div>
    <div class="diag-note">${[...missingSky, ...missingVia].slice(0,18).map(esc).join('<br>')}${missing>18?'<br>…':''}</div>
  </div>` : '';

  return `<div class="sec-hdr">
    <h2>Estado operativo y checklist de feria</h2>
  </div>
  <div class="diag-grid">
    <div class="diag-card"><div class="diag-title">Sitio local</div><div class="diag-value">${SITE_ACTIVE ? 'EN VIVO' : 'APAGADO'}</div><div class="diag-note">Controla la visibilidad del portafolio en este servidor.</div></div>
    <div class="diag-card"><div class="diag-title">Último sync</div><div class="diag-value" style="font-size:16px">${esc(lastSync)}</div><div class="diag-note">${DIAG.sync_archive_kb ? esc(DIAG.sync_archive_kb + ' KB descargados') : 'Aún no hay sincronización exitosa registrada.'}</div></div>
    <div class="diag-card"><div class="diag-title">Sitio principal</div><div class="diag-value" id="sync-probe-value" style="font-size:16px">${esc(DIAG.live_url)}</div>
      <div class="diag-actions">
        <button class="btn-sync" type="button" onclick="probeSync()">Probar conexión</button>
        ${DIAG.is_router ? '<a class="btn-sync" style="text-decoration:none" href="sync.php">Sincronizar ahora</a>' : ''}
      </div>
      <div class="diag-note" id="sync-probe-note">Prueba el acceso al paquete de sincronización.</div>
    </div>
  </div>
  <div class="check-list">${checks}</div>
  ${missHtml}`;
}

async function probeSync(){
  const value = document.getElementById('sync-probe-value');
  const note = document.getElementById('sync-probe-note');
  value.textContent = 'Probando...';
  note.textContent = 'Consultando sitio principal.';
  try {
    const r = await fetch('sync_status.php?csrf=' + encodeURIComponent(CSRF));
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Error');
    value.textContent = j.reachable ? 'ALCANZABLE' : 'SIN CONEXIÓN';
    value.style.color = j.reachable ? '#166534' : '#991B1B';
    note.textContent = j.reachable ? `HTTP ${j.status} · ${j.url}` : (j.error || `HTTP ${j.status || 'sin respuesta'}`);
  } catch(e) {
    value.textContent = 'ERROR';
    value.style.color = '#991B1B';
    note.textContent = e.message;
  }
}

async function confirmDeleteLead(id, label){
  document.getElementById('modal-box').innerHTML = `
    <div class="confirm-box" style="margin:0;border-radius:0;box-shadow:none">
      <h3>¿Confirmar eliminación?</h3>
      <p>Se eliminará el lead: <strong>${esc(label)}</strong></p>
      <div class="confirm-btns">
        <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
        <button class="btn-confirm-del" onclick="doDeleteLead('${esc(id)}')">Eliminar</button>
      </div>
    </div>`;
  document.getElementById('modal-backdrop').classList.remove('hidden');
}

async function doDeleteLead(id){
  try {
    const r = await fetch('api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'delete_lead', id, csrf: CSRF })
    });
    const j = await r.json();
    if (j.ok) {
      S.leads = j.leads;
      closeModal();
      renderTabContent();
    } else { alert('Error: ' + j.error); }
  } catch(e) { alert('Error de red: ' + e.message); }
}

/* ─── MODAL ─── */
function openAdd(type){
  S.modal = { type, item: null };
  S.formLang = 'es';
  showModal();
}

function openEdit(type, jsonStr){
  S.modal = { type, item: JSON.parse(jsonStr) };
  S.formLang = 'es';
  showModal();
}

function showModal(){
  const { type, item } = S.modal;
  const isEdit = !!item;
  const titles = { case:'Caso de Estudio', gallery:'Imagen de Galería', video:'Video', user:'Usuario' };
  const title = (isEdit ? 'Editar ' : 'Agregar ') + titles[type];

  let body = '';
  let errorHtml = '';

  if (type === 'case')    body = formCase(item);
  if (type === 'gallery') body = formGallery(item);
  if (type === 'video')   body = formVideo(item);
  if (type === 'user')    body = formUser(item);

  document.getElementById('modal-box').innerHTML = `
    <div class="modal-hdr">
      <h3>${esc(title)}</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body">
      <div id="form-error" class="error-msg" style="display:none"></div>
      ${body}
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
      <button class="btn-save" id="btn-save" onclick="saveForm()">Guardar</button>
    </div>`;

  document.getElementById('modal-backdrop').classList.remove('hidden');
}

function closeModal(){
  S.modal = null;
  document.getElementById('modal-backdrop').classList.add('hidden');
}

function handleBackdropClick(e){
  if (e.target === document.getElementById('modal-backdrop')) closeModal();
}

/* ─── LANGUAGE TABS IN FORMS ─── */
function switchFormLang(lang){
  S.formLang = lang;
  document.querySelectorAll('.lang-tab').forEach(b => b.classList.toggle('active', b.dataset.lang === lang));
  document.querySelectorAll('.lang-panel').forEach(p => p.style.display = p.dataset.lang === lang ? 'block' : 'none');
}

function langTabs(){
  return `<div class="lang-tabs">
    <button class="lang-tab ${S.formLang==='es'?'active':''}" data-lang="es" onclick="switchFormLang('es')">ES</button>
    <button class="lang-tab ${S.formLang==='en'?'active':''}" data-lang="en" onclick="switchFormLang('en')">EN</button>
    <button class="lang-tab ${S.formLang==='pt'?'active':''}" data-lang="pt" onclick="switchFormLang('pt')">PT</button>
  </div>`;
}

function langPanels(item, hasdesc){
  return ['es','en','pt'].map(l => `
    <div class="lang-panel" data-lang="${l}" style="display:${l===S.formLang?'block':'none'}">
      <div class="form-row">
        <label>Título (${l.toUpperCase()})</label>
        <input type="text" id="f-title-${l}" value="${esc(item&&item.title?item.title[l]:'')}">
      </div>
      ${hasdesc?`<div class="form-row">
        <label>Descripción (${l.toUpperCase()})</label>
        <textarea id="f-desc-${l}">${esc(item&&item.description?item.description[l]:'')}</textarea>
      </div>`:''}
    </div>`).join('');
}

function captionPanels(item){
  return ['es','en','pt'].map(l => `
    <div class="lang-panel" data-lang="${l}" style="display:${l===S.formLang?'block':'none'}">
      <div class="form-row">
        <label>Título (${l.toUpperCase()})</label>
        <input type="text" id="f-title-${l}" value="${esc(item&&item.title?item.title[l]:'')}">
      </div>
      <div class="form-row">
        <label>Descripción / Pie de foto (${l.toUpperCase()})</label>
        <textarea id="f-caption-${l}">${esc(item&&item.caption?item.caption[l]:'')}</textarea>
      </div>
    </div>`).join('');
}

/* ─── FORM BUILDERS ─── */
function brandSelect(val){
  const opts = Object.entries(BRANDS).map(([k,v]) => `<option value="${k}" ${val===k?'selected':''}>${v}</option>`).join('');
  return `<select id="f-brand">${opts}</select>`;
}

function industrySelect(val, withBlank){
  const blank = withBlank ? `<option value="">— Todas —</option>` : '';
  const opts = Object.entries(INDUSTRIES).map(([k,v]) => `<option value="${k}" ${val===k?'selected':''}>${v}</option>`).join('');
  return `<select id="f-industry">${blank}${opts}</select>`;
}

function uploadField(id, label, currentUrl, fileType, accept){
  const preview = currentUrl && fileType==='img' ? `<img class="img-preview" src="${esc(currentUrl)}" id="fp-${id}">` : '';
  const urlVal  = currentUrl || '';
  return `<div class="form-row">
    <label>${esc(label)}</label>
    <div class="upload-row">
      <input type="text" id="f-${id}" value="${esc(urlVal)}" placeholder="${fileType==='doc'?'URL del PDF':'URL de la imagen'}">
      <button type="button" class="btn-upload" onclick="document.getElementById('fu-${id}').click()">Subir archivo</button>
      <input type="file" id="fu-${id}" accept="${accept}" style="display:none" onchange="handleUpload(this,'${id}','${fileType}')">
    </div>
    <div class="upload-status" id="us-${id}"></div>
    ${preview}
  </div>`;
}

function formCase(cs){
  const active = cs ? cs.active : true;
  return `
    <div class="form-2col">
      <div class="form-row"><label>Marca</label>${brandSelect(cs?cs.brand:'skywave')}</div>
      <div class="form-row"><label>Industria</label>${industrySelect(cs?cs.industry:'','')}</div>
    </div>
    <div class="form-2col">
      <div class="form-row"><label>Fecha</label>
        <input type="date" id="f-date" value="${esc(cs?cs.date:today())}">
      </div>
      <div class="form-row" style="display:flex;align-items:flex-end;padding-bottom:2px">
        <label class="form-check">
          <input type="checkbox" id="f-active" ${active?'checked':''}>Activo (visible al público)
        </label>
      </div>
    </div>
    ${langTabs()}
    ${langPanels(cs, true)}
    ${uploadField('cover','Imagen de portada',cs?cs.cover:'','img','image/*')}
    <div style="margin-top:4px;font-size:12px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">PDFs por idioma</div>
    ${uploadField('pdf-es','PDF Español',cs&&cs.pdf?cs.pdf.es:'','doc','.pdf')}
    ${uploadField('pdf-en','PDF Inglés',cs&&cs.pdf?cs.pdf.en:'','doc','.pdf')}
    ${uploadField('pdf-pt','PDF Portugués',cs&&cs.pdf?cs.pdf.pt:'','doc','.pdf')}
  `;
}

function imgSlotHTML(i, url, visible){
  const req = i === 0;
  const prev = url ? `<img class="img-preview" id="fp-img-${i}" src="${esc(url)}">` : '';
  return `<div class="img-slot" id="img-slot-${i}" style="${visible?'':'display:none'}">
    <div style="display:flex;align-items:center;margin-bottom:5px">
      <span style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px">Imagen ${i+1}${req?' *':' (opcional)'}</span>
      ${i>0?`<button type="button" onclick="hideImgSlot(${i})" style="margin-left:auto;font-size:11px;color:#B91C1C;background:none;border:none;cursor:pointer;padding:0 4px">✕ Quitar</button>`:''}
    </div>
    <div class="upload-row">
      <input type="text" id="f-img-${i}" value="${esc(url)}" placeholder="URL de la imagen">
      <button type="button" class="btn-upload" onclick="document.getElementById('fu-img-${i}').click()">Subir</button>
      <input type="file" id="fu-img-${i}" accept="image/*" style="display:none" onchange="handleUpload(this,'img-${i}','img')">
    </div>
    <div class="upload-status" id="us-img-${i}"></div>
    ${prev}
  </div>`;
}

function formGallery(g){
  const imgs = g ? (g.images || (g.image ? [g.image] : [])) : [];
  const visible = Math.max(1, imgs.length);
  const slots = Array.from({length:5}, (_,i) => imgSlotHTML(i, imgs[i]||'', i < visible)).join('');
  return `
    <div class="form-2col">
      <div class="form-row"><label>Marca</label>${brandSelect(g?g.brand:'skywave')}</div>
      <div class="form-row"><label>Industria</label>${industrySelect(g?g.industry:'','')}</div>
    </div>
    <div class="form-row"><label>Dispositivo / Equipo</label>
      <input type="text" id="f-device" value="${esc(g?g.device:'')}" placeholder="Ej. ST8100, PT6100">
    </div>
    <div class="form-row">
      <label class="form-check">
        <input type="checkbox" id="f-active" ${(g?g.active:true)?'checked':''}>Activo (visible al público)
      </label>
    </div>
    ${langTabs()}
    ${captionPanels(g)}
    <div id="img-slots" style="display:flex;flex-direction:column;gap:14px">${slots}</div>
    <button type="button" id="btn-add-img" onclick="showNextImgSlot()"
      style="margin-top:10px;${visible>=5?'display:none':''}font-size:12px;color:#BA0C2F;
        background:#FFF5F6;border:1px dashed #FECACA;border-radius:6px;padding:7px 16px;
        cursor:pointer;font-weight:600;width:100%">+ Agregar otra imagen (máx 5)</button>
  `;
}

function formVideo(v){
  return `
    <div class="form-2col">
      <div class="form-row"><label>Marca</label>${brandSelect(v?v.brand:'skywave')}</div>
      <div class="form-row"><label>Fecha</label>
        <input type="date" id="f-date" value="${esc(v?v.date:today())}">
      </div>
    </div>
    <div class="form-row"><label>YouTube ID *</label>
      <input type="text" id="f-youtube-id" value="${esc(v?v.youtube_id:'')}" placeholder="Ej. dQw4w9WgXcQ">
    </div>
    <div class="form-row">
      <label class="form-check">
        <input type="checkbox" id="f-active" ${(v?v.active:true)?'checked':''}>Activo (visible al público)
      </label>
    </div>
    ${langTabs()}
    ${langPanels(v, true)}
  `;
}

function formUser(u){
  return `
    <div class="form-2col">
      <div class="form-row"><label>Usuario *</label>
        <input type="text" id="f-username" value="${esc(u?u.username:'')}" placeholder="letras, números, _">
      </div>
      <div class="form-row"><label>Rol</label>
        <select id="f-role">
          <option value="editor" ${(!u||u.role==='editor')?'selected':''}>Editor</option>
          <option value="admin"  ${(u&&u.role==='admin')?'selected':''}>Admin</option>
        </select>
      </div>
    </div>
    <div class="form-2col">
      <div class="form-row"><label>${u?'Nueva contraseña (dejar vacío para no cambiar)':'Contraseña *'}</label>
        <input type="password" id="f-pass" placeholder="${u?'Opcional':'Mínimo 8 caracteres'}">
      </div>
      <div class="form-row"><label>Confirmar contraseña</label>
        <input type="password" id="f-pass2" placeholder="Repetir contraseña">
      </div>
    </div>
  `;
}

/* ─── GALLERY MULTI-IMAGE SLOTS ─── */
function showNextImgSlot(){
  for (let i = 1; i < 5; i++){
    const s = document.getElementById('img-slot-' + i);
    if (s && s.style.display === 'none'){
      s.style.display = 'block';
      const allVisible = Array.from({length:5}, (_,j) => document.getElementById('img-slot-'+j)).every(x => x && x.style.display !== 'none');
      if (allVisible) document.getElementById('btn-add-img').style.display = 'none';
      return;
    }
  }
}
function hideImgSlot(i){
  const s = document.getElementById('img-slot-' + i);
  if (s) s.style.display = 'none';
  const f = document.getElementById('f-img-' + i);
  if (f) f.value = '';
  const p = document.getElementById('fp-img-' + i);
  if (p) p.remove();
  const addBtn = document.getElementById('btn-add-img');
  if (addBtn) addBtn.style.display = 'block';
}

/* ─── FILE UPLOAD ─── */
function setImgPreview(fieldId, url){
  const prev = document.getElementById('fp-' + fieldId);
  if (prev) { prev.src = url; return; }
  const status = document.getElementById('us-' + fieldId);
  if (!status) return;
  const img = document.createElement('img');
  img.id = 'fp-' + fieldId;
  img.className = 'img-preview';
  img.src = url;
  status.parentElement.appendChild(img);
}

async function handleUpload(input, fieldId, type){
  if (!input.files[0]) return;
  const errEl = document.getElementById('form-error');
  if (errEl) errEl.style.display = 'none';
  const status = document.getElementById('us-' + fieldId);
  status.textContent = 'Subiendo...';
  status.className = 'upload-status';
  try {
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('type', type);
    fd.append('csrf', CSRF);
    const r = await fetch('upload.php', { method:'POST', body:fd });
    const j = await r.json();
    if (j.ok) {
      document.getElementById('f-' + fieldId).value = j.url;
      status.className = 'upload-status ok';

      if (type === 'img') {
        status.textContent = '✓ ' + j.name;
        setImgPreview(fieldId, j.url);
      } else if (type === 'doc') {
        // Auto-fill cover with generated thumbnail if cover is empty
        if (j.thumb_url) {
          status.textContent = '✓ ' + j.name + ' · miniatura generada';
          const coverField = document.getElementById('f-cover');
          if (coverField && !coverField.value.trim()) {
            coverField.value = j.thumb_url;
            setImgPreview('cover', j.thumb_url);
            const coverStatus = document.getElementById('us-cover');
            if (coverStatus) { coverStatus.textContent = '✓ Miniatura generada automáticamente del PDF'; coverStatus.className = 'upload-status ok'; }
          }
        } else {
          status.textContent = '✓ ' + j.name;
        }
      }
    } else {
      status.textContent = '✗ ' + j.error;
      status.className = 'upload-status err';
    }
  } catch(e) {
    status.textContent = '✗ Error de red';
    status.className = 'upload-status err';
  }
}

/* ─── SAVE FORM ─── */
function v(id){ const el = document.getElementById(id); return el ? el.value.trim() : ''; }
function vchk(id){ const el = document.getElementById(id); return el ? el.checked : false; }

function requireTitle(){
  if (v('f-title-es')) return true;
  // Switch to ES tab and focus the title field to guide the user
  switchFormLang('es');
  const titleEl = document.getElementById('f-title-es');
  if (titleEl){ titleEl.focus(); titleEl.style.borderColor='#DC2626'; setTimeout(()=>{ titleEl.style.borderColor=''; },3000); }
  showFormError('Escribe el título en español (tab ES)');
  return false;
}

async function saveForm(){
  const { type, item } = S.modal;
  const saveBtn = document.getElementById('btn-save');
  const errEl   = document.getElementById('form-error');
  errEl.style.display = 'none';

  let payload = { csrf: CSRF };
  if (item) payload.id = item.id;

  if (type === 'case'){
    payload.action    = 'save_case';
    payload.brand     = v('f-brand');
    payload.industry  = v('f-industry');
    payload.date      = v('f-date');
    payload.active    = vchk('f-active');
    payload.title_es  = v('f-title-es');
    payload.title_en  = v('f-title-en');
    payload.title_pt  = v('f-title-pt');
    payload.desc_es   = v('f-desc-es');
    payload.desc_en   = v('f-desc-en');
    payload.desc_pt   = v('f-desc-pt');
    payload.cover     = v('f-cover');
    payload.pdf_es    = v('f-pdf-es');
    payload.pdf_en    = v('f-pdf-en');
    payload.pdf_pt    = v('f-pdf-pt');
    if (!requireTitle()) return;
  } else if (type === 'gallery'){
    payload.action    = 'save_gallery';
    payload.brand     = v('f-brand');
    payload.industry  = v('f-industry');
    payload.device    = v('f-device');
    payload.active    = vchk('f-active');
    payload.title_es  = v('f-title-es');
    payload.title_en  = v('f-title-en');
    payload.title_pt  = v('f-title-pt');
    payload.caption_es= v('f-caption-es');
    payload.caption_en= v('f-caption-en');
    payload.caption_pt= v('f-caption-pt');
    payload.images = [];
    for (let i = 0; i < 5; i++){
      const slot = document.getElementById('img-slot-' + i);
      if (slot && slot.style.display !== 'none'){
        const url = v('f-img-' + i);
        if (url) payload.images.push(url);
      }
    }
    if (!requireTitle()) return;
    if (!payload.images.length) { showFormError('La imagen 1 es obligatoria'); return; }
  } else if (type === 'video'){
    payload.action      = 'save_video';
    payload.brand       = v('f-brand');
    payload.date        = v('f-date');
    payload.youtube_id  = v('f-youtube-id');
    payload.active      = vchk('f-active');
    payload.title_es    = v('f-title-es');
    payload.title_en    = v('f-title-en');
    payload.title_pt    = v('f-title-pt');
    payload.desc_es     = v('f-desc-es');
    payload.desc_en     = v('f-desc-en');
    payload.desc_pt     = v('f-desc-pt');
    if (!requireTitle()) return;
    if (!payload.youtube_id) { showFormError('El ID de YouTube es obligatorio'); return; }
  } else if (type === 'user'){
    payload.action   = 'save_user';
    payload.username = v('f-username');
    payload.role     = v('f-role');
    payload.password = v('f-pass');
    const pass2 = v('f-pass2');
    if (payload.password !== pass2) { showFormError('Las contraseñas no coinciden'); return; }
    if (!payload.username) { showFormError('El nombre de usuario es obligatorio'); return; }
  }

  saveBtn.disabled = true;
  saveBtn.textContent = 'Guardando...';

  try {
    const r = await fetch('api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    if (j.ok) {
      if (j.data) S.data = j.data;
      if (j.users) S.users = j.users;
      closeModal();
      renderTabContent();
    } else {
      showFormError(j.error || 'Error desconocido');
    }
  } catch(e) {
    showFormError('Error de red: ' + e.message);
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = 'Guardar';
  }
}

function showFormError(msg){
  const el = document.getElementById('form-error');
  el.textContent = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

/* ─── DELETE CONFIRM ─── */
function confirmDelete(type, id, label){
  const actionMap = { case:'delete_case', gallery:'delete_gallery', video:'delete_video', user:'delete_user' };
  const action = actionMap[type];

  document.getElementById('modal-box').innerHTML = `
    <div class="confirm-box" style="margin:0;border-radius:0;box-shadow:none">
      <h3>¿Confirmar eliminación?</h3>
      <p>Se eliminará permanentemente: <strong>${esc(label)}</strong></p>
      <div class="confirm-btns">
        <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
        <button class="btn-confirm-del" onclick="doDelete('${action}','${id}')">Eliminar</button>
      </div>
    </div>`;
  document.getElementById('modal-backdrop').classList.remove('hidden');
}

async function doDelete(action, id){
  try {
    const r = await fetch('api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action, id, csrf: CSRF })
    });
    const j = await r.json();
    if (j.ok) {
      if (j.data) S.data = j.data;
      if (j.users) S.users = j.users;
      closeModal();
      renderTabContent();
    } else {
      alert('Error: ' + j.error);
    }
  } catch(e) {
    alert('Error de red: ' + e.message);
  }
}

/* ─── SETTINGS ─── */
function renderSettings(){
  if (!IS_ADMIN) return '<div class="empty">Sin acceso</div>';
  const code = esc(DOCS_CODE);
  return `<div class="sec-hdr"><h2>Configuración</h2></div>
  <div class="tbl-wrap" style="max-width:560px">
    <div style="padding:24px">
      <div style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">
        Código de acceso a documentos técnicos
      </div>
      <div style="font-size:12px;color:#888;margin-bottom:14px;line-height:1.5">
        Este código desbloquea el Hardware Guide y las Guías de instalación en el showroom. Compártelo solo con socios y distribuidores autorizados.
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <input type="text" id="f-docs-code" value="${code}" maxlength="64"
          style="flex:1;font-family:monospace;letter-spacing:1px"
          placeholder="Mínimo 4 caracteres">
        <button class="btn-save" id="btn-save-code" onclick="saveDocsCode()" style="white-space:nowrap">
          Guardar
        </button>
      </div>
      <div id="settings-msg" style="font-size:12px;margin-top:10px;display:none"></div>
    </div>
  </div>`;
}

async function saveDocsCode(){
  const val = document.getElementById('f-docs-code').value.trim();
  const btn = document.getElementById('btn-save-code');
  const msg = document.getElementById('settings-msg');
  if (!val) return;
  btn.disabled = true;
  btn.textContent = 'Guardando...';
  msg.style.display = 'none';
  try {
    const r = await fetch('settings_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf: CSRF, docs_access_code: val }),
    });
    const j = await r.json();
    if (j.ok) {
      msg.textContent = '✓ Código actualizado correctamente';
      msg.style.color = '#166534';
    } else {
      msg.textContent = '✗ ' + (j.error || 'Error desconocido');
      msg.style.color = '#991B1B';
    }
  } catch(e) {
    msg.textContent = '✗ Error de red: ' + e.message;
    msg.style.color = '#991B1B';
  } finally {
    msg.style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Guardar';
  }
}

/* ─── INIT ─── */
renderTabContent();
<?php if ($isAdmin) include __DIR__ . '/catalog_funcs.php'; ?>
</script>
</body>
</html>
