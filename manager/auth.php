<?php
require_once __DIR__ . '/config.php';

function session_start_secure(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure',   1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function require_auth(): void {
    session_start_secure();
    if (empty($_SESSION['manager_user']) || empty($_SESSION['manager_time'])) {
        header('Location: index.php'); exit;
    }
    if (time() - $_SESSION['manager_time'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: index.php?expired=1'); exit;
    }
    $_SESSION['manager_time'] = time();

    // Regenerate CSRF token if missing
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string {
    return $_SESSION['csrf'] ?? '';
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function load_users(): array {
    $raw = @file_get_contents(USERS_FILE);
    return $raw ? (json_decode($raw, true)['users'] ?? []) : [];
}

function save_users(array $users): void {
    file_put_contents(USERS_FILE, json_encode(['users' => $users], JSON_PRETTY_PRINT), LOCK_EX);
}

function find_user(string $username): ?array {
    foreach (load_users() as $u) {
        if ($u['username'] === $username) return $u;
    }
    return null;
}

function is_locked_out(string $username): bool {
    $key = 'lockout_' . md5($username);
    if (empty($_SESSION[$key])) return false;
    if ($_SESSION[$key]['until'] < time()) {
        unset($_SESSION[$key]);
        return false;
    }
    return $_SESSION[$key]['attempts'] >= MAX_ATTEMPTS;
}

function record_attempt(string $username): void {
    $key = 'lockout_' . md5($username);
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'until' => time() + LOCKOUT_SECONDS];
    }
    $_SESSION[$key]['attempts']++;
    $_SESSION[$key]['until'] = time() + LOCKOUT_SECONDS;
}

function clear_attempts(string $username): void {
    unset($_SESSION['lockout_' . md5($username)]);
}

function load_data(): array {
    $raw = @file_get_contents(DATA_FILE);
    if (!$raw) return ['casestudies' => [], 'gallery' => [], 'videos' => [], 'meta' => []];
    return json_decode($raw, true) ?: ['casestudies' => [], 'gallery' => [], 'videos' => [], 'meta' => []];
}

function save_data(array $data): void {
    $data['meta']['updated'] = date('c');
    $data['meta']['version']  = ($data['meta']['version'] ?? 0) + 1;
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function gen_id(string $prefix): string {
    return $prefix . '_' . substr(md5(uniqid('', true)), 0, 8);
}

function no_cache_headers(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
}

function load_leads(): array {
    $raw = @file_get_contents(LEADS_FILE);
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

function save_leads(array $leads): void {
    file_put_contents(LEADS_FILE, json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
