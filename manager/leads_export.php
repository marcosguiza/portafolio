<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if (!verify_csrf($_GET['csrf'] ?? '')) {
    http_response_code(403);
    exit('CSRF mismatch');
}

$leads = load_leads();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="leads_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
// UTF-8 BOM for Excel compatibility
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['ID','Nombre','Cargo','Empresa','Email','Teléfono','Terminales','Otros','Idioma','Fecha','Fuente','Foto Tarjeta']);

foreach ($leads as $l) {
    fputcsv($out, [
        $l['id']             ?? '',
        $l['name']           ?? '',
        $l['jobtitle']       ?? '',
        $l['company']        ?? '',
        $l['email']          ?? '',
        $l['phone']          ?? '',
        implode('; ', $l['terminals'] ?? []),
        $l['other_interest'] ?? '',
        $l['lang']           ?? '',
        $l['created']        ?? '',
        $l['source']         ?? 'online',
        $l['card_image']     ?? '',
    ]);
}

fclose($out);
