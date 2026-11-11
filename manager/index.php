<?php
require_once __DIR__ . '/auth.php';
session_start_secure();
no_cache_headers();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (is_locked_out($user)) {
        $error = 'Too many failed attempts. Try again in 15 minutes.';
    } else {
        $found = find_user($user);
        if ($found && password_verify($pass, $found['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['manager_user'] = $found['username'];
            $_SESSION['manager_role'] = $found['role'];
            $_SESSION['manager_time'] = time();
            $_SESSION['csrf']         = bin2hex(random_bytes(32));
            clear_attempts($user);
            header('Location: dashboard.php'); exit;
        } else {
            record_attempt($user);
            $error = 'Invalid credentials.';
            usleep(500000); // 0.5s delay
        }
    }
}

$expired = isset($_GET['expired']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title>Manager · Login</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: #F0F2F5; min-height: 100vh;
    display: flex; align-items: center; justify-content: center; }
  .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,.08);
    padding: 48px 40px; width: 100%; max-width: 380px; }
  .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
  .brand-dot { width: 28px; height: 28px; background: #BA0C2F; border-radius: 6px; flex-shrink: 0; }
  .brand-text { font-size: 14px; font-weight: 600; color: #0D0A07; letter-spacing: .5px; }
  .brand-sub  { font-size: 11px; color: #888; letter-spacing: 1px; text-transform: uppercase; }
  h1 { font-size: 22px; font-weight: 700; color: #0D0A07; margin-bottom: 24px; }
  label { display: block; font-size: 12px; font-weight: 600; color: #555; letter-spacing: .5px;
    text-transform: uppercase; margin-bottom: 6px; }
  input[type=text], input[type=password] {
    width: 100%; padding: 10px 14px; border: 1px solid #DDD; border-radius: 6px;
    font-size: 14px; font-family: inherit; transition: border-color .15s;
    margin-bottom: 16px; }
  input:focus { outline: none; border-color: #BA0C2F; }
  .btn { width: 100%; padding: 12px; background: #BA0C2F; color: #fff; border: none;
    border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;
    letter-spacing: .5px; transition: background .15s; }
  .btn:hover { background: #9a0a27; }
  .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
  .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
  .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-dot"></div>
    <div>
      <div class="brand-text">Portfolio Manager</div>
      <div class="brand-sub">LATAM 2026</div>
    </div>
  </div>
  <h1>Sign in</h1>
  <?php if ($expired): ?>
    <div class="alert alert-warning">Session expired. Please sign in again.</div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" autocomplete="off">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required autofocus
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required />
    <button type="submit" class="btn">Sign in →</button>
  </form>
</div>
</body>
</html>
