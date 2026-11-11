<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'error' => 'Method not allowed']));
}

if (!verify_csrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

$type = $_POST['type'] ?? '';
if (!in_array($type, ['doc', 'img'])) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'Invalid type']));
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['file']['error'] ?? -1;
    exit(json_encode(['ok' => false, 'error' => "Upload error code: $code"]));
}

$file    = $_FILES['file'];
$tmpPath = $file['tmp_name'];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($tmpPath);

if ($type === 'doc') {
    if (!in_array($mime, ALLOWED_DOC_MIME)) {
        exit(json_encode(['ok' => false, 'error' => "Tipo no permitido: $mime. Solo PDF."]));
    }
    if ($file['size'] > MAX_DOC_BYTES) {
        exit(json_encode(['ok' => false, 'error' => 'Archivo muy grande (máx 25 MB)']));
    }
    $ext     = 'pdf';
    $dir     = UPLOAD_DOCS_DIR;
    $urlBase = UPLOAD_DOCS_URL;
} else {
    if (!in_array($mime, ALLOWED_IMG_MIME)) {
        exit(json_encode(['ok' => false, 'error' => "Tipo no permitido: $mime"]));
    }
    if ($file['size'] > MAX_IMG_BYTES) {
        exit(json_encode(['ok' => false, 'error' => 'Imagen muy grande (máx 10 MB)']));
    }
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png',
               'image/webp' => 'webp', 'image/gif' => 'gif'];
    $ext     = $extMap[$mime] ?? 'jpg';
    $dir     = UPLOAD_IMG_DIR;
    $urlBase = UPLOAD_IMG_URL;
}

if (!is_dir($dir)) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'error' => 'Upload directory not configured']));
}

$filename = $type . '_' . date('Ymd') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
$destPath = $dir . $filename;

if (!move_uploaded_file($tmpPath, $destPath)) {
    http_response_code(500);
    exit(json_encode(['ok' => false, 'error' => 'Failed to save file']));
}

$response = ['ok' => true, 'url' => $urlBase . $filename, 'name' => $filename];

// Auto-generate thumbnail from first PDF page using pdftoppm
if ($type === 'doc') {
    $thumbName = 'thumb_' . date('Ymd') . '_' . bin2hex(random_bytes(5));
    $thumbBase = UPLOAD_IMG_DIR . $thumbName;
    $pdftoppm  = '/usr/bin/pdftoppm';
    if (is_executable($pdftoppm)) {
        $cmd = $pdftoppm . ' -f 1 -l 1 -r 72 -jpeg -jpegopt quality=82 '
             . escapeshellarg($destPath) . ' '
             . escapeshellarg($thumbBase)
             . ' 2>/dev/null';
        exec($cmd, $out, $rc);
        $generated = $thumbBase . '-1.jpg';
        if ($rc === 0 && file_exists($generated)) {
            $finalThumb = UPLOAD_IMG_DIR . $thumbName . '.jpg';
            rename($generated, $finalThumb);
            $response['thumb_url'] = UPLOAD_IMG_URL . $thumbName . '.jpg';
        }
    }
}

exit(json_encode($response));
