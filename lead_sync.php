<?php
// Endpoint for syncing leads that were captured offline (IndexedDB queue).
// Called by assets/lead-cam.js when the device comes back online.
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => 1,
]);
require_once __DIR__ . '/manager/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'error' => 'Method not allowed']));
}

$raw  = file_get_contents('php://input');
$body = $raw ? (json_decode($raw, true) ?? []) : [];

// CSRF — uses the lead form session token
if (!hash_equals($_SESSION['lead_csrf'] ?? '', $body['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

// Rate limit (shared bucket with lead.php)
$ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ipHash   = substr(hash('sha256', $ip . 'lsalt_2026_portafolio'), 0, 16);
$rateFile = sys_get_temp_dir() . '/lead_rate_' . $ipHash . '.json';
$now      = time();
$rateRaw  = @file_get_contents($rateFile);
$hits     = $rateRaw ? (json_decode($rateRaw, true) ?? []) : [];
$hits     = array_values(array_filter($hits, fn($t) => $now - $t < 3600));
if (count($hits) >= 15) {
    http_response_code(429);
    exit(json_encode(['ok' => false, 'error' => 'Rate limit']));
}

$leadsIn = $body['leads'] ?? [];
if (!is_array($leadsIn) || !$leadsIn) {
    exit(json_encode(['ok' => false, 'error' => 'No leads provided']));
}

function ls_load(): array {
    $raw = @file_get_contents(LEADS_FILE);
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

function ls_save(array $leads): void {
    file_put_contents(LEADS_FILE, json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function ls_tg(array $lead): void {
    $token   = TELEGRAM_BOT_TOKEN;
    $chat_id = TELEGRAM_CHAT_ID;
    if (!$token || !$chat_id) return;
    $terms = implode(', ', $lead['terminals'] ?? []);
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
    if (!empty($lead['other_interest'])) $lines[] = "*Otros:* " . $lead['other_interest'];
    $lines[] = "*Idioma:* " . strtoupper($lead['lang'] ?? '');
    $lines[] = "*Fecha:* "  . ($lead['created'] ?? '');
    if (!empty($lead['card_image'])) $lines[] = "📷 *Con foto de tarjeta*";
    $lines[] = "_(sincronizado offline)_";
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

$saved = 0;
$all   = ls_load();

foreach ($leadsIn as $leadIn) {
    if (!is_array($leadIn)) continue;
    $name    = trim($leadIn['name']    ?? '');
    $company = trim($leadIn['company'] ?? '');
    $email   = trim($leadIn['email']   ?? '');
    if (!$name || !$company || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

    $new_lead = [
        'id'             => 'lead_' . substr(md5(uniqid('', true)), 0, 8),
        'name'           => $name,
        'jobtitle'       => trim($leadIn['jobtitle']       ?? ''),
        'company'        => $company,
        'email'          => $email,
        'phone'          => trim($leadIn['phone']          ?? ''),
        'terminals'      => array_values(array_filter(array_map('trim', (array)($leadIn['terminals'] ?? [])))),
        'other_interest' => trim($leadIn['other_interest'] ?? ''),
        'card_image'     => trim($leadIn['card_image']     ?? '') ?: null,
        'lang'           => in_array($leadIn['lang'] ?? '', ['es','en','pt']) ? $leadIn['lang'] : 'es',
        'created'        => $leadIn['created'] ?? date('c'),
        'ip_hash'        => $ipHash,
        'source'         => 'offline_sync',
    ];
    $all[] = $new_lead;
    ls_tg($new_lead);
    $hits[] = $now;
    $saved++;
}

file_put_contents($rateFile, json_encode($hits), LOCK_EX);
ls_save($all);
exit(json_encode(['ok' => true, 'saved' => $saved]));
