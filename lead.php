<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => 1,
]);

require_once __DIR__ . '/manager/config.php';

// Language
$lang = trim($_GET['lang'] ?? $_SESSION['lead_lang'] ?? 'es');
if (!in_array($lang, ['es','en','pt'])) $lang = 'es';
$_SESSION['lead_lang'] = $lang;

// CSRF
if (empty($_SESSION['lead_csrf'])) {
    $_SESSION['lead_csrf'] = bin2hex(random_bytes(32));
}

function load_leads(): array {
    $raw = @file_get_contents(LEADS_FILE);
    return $raw ? (json_decode($raw, true) ?? []) : [];
}
function save_lead(array $lead): void {
    $all = load_leads();
    $all[] = $lead;
    file_put_contents(LEADS_FILE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
function tg_notify_lead(array $lead): void {
    $token   = TELEGRAM_BOT_TOKEN;
    $chat_id = TELEGRAM_CHAT_ID;
    if (!$token || !$chat_id) return;
    $terms = implode(', ', $lead['terminals'] ?? []);
    $other = $lead['other_interest'] ?? '';
    $lines = [
        "🟢 *Nuevo Lead — Portafolio*",
        "",
        "*Nombre:* " . ($lead['name']     ?? ''),
        "*Cargo:* "  . ($lead['jobtitle'] ?? ''),
        "*Empresa:* ". ($lead['company']  ?? ''),
        "*Email:* "  . ($lead['email']    ?? ''),
        "*Tel:* "    . ($lead['phone']    ?? ''),
    ];
    if ($terms) $lines[] = "*Terminales:* " . $terms;
    if ($other) $lines[] = "*Otros:* "      . $other;
    $lines[] = "*Idioma:* " . strtoupper($lead['lang'] ?? '');
    $lines[] = "*Fecha:* "  . ($lead['created'] ?? '');
    if (!empty($lead['card_image'])) $lines[] = "📷 *Con foto de tarjeta adjunta*";
    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_POSTFIELDS     => [
            'chat_id'    => $chat_id,
            'text'       => implode("\n", $lines),
            'parse_mode' => 'Markdown',
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function rate_ok(string $ipHash): bool {
    $file = sys_get_temp_dir() . '/lead_rate_' . $ipHash . '.json';
    $now  = time();
    $raw  = @file_get_contents($file);
    $hits = $raw ? (json_decode($raw, true) ?? []) : [];
    $hits = array_values(array_filter($hits, fn($t) => $now - $t < 3600));
    if (count($hits) >= 15) return false;
    $hits[] = $now;
    file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}

$errors  = [];
$success = false;
$posted  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['lead_csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $errors['csrf'] = true;
    } elseif (!empty($_POST['_hp'])) {
        $success = true;
    } else {
        $dialCode = trim($_POST['dial_code'] ?? '');
        $phoneNum = trim($_POST['phone_num'] ?? '');
        $phone = $dialCode !== '' && $phoneNum !== '' ? $dialCode . ' ' . $phoneNum : $phoneNum;

        $posted = [
            'name'           => trim($_POST['name'] ?? ''),
            'jobtitle'       => trim($_POST['jobtitle'] ?? ''),
            'company'        => trim($_POST['company'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'phone'          => $phone,
            'dial_code'      => $dialCode,
            'phone_num'      => $phoneNum,
            'terminals'      => array_values(array_filter(array_map('trim', (array)($_POST['terminals'] ?? [])))),
            'other_interest' => trim($_POST['other_interest'] ?? ''),
            'card_image'     => trim($_POST['card_image'] ?? '') ?: null,
        ];
        if ($posted['name']    === '') $errors['name']    = true;
        if ($posted['company'] === '') $errors['company'] = true;
        if ($posted['email']   === '' || !filter_var($posted['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = true;

        if (empty($errors)) {
            $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ipHash = substr(hash('sha256', $ip . 'lsalt_2026_portafolio'), 0, 16);
            if (!rate_ok($ipHash)) {
                $errors['rate'] = true;
            } else {
                $new_lead = [
                    'id'             => 'lead_' . substr(md5(uniqid('', true)), 0, 8),
                    'name'           => $posted['name'],
                    'jobtitle'       => $posted['jobtitle'],
                    'company'        => $posted['company'],
                    'email'          => $posted['email'],
                    'phone'          => $posted['phone'],
                    'terminals'      => $posted['terminals'],
                    'other_interest' => $posted['other_interest'],
                    'card_image'     => $posted['card_image'],
                    'lang'           => $lang,
                    'created'        => date('c'),
                    'ip_hash'        => $ipHash,
                ];
                save_lead($new_lead);
                tg_notify_lead($new_lead);
                $_SESSION['lead_csrf'] = bin2hex(random_bytes(32));
                $success = true;
            }
        }
    }
}

// Country dial codes (LATAM-first, then global)
$DIAL_CODES = [
    ['code'=>'+54',  'flag'=>'🇦🇷','name'=>'Argentina'],
    ['code'=>'+591', 'flag'=>'🇧🇴','name'=>'Bolivia'],
    ['code'=>'+55',  'flag'=>'🇧🇷','name'=>'Brasil / Brazil'],
    ['code'=>'+56',  'flag'=>'🇨🇱','name'=>'Chile'],
    ['code'=>'+57',  'flag'=>'🇨🇴','name'=>'Colombia'],
    ['code'=>'+506', 'flag'=>'🇨🇷','name'=>'Costa Rica'],
    ['code'=>'+53',  'flag'=>'🇨🇺','name'=>'Cuba'],
    ['code'=>'+1',   'flag'=>'🇩🇴','name'=>'Rep. Dominicana'],
    ['code'=>'+593', 'flag'=>'🇪🇨','name'=>'Ecuador'],
    ['code'=>'+503', 'flag'=>'🇸🇻','name'=>'El Salvador'],
    ['code'=>'+502', 'flag'=>'🇬🇹','name'=>'Guatemala'],
    ['code'=>'+509', 'flag'=>'🇭🇹','name'=>'Haití / Haiti'],
    ['code'=>'+504', 'flag'=>'🇭🇳','name'=>'Honduras'],
    ['code'=>'+52',  'flag'=>'🇲🇽','name'=>'México / Mexico'],
    ['code'=>'+505', 'flag'=>'🇳🇮','name'=>'Nicaragua'],
    ['code'=>'+507', 'flag'=>'🇵🇦','name'=>'Panamá / Panama'],
    ['code'=>'+595', 'flag'=>'🇵🇾','name'=>'Paraguay'],
    ['code'=>'+51',  'flag'=>'🇵🇪','name'=>'Perú / Peru'],
    ['code'=>'+598', 'flag'=>'🇺🇾','name'=>'Uruguay'],
    ['code'=>'+58',  'flag'=>'🇻🇪','name'=>'Venezuela'],
    ['code'=>'---',  'flag'=>'',   'name'=>'─────────────'],
    ['code'=>'+1',   'flag'=>'🇺🇸','name'=>'United States'],
    ['code'=>'+1',   'flag'=>'🇨🇦','name'=>'Canada'],
    ['code'=>'+34',  'flag'=>'🇪🇸','name'=>'España / Spain'],
    ['code'=>'+351', 'flag'=>'🇵🇹','name'=>'Portugal'],
    ['code'=>'+44',  'flag'=>'🇬🇧','name'=>'United Kingdom'],
    ['code'=>'+49',  'flag'=>'🇩🇪','name'=>'Deutschland / Germany'],
    ['code'=>'+33',  'flag'=>'🇫🇷','name'=>'France'],
    ['code'=>'+27',  'flag'=>'🇿🇦','name'=>'South Africa'],
    ['code'=>'+61',  'flag'=>'🇦🇺','name'=>'Australia'],
    ['code'=>'+86',  'flag'=>'🇨🇳','name'=>'China'],
    ['code'=>'+91',  'flag'=>'🇮🇳','name'=>'India'],
];

// i18n strings
$tx = [
    'es' => [
        'title'        => 'Formulario de Contacto',
        'heading'      => 'Conéctate con nosotros',
        'sub'          => 'Completa el formulario y nuestro equipo se pondrá en contacto contigo.',
        'name'         => 'Nombre',
        'jobtitle'     => 'Cargo / Título',
        'company'      => 'Empresa',
        'email'        => 'Correo electrónico',
        'phone'        => 'Teléfono',
        'phone_num'    => 'Número',
        'country_code' => 'País',
        'terminals'    => 'Terminales de interés',
        'other_group'  => 'Otros',
        'other_ph'     => 'Describe tu interés (producto, solución, industria...)',
        'submit'       => 'Enviar',
        'ok_heading'   => '¡Gracias!',
        'ok_msg'       => 'Hemos recibido tu información. Nuestro equipo se pondrá en contacto contigo pronto.',
        'ok_btn'       => 'Enviar otro',
        'err_required' => 'Este campo es obligatorio.',
        'err_email'    => 'Correo electrónico inválido.',
        'err_rate'     => 'Demasiados intentos. Por favor intenta más tarde.',
        'err_csrf'     => 'Sesión expirada. Recarga la página.',
        'required_mark'=> '* Campo obligatorio',
        // Camera capture
        'scan_btn'        => 'Escanear badge / Capturar tarjeta',
        'scan_modal_title'=> 'Captura · Escaneo',
        'scan_tab_qr'     => 'QR Badge',
        'scan_tab_photo'  => 'Foto Tarjeta',
        'scan_capture'    => 'Capturar foto',
        'scan_hint_qr'    => 'El QR se detecta automáticamente',
        'scan_filled'     => 'Campos completados desde QR',
        'scan_card_lbl'   => 'Tarjeta capturada',
        'scan_card_clear' => 'Quitar foto',
        // Offline sync
        'sync_pending'    => 'lead(s) guardado(s) sin conexión',
        'sync_now'        => 'Sincronizar ahora',
        // JS strings passed to lead-cam.js
        'js_qr_status'    => 'Apunta al código QR del badge...',
        'js_ph_status'    => 'Apunta la cámara y toca Capturar',
        'js_cam_denied'   => 'Permiso de cámara denegado. Revisa la configuración del navegador.',
        'js_qr_unavail'   => 'Escáner QR no disponible en este navegador. Cambiando a foto...',
        'js_qr_nocontact' => 'QR sin datos de contacto. Cambiando a foto...',
    ],
    'en' => [
        'title'        => 'Contact Form',
        'heading'      => 'Connect with us',
        'sub'          => 'Fill out the form and our team will get in touch with you.',
        'name'         => 'Name',
        'jobtitle'     => 'Position / Title',
        'company'      => 'Company',
        'email'        => 'Email',
        'phone'        => 'Phone',
        'phone_num'    => 'Number',
        'country_code' => 'Country',
        'terminals'    => 'Terminals of interest',
        'other_group'  => 'Other',
        'other_ph'     => 'Describe your interest (product, solution, industry...)',
        'submit'       => 'Submit',
        'ok_heading'   => 'Thank you!',
        'ok_msg'       => 'We have received your information. Our team will contact you soon.',
        'ok_btn'       => 'Submit another',
        'err_required' => 'This field is required.',
        'err_email'    => 'Invalid email address.',
        'err_rate'     => 'Too many attempts. Please try again later.',
        'err_csrf'     => 'Session expired. Please reload the page.',
        'required_mark'=> '* Required field',
        // Camera capture
        'scan_btn'        => 'Scan badge / Capture card',
        'scan_modal_title'=> 'Capture · Scan',
        'scan_tab_qr'     => 'QR Badge',
        'scan_tab_photo'  => 'Card Photo',
        'scan_capture'    => 'Capture photo',
        'scan_hint_qr'    => 'QR is detected automatically',
        'scan_filled'     => 'Fields filled from QR',
        'scan_card_lbl'   => 'Captured card',
        'scan_card_clear' => 'Remove photo',
        // Offline sync
        'sync_pending'    => 'lead(s) saved offline',
        'sync_now'        => 'Sync now',
        // JS strings
        'js_qr_status'    => 'Point at the badge QR code...',
        'js_ph_status'    => 'Point camera at the card and tap Capture',
        'js_cam_denied'   => 'Camera permission denied. Check browser settings.',
        'js_qr_unavail'   => 'QR scanner not available in this browser. Switching to photo...',
        'js_qr_nocontact' => 'QR found but no contact data. Switching to photo...',
    ],
    'pt' => [
        'title'        => 'Formulário de Contato',
        'heading'      => 'Conecte-se conosco',
        'sub'          => 'Preencha o formulário e nossa equipe entrará em contato com você.',
        'name'         => 'Nome',
        'jobtitle'     => 'Cargo / Título',
        'company'      => 'Empresa',
        'email'        => 'E-mail',
        'phone'        => 'Telefone',
        'phone_num'    => 'Número',
        'country_code' => 'País',
        'terminals'    => 'Terminais de interesse',
        'other_group'  => 'Outros',
        'other_ph'     => 'Descreva seu interesse (produto, solução, indústria...)',
        'submit'       => 'Enviar',
        'ok_heading'   => 'Obrigado!',
        'ok_msg'       => 'Recebemos suas informações. Nossa equipe entrará em contato em breve.',
        'ok_btn'       => 'Enviar outro',
        'err_required' => 'Este campo é obrigatório.',
        'err_email'    => 'E-mail inválido.',
        'err_rate'     => 'Muitas tentativas. Por favor, tente novamente mais tarde.',
        'err_csrf'     => 'Sessão expirada. Por favor, recarregue a página.',
        'required_mark'=> '* Campo obrigatório',
        // Camera capture
        'scan_btn'        => 'Escanear badge / Capturar cartão',
        'scan_modal_title'=> 'Captura · Escaneamento',
        'scan_tab_qr'     => 'QR Badge',
        'scan_tab_photo'  => 'Foto Cartão',
        'scan_capture'    => 'Capturar foto',
        'scan_hint_qr'    => 'O QR é detectado automaticamente',
        'scan_filled'     => 'Campos preenchidos do QR',
        'scan_card_lbl'   => 'Cartão capturado',
        'scan_card_clear' => 'Remover foto',
        // Offline sync
        'sync_pending'    => 'lead(s) salvo(s) sem conexão',
        'sync_now'        => 'Sincronizar agora',
        // JS strings
        'js_qr_status'    => 'Aponte para o QR code do badge...',
        'js_ph_status'    => 'Aponte a câmera e toque em Capturar',
        'js_cam_denied'   => 'Permissão de câmera negada. Verifique as configurações do navegador.',
        'js_qr_unavail'   => 'Scanner QR não disponível neste navegador. Mudando para foto...',
        'js_qr_nocontact' => 'QR encontrado mas sem dados de contato. Mudando para foto...',
    ],
];
$t = $tx[$lang];

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fv(array $posted, string $key): string { return h($posted[$key] ?? ''); }
function ferr(array $errors, string $key, string $msg): string {
    return isset($errors[$key]) ? '<p class="field-err">' . h($msg) . '</p>' : '';
}
$csrf = $_SESSION['lead_csrf'];
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="robots" content="noindex,nofollow"/>
<title><?= h($t['title']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Roboto',system-ui,sans-serif;background:#F5F6F8;color:#0D0A07;min-height:100vh;
  display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}

/* Header */
.hdr{background:#fff;border-bottom:1px solid #EBEBEB;padding:14px 24px;
  display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:50}
.hdr-dot{width:22px;height:22px;background:#BA0C2F;border-radius:5px;flex-shrink:0}
.hdr-name{font-size:14px;font-weight:700;color:#0D0A07;letter-spacing:.3px}
.hdr-right{margin-left:auto;display:flex;align-items:center;gap:6px}
.lang-btn{padding:4px 10px;border-radius:3px;border:1px solid #EBEBEB;background:transparent;
  color:#999;font-family:'Roboto Mono',monospace;font-size:10px;font-weight:600;cursor:pointer;
  text-transform:uppercase;letter-spacing:1px;transition:all .15s;text-decoration:none;display:inline-block}
.lang-btn.active,.lang-btn:hover{background:#0D0A07;border-color:#0D0A07;color:#fff}
.home-btn{display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:3px;
  border:1px solid #EBEBEB;background:#fff;color:#666;font-size:11px;font-family:'Roboto Mono',monospace;
  font-weight:600;letter-spacing:.5px;cursor:pointer;text-decoration:none;transition:all .15s;margin-left:4px}
.home-btn:hover{border-color:#BA0C2F;color:#BA0C2F}

/* Main */
main{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:36px 16px 60px}
.card{background:#fff;border:1px solid #EBEBEB;border-radius:12px;
  width:100%;max-width:560px;padding:36px 32px;box-shadow:0 1px 12px rgba(0,0,0,.06)}
@media(max-width:500px){.card{padding:24px 16px}}

.card-heading{font-size:22px;font-weight:700;letter-spacing:-.3px;margin-bottom:8px;color:#0D0A07}
.card-sub{font-size:13px;color:#777;line-height:1.6;margin-bottom:28px}

/* Form */
.form-row{margin-bottom:16px}
.form-row label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.6px;color:#888;margin-bottom:5px;font-family:'Roboto Mono',monospace}
.form-row label .req{color:#BA0C2F}
input[type=text],input[type=email],input[type=tel],textarea{
  width:100%;padding:9px 12px;background:#fff;border:1px solid #D1D5DB;border-radius:6px;
  color:#0D0A07;font-size:13px;font-family:'Roboto',sans-serif;transition:border-color .15s}
input:focus,textarea:focus{outline:none;border-color:#BA0C2F}
input.err-field,textarea.err-field{border-color:#DC2626}
.field-err{color:#DC2626;font-size:11px;margin-top:4px}

/* Phone row */
.phone-row{display:flex;gap:8px;align-items:flex-start}
.dial-wrap{flex-shrink:0;width:180px}
.num-wrap{flex:1;min-width:0}
.dial-select-wrap{position:relative}
.dial-select-wrap::after{content:'▾';position:absolute;right:10px;top:50%;transform:translateY(-50%);
  pointer-events:none;color:#888;font-size:11px}
select.dial-select{
  width:100%;padding:9px 28px 9px 10px;background:#fff;border:1px solid #D1D5DB;border-radius:6px;
  color:#0D0A07;font-size:13px;font-family:'Roboto',sans-serif;transition:border-color .15s;
  appearance:none;-webkit-appearance:none;cursor:pointer}
select.dial-select:focus{outline:none;border-color:#BA0C2F}
.dial-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.6px;color:#888;margin-bottom:5px;font-family:'Roboto Mono',monospace}
.num-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.6px;color:#888;margin-bottom:5px;font-family:'Roboto Mono',monospace}

/* Terminals */
.term-section{margin-bottom:16px}
.term-section-label{font-size:11px;font-weight:700;text-transform:uppercase;
  letter-spacing:.6px;color:#888;margin-bottom:10px;font-family:'Roboto Mono',monospace}
.term-group{margin-bottom:14px}
.term-group-name{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;
  color:#BA0C2F;margin-bottom:8px;padding-bottom:4px;border-bottom:2px solid #EBEBEB}
.term-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:5px}
.term-item{display:flex;align-items:center;gap:7px;cursor:pointer;padding:7px 10px;
  border-radius:6px;border:1px solid #E5E7EB;background:#F9FAFB;transition:all .15s}
.term-item:hover{border-color:#BA0C2F;background:#FFF5F6}
.term-item input[type=checkbox]{width:13px;height:13px;accent-color:#BA0C2F;flex-shrink:0;cursor:pointer}
.term-item span{font-size:12px;font-weight:500;color:#555;cursor:pointer;line-height:1.2}
.term-item:has(input:checked){border-color:#BA0C2F;background:#FFF5F6}
.term-item:has(input:checked) span{color:#BA0C2F;font-weight:700}

/* Other group */
.other-group{background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;padding:14px}
.other-group .term-group-name{margin-bottom:10px}
textarea.other-input{
  width:100%;padding:9px 12px;background:#fff;border:1px solid #D1D5DB;border-radius:6px;
  color:#0D0A07;font-size:13px;font-family:'Roboto',sans-serif;transition:border-color .15s;
  resize:vertical;min-height:72px;margin:0}
textarea.other-input:focus{outline:none;border-color:#BA0C2F}
textarea.other-input::placeholder{color:#B0B0B0}

/* Submit */
.btn-submit{width:100%;padding:12px;background:#BA0C2F;border:none;border-radius:7px;
  color:#fff;font-size:13px;font-weight:700;letter-spacing:.5px;cursor:pointer;
  transition:background .15s;margin-top:8px;font-family:'Roboto',sans-serif}
.btn-submit:hover{background:#9A0A27}
.btn-submit:disabled{background:#d9a0a8;cursor:not-allowed}
.required-note{font-size:11px;color:#AAA;margin-top:10px;text-align:center;font-family:'Roboto Mono',monospace}

/* Global errors */
.global-err{background:#FEF2F2;border:1px solid #FECACA;border-radius:7px;padding:10px 14px;
  color:#DC2626;font-size:13px;margin-bottom:20px}

/* Success */
.success-wrap{text-align:center;padding:20px 0}
.success-icon{font-size:52px;margin-bottom:16px;color:#16A34A}
.success-heading{font-size:22px;font-weight:700;margin-bottom:10px;color:#0D0A07}
.success-msg{font-size:14px;color:#666;line-height:1.6;margin-bottom:28px}
.btn-again{display:inline-block;padding:10px 24px;background:#BA0C2F;border-radius:6px;
  color:#fff;font-size:13px;font-weight:700;cursor:pointer;border:none;transition:background .15s;
  text-decoration:none;font-family:'Roboto',sans-serif}
.btn-again:hover{background:#9A0A27}

/* Honeypot */
.hp-field{display:none!important;visibility:hidden!important;position:absolute!important;
  left:-9999px!important;top:-9999px!important}

/* ── Sync banner ── */
#sync-banner{display:none;align-items:center;gap:10px;padding:10px 20px;
  background:#FEF3C7;border-bottom:1px solid #FDE68A;font-size:13px;color:#92400E}
#btn-sync-now{margin-left:auto;padding:5px 14px;background:#D97706;border:none;
  border-radius:5px;color:#fff;font-size:12px;font-weight:700;cursor:pointer;transition:background .15s}
#btn-sync-now:hover{background:#B45309}
#btn-sync-now:disabled{opacity:.6;cursor:not-allowed}
#btn-sync-dismiss{padding:4px 8px;border:none;background:none;cursor:pointer;
  color:#92400E;font-size:18px;line-height:1}

/* ── Camera overlay ── */
#cam-overlay{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.82);
  align-items:center;justify-content:center;padding:16px}
#cam-modal{background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;
  display:flex;flex-direction:column;max-height:96vh}
#cam-video{width:100%;display:block;aspect-ratio:4/3;object-fit:cover;background:#111}
.cam-tab{flex:1;padding:13px 8px;border:none;background:none;cursor:pointer;
  font-size:13px;font-weight:600;color:#888;
  border-bottom:2px solid transparent;transition:all .15s;font-family:'Roboto',sans-serif}
.cam-tab[data-active='1']{color:#BA0C2F;border-bottom-color:#BA0C2F}
#qr-aim-frame{position:absolute;inset:0;display:flex;
  align-items:center;justify-content:center;pointer-events:none}
.qr-square{width:min(200px,55vw);height:min(200px,55vw);
  border:2px solid rgba(255,255,255,.85);border-radius:14px;
  box-shadow:0 0 0 9999px rgba(0,0,0,.38)}
#cam-status{position:absolute;bottom:0;left:0;right:0;padding:10px 16px;
  background:linear-gradient(transparent,rgba(0,0,0,.65));color:#fff;
  font-size:11px;text-align:center;font-family:'Roboto Mono',monospace;letter-spacing:.3px}

/* ── Scan button ── */
.btn-scan{display:flex;align-items:center;gap:10px;width:100%;padding:12px 16px;
  margin-bottom:20px;border:1.5px dashed #D1D5DB;border-radius:8px;background:#F9FAFB;
  cursor:pointer;font-size:13px;font-weight:600;color:#555;transition:all .15s;
  font-family:'Roboto',sans-serif;text-align:left}
.btn-scan:hover{border-color:#BA0C2F;color:#BA0C2F;background:#FFF5F6}
.btn-scan-badge{margin-left:auto;font-size:9px;font-family:'Roboto Mono',monospace;
  color:#AAA;letter-spacing:1px;white-space:nowrap}

/* ── Fill indicator ── */
#fill-indicator{display:none;align-items:center;gap:8px;padding:9px 14px;
  margin-bottom:16px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:7px;
  color:#166534;font-size:12px;font-weight:600}

/* ── Card thumbnail ── */
#card-thumb-wrap{display:none;align-items:flex-start;gap:12px;padding:12px;
  margin-bottom:16px;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px}
#card-thumb-img{width:88px;height:66px;object-fit:cover;border-radius:6px;
  border:1px solid #D1D5DB;flex-shrink:0;cursor:pointer}
.btn-clear-photo{margin-top:6px;padding:4px 10px;background:none;
  border:1px solid #D1D5DB;border-radius:5px;font-size:11px;color:#888;
  cursor:pointer;font-family:'Roboto',sans-serif;transition:all .15s}
.btn-clear-photo:hover{border-color:#DC2626;color:#DC2626}
</style>
</head>
<body>

<!-- Offline sync banner -->
<div id="sync-banner">
  <span>📲 <strong id="sync-count">0</strong> <?= h($t['sync_pending']) ?></span>
  <button id="btn-sync-now" onclick="syncNow()"><?= h($t['sync_now']) ?></button>
  <button id="btn-sync-dismiss" onclick="this.parentElement.style.display='none'" title="Cerrar">✕</button>
</div>

<header class="hdr">
  <div class="hdr-dot"></div>
  <span class="hdr-name">Skywave · VIACHAIN</span>
  <div class="hdr-right">
    <a href="?lang=es" class="lang-btn <?= $lang==='es'?'active':'' ?>">ES</a>
    <a href="?lang=en" class="lang-btn <?= $lang==='en'?'active':'' ?>">EN</a>
    <a href="?lang=pt" class="lang-btn <?= $lang==='pt'?'active':'' ?>">PT</a>
    <a href="/" class="home-btn">← Home</a>
  </div>
</header>

<!-- Camera overlay -->
<div id="cam-overlay">
  <div id="cam-modal">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #EBEBEB;flex-shrink:0">
      <span style="font-family:'Roboto Mono',monospace;font-size:10px;letter-spacing:1.5px;color:#888;text-transform:uppercase"><?= h($t['scan_modal_title']) ?></span>
      <button onclick="closeCamera()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#888;line-height:1;padding:2px 6px;border-radius:4px">✕</button>
    </div>
    <!-- Mode tabs -->
    <div style="display:flex;border-bottom:1px solid #EBEBEB;flex-shrink:0">
      <button id="cam-tab-qr"    class="cam-tab" data-active="1" onclick="switchCamMode('qr')">📷 <?= h($t['scan_tab_qr']) ?></button>
      <button id="cam-tab-photo" class="cam-tab" data-active="0" onclick="switchCamMode('photo')">🪪 <?= h($t['scan_tab_photo']) ?></button>
    </div>
    <!-- Video stage -->
    <div style="position:relative;background:#111;flex-shrink:0">
      <video id="cam-video" autoplay playsinline muted></video>
      <div id="qr-aim-frame"><div class="qr-square"></div></div>
      <div id="cam-status"></div>
    </div>
    <canvas id="cam-canvas" style="display:none"></canvas>
    <!-- Controls -->
    <div style="padding:16px 20px;display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0">
      <button id="btn-cam-capture" onclick="capturePhoto()"
        style="display:none;padding:14px 0;background:#BA0C2F;color:#fff;border:none;border-radius:8px;
          font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.3px;width:100%;font-family:'Roboto',sans-serif">
        <?= h($t['scan_capture']) ?>
      </button>
      <div id="qr-hint" style="font-family:'Roboto Mono',monospace;font-size:11px;color:#999;letter-spacing:.3px;text-align:center">
        <?= h($t['scan_hint_qr']) ?>
      </div>
    </div>
  </div>
</div>

<main>
  <div class="card">

<?php if ($success): ?>
  <div class="success-wrap">
    <div class="success-icon">✓</div>
    <h1 class="success-heading"><?= h($t['ok_heading']) ?></h1>
    <p class="success-msg"><?= h($t['ok_msg']) ?></p>
    <a href="lead.php?lang=<?= h($lang) ?>" class="btn-again"><?= h($t['ok_btn']) ?></a>
  </div>
<?php else: ?>

  <button type="button" class="btn-scan" onclick="openCamera('qr')">
    <span style="font-size:20px;line-height:1">📷</span>
    <span><?= h($t['scan_btn']) ?></span>
    <span class="btn-scan-badge">QR · FOTO</span>
  </button>
  <div id="fill-indicator">✓ <?= h($t['scan_filled']) ?></div>

  <h1 class="card-heading"><?= h($t['heading']) ?></h1>
  <p class="card-sub"><?= h($t['sub']) ?></p>

  <?php if (isset($errors['csrf'])): ?>
    <div class="global-err"><?= h($t['err_csrf']) ?></div>
  <?php elseif (isset($errors['rate'])): ?>
    <div class="global-err"><?= h($t['err_rate']) ?></div>
  <?php endif; ?>

  <form method="post" action="lead.php?lang=<?= h($lang) ?>" novalidate>
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
    <div class="hp-field" aria-hidden="true">
      <label>Leave blank <input type="text" name="_hp" autocomplete="off" tabindex="-1"></label>
    </div>

    <!-- Name -->
    <div class="form-row">
      <label for="f-name"><?= h($t['name']) ?> <span class="req">*</span></label>
      <input type="text" id="f-name" name="name" value="<?= fv($posted,'name') ?>"
        autocomplete="name" <?= isset($errors['name'])?'class="err-field"':'' ?>>
      <?= ferr($errors,'name',$t['err_required']) ?>
    </div>

    <!-- Job title -->
    <div class="form-row">
      <label for="f-jobtitle"><?= h($t['jobtitle']) ?></label>
      <input type="text" id="f-jobtitle" name="jobtitle" value="<?= fv($posted,'jobtitle') ?>"
        autocomplete="organization-title">
    </div>

    <!-- Company -->
    <div class="form-row">
      <label for="f-company"><?= h($t['company']) ?> <span class="req">*</span></label>
      <input type="text" id="f-company" name="company" value="<?= fv($posted,'company') ?>"
        autocomplete="organization" <?= isset($errors['company'])?'class="err-field"':'' ?>>
      <?= ferr($errors,'company',$t['err_required']) ?>
    </div>

    <!-- Email -->
    <div class="form-row">
      <label for="f-email"><?= h($t['email']) ?> <span class="req">*</span></label>
      <input type="email" id="f-email" name="email" value="<?= fv($posted,'email') ?>"
        autocomplete="email" <?= isset($errors['email'])?'class="err-field"':'' ?>>
      <?= ferr($errors,'email',$t['err_email']) ?>
    </div>

    <!-- Phone with country code -->
    <div class="form-row" style="margin-bottom:16px">
      <div class="phone-row">
        <div class="dial-wrap">
          <label class="dial-label" for="f-dial"><?= h($t['country_code']) ?></label>
          <div class="dial-select-wrap">
            <select class="dial-select" id="f-dial" name="dial_code">
              <option value="">—</option>
              <?php foreach ($DIAL_CODES as $dc):
                if ($dc['code'] === '---'): ?>
                <option disabled>──────────</option>
                <?php else:
                  $sel = (isset($posted['dial_code']) && $posted['dial_code'] === $dc['code'] . ($dc['name'] !== $posted['dial_code'] ? '' : '')) ? 'selected' : '';
                  // Match on stored dial_code
                  $opt_val = h($dc['code']);
                  $sel = (isset($posted['dial_code']) && $posted['dial_code'] === $dc['code'] && (isset($posted['_dial_name']) ? $posted['_dial_name'] === $dc['name'] : true)) ? 'selected' : '';
                ?>
                <option value="<?= $opt_val ?>" <?= $sel ?>><?= h($dc['flag'] . ' ' . $dc['name'] . ' (' . $dc['code'] . ')') ?></option>
              <?php endif; endforeach; ?>
            </select>
          </div>
        </div>
        <div class="num-wrap">
          <label class="num-label" for="f-phone"><?= h($t['phone_num']) ?></label>
          <input type="tel" id="f-phone" name="phone_num" value="<?= fv($posted,'phone_num') ?>"
            autocomplete="tel-national" placeholder="912 345 678">
        </div>
      </div>
    </div>

    <!-- Card thumbnail (shown after photo capture) -->
    <div id="card-thumb-wrap">
      <img id="card-thumb-img" src="" alt="<?= h($t['scan_card_lbl']) ?>"
        onclick="window.open(this.src,'_blank')" title="<?= h($t['scan_card_lbl']) ?>">
      <div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
          color:#888;font-family:'Roboto Mono',monospace;margin-bottom:8px">
          <?= h($t['scan_card_lbl']) ?>
        </div>
        <button type="button" class="btn-clear-photo" onclick="clearPhoto()">
          ✕ <?= h($t['scan_card_clear']) ?>
        </button>
      </div>
    </div>
    <input type="hidden" id="f-card-image" name="card_image" value="">

    <!-- Terminals -->
    <div class="term-section">
      <div class="term-section-label"><?= h($t['terminals']) ?></div>

      <?php foreach (TERMINALS as $brand => $group): ?>
      <div class="term-group">
        <div class="term-group-name"><?= h($group['label']) ?></div>
        <div class="term-grid">
          <?php foreach ($group['items'] as $term): ?>
          <?php $checked = in_array($term, $posted['terminals'] ?? []); ?>
          <label class="term-item">
            <input type="checkbox" name="terminals[]" value="<?= h($term) ?>" <?= $checked?'checked':'' ?>>
            <span><?= h($term) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Other group -->
      <div class="other-group">
        <div class="term-group-name"><?= h($t['other_group']) ?></div>
        <textarea class="other-input" name="other_interest" id="f-other"
          placeholder="<?= h($t['other_ph']) ?>"
          rows="3"><?= fv($posted,'other_interest') ?></textarea>
      </div>
    </div>

    <button type="submit" class="btn-submit" id="btn-submit"><?= h($t['submit']) ?></button>
    <p class="required-note"><?= h($t['required_mark']) ?></p>
  </form>

<?php endif; ?>

  </div><!-- /card -->
</main>

<script>
/* Pass translated strings to lead-cam.js before it loads */
window._CAM_STRINGS = {
  qr_status:    <?= json_encode($t['js_qr_status']) ?>,
  ph_status:    <?= json_encode($t['js_ph_status']) ?>,
  cam_denied:   <?= json_encode($t['js_cam_denied']) ?>,
  qr_unavail:   <?= json_encode($t['js_qr_unavail']) ?>,
  qr_nocontact: <?= json_encode($t['js_qr_nocontact']) ?>,
};
</script>
<script src="assets/lead-cam.js"></script>
</body>
</html>
