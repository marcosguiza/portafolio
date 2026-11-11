<?php
require_once __DIR__ . '/portfolio_auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

portfolio_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$code = trim($body['code'] ?? '');

if ($code === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_code']);
    exit;
}

// Read access code from settings.json (editable via manager)
$settings_file = __DIR__ . '/manager/settings.json';
$settings      = file_exists($settings_file) ? (json_decode(file_get_contents($settings_file), true) ?? []) : [];
$docs_code     = $settings['docs_access_code'] ?? '';

if ($docs_code === '') {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'not_configured']);
    exit;
}

// Simple rate limiting via session — 5 attempts then 5-min lockout
$lockout_until = $_SESSION['docs_lockout_until'] ?? 0;
if ($lockout_until > time()) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'too_many_attempts', 'retry_after' => $lockout_until - time()]);
    exit;
}

if ($code === $docs_code) {
    $_SESSION['docs_unlocked']    = true;
    $_SESSION['docs_unlock_time'] = time();
    unset($_SESSION['docs_attempts'], $_SESSION['docs_lockout_until']);
    echo json_encode(['ok' => true]);
} else {
    $attempts = ($_SESSION['docs_attempts'] ?? 0) + 1;
    $_SESSION['docs_attempts'] = $attempts;
    if ($attempts >= 5) {
        $_SESSION['docs_lockout_until'] = time() + 300;
    }
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'invalid_code']);
}
