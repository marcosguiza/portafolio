<?php
require_once __DIR__ . '/auth.php';
require_auth();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!verify_csrf($body['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
    exit;
}

$new_code = trim($body['docs_access_code'] ?? '');

if (strlen($new_code) < 4) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Código muy corto (mínimo 4 caracteres)']);
    exit;
}

if (strlen($new_code) > 64) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Código muy largo (máximo 64 caracteres)']);
    exit;
}

$settings_file = __DIR__ . '/settings.json';
$settings = file_exists($settings_file)
    ? (json_decode(file_get_contents($settings_file), true) ?? [])
    : [];

$settings['docs_access_code'] = $new_code;

if (file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT), LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el archivo']);
    exit;
}

echo json_encode(['ok' => true, 'docs_access_code' => $new_code]);
