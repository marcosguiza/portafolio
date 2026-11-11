<?php
require_once __DIR__ . '/portfolio_auth.php';
portfolio_session_start();

unset($_SESSION['portfolio_user'], $_SESSION['portfolio_time']);

// Clear any lockout keys specific to portfolio
foreach (array_keys($_SESSION) as $k) {
    if (str_starts_with($k, 'pf_lock_')) unset($_SESSION[$k]);
}

session_write_close();

header('Cache-Control: no-store');
header('Location: /login.php');
exit;
