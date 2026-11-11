<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false]));
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
if (!verify_csrf($body['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

$flag = SITE_ACTIVE_FILE;

if (file_exists($flag)) {
    unlink($flag);
    $active = false;
} else {
    file_put_contents($flag, date('c'));
    $active = true;
}

exit(json_encode(['ok' => true, 'active' => $active]));
