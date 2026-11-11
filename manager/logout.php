<?php
require_once __DIR__ . '/auth.php';
session_start_secure();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/', '', true, true);
header('Location: index.php'); exit;
