<?php
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

// CSRF — uses the lead form session token
if (!hash_equals($_SESSION['lead_csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

// Rate limit: 30 images per IP per hour
$ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ipHash   = substr(hash('sha256', $ip . 'card_ul_2026_portafolio'), 0, 16);
$rateFile = sys_get_temp_dir() . '/card_rate_' . $ipHash . '.json';
$now      = time();
$rateRaw  = @file_get_contents($rateFile);
$hits     = $rateRaw ? (json_decode($rateRaw, true) ?? []) : [];
$hits     = array_values(array_filter($hits, fn($t) => $now - $t < 3600));
if (count($hits) >= 30) {
    http_response_code(429);
    exit(json_encode(['ok' => false, 'error' => 'Rate limit exceeded']));
}

// File validation
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['ok' => false, 'error' => 'No file received']));
}
$file    = $_FILES['file'];
$finfo   = new finfo(FILEINFO_MIME_TYPE);
$mime    = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($allowed[$mime])) {
    exit(json_encode(['ok' => false, 'error' => 'Tipo no permitido: ' . $mime]));
}
if ($file['size'] > 8 * 1024 * 1024) {
    exit(json_encode(['ok' => false, 'error' => 'Imagen muy grande (máx 8 MB)']));
}

// Ensure directory exists
if (!is_dir(CARD_IMG_DIR)) {
    mkdir(CARD_IMG_DIR, 0755, true);
}

$ext      = $allowed[$mime];
$filename = 'card_' . date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$destPath = CARD_IMG_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'error' => 'Error al guardar imagen']));
}

$hits[] = $now;
file_put_contents($rateFile, json_encode($hits), LOCK_EX);
exit(json_encode(['ok' => true, 'url' => CARD_IMG_URL . $filename]));
