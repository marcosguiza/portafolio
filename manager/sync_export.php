<?php
require_once __DIR__ . '/config.php';

if (!hash_equals(SYNC_SECRET, $_GET['token'] ?? '')) {
    http_response_code(403); exit;
}

$root = dirname(__DIR__);
$tmp  = tempnam('/tmp', 'sw_export_') . '.tar.gz';

exec("cd " . escapeshellarg($root) . " && tar czf " . escapeshellarg($tmp)
    . " --exclude='./manager/users.json'"
    . " --exclude='./manager/leads.json'"
    . " .", $out, $rc);

if ($rc !== 0 || !file_exists($tmp)) {
    http_response_code(500); exit('export failed');
}

header('Content-Type: application/gzip');
header('Content-Disposition: attachment; filename="portafolio.tar.gz"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
unlink($tmp);
