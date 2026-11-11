<?php
require_once __DIR__ . '/portfolio_auth.php';
require_portfolio_auth();
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile(__DIR__ . '/Portafolio Viachain.html');
