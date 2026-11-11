<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'Forbidden']));
}

header('Content-Type: application/json');

if (!verify_csrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

$type = $_POST['type'] ?? '';
if (!in_array($type, ['product', 'accessory'], true)) {
    exit(json_encode(['ok' => false, 'error' => 'Invalid type']));
}

$file = $_FILES['file'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['ok' => false, 'error' => 'Upload error ' . ($file['error'] ?? '?')]));
}

// Size limit: 8 MB
if ($file['size'] > 8 * 1024 * 1024) {
    exit(json_encode(['ok' => false, 'error' => 'Archivo muy grande (máx 8 MB)']));
}

// MIME type validation
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowed, true)) {
    exit(json_encode(['ok' => false, 'error' => 'Solo imágenes JPEG, PNG o WEBP']));
}

$ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$ext     = $ext_map[$mime];

$sub_dir = $type === 'product' ? 'products' : 'accessories';
$dest_dir = dirname(__DIR__) . '/assets/' . $sub_dir . '/';

if (!is_dir($dest_dir) && !mkdir($dest_dir, 0755, true)) {
    exit(json_encode(['ok' => false, 'error' => 'No se puede crear el directorio de destino']));
}

$filename = strtoupper($sub_dir[0]) . '_' . date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$dest     = $dest_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    exit(json_encode(['ok' => false, 'error' => 'Error moviendo el archivo']));
}

$url = 'assets/' . $sub_dir . '/' . $filename;
exit(json_encode(['ok' => true, 'url' => $url, 'name' => $filename]));
