<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();
header('Content-Type: application/json');

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'Forbidden']));
}

if (!verify_csrf($_GET['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

$target = rtrim(LIVE_SERVER_URL, '/') . '/manager/sync_export.php?token=' . urlencode(SYNC_SECRET);
$ctx = stream_context_create([
    'http' => ['method' => 'HEAD', 'timeout' => 8],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);

$ok = false;
$status = 0;
$err = null;
$headers = @get_headers($target, true, $ctx);

if (is_array($headers) && isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
    $status = (int) $m[1];
    $ok = $status >= 200 && $status < 400;
} else {
    $err = 'No se pudo conectar con el sitio principal';
}

exit(json_encode([
    'ok' => true,
    'reachable' => $ok,
    'status' => $status,
    'error' => $err,
    'url' => LIVE_SERVER_URL,
]));
