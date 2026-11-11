<?php
ob_start();
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();
ini_set('display_errors', '0');

if (($_SESSION['manager_role'] ?? '') !== 'admin') {
    cat_json_exit(['ok' => false, 'error' => 'Forbidden'], 403);
}

header('Content-Type: application/json');

function cat_json_exit(array $payload, int $status = 200): void {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err || !in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) return;
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => 'Error PHP: ' . basename($err['file']) . ':' . $err['line'] . ' · ' . $err['message'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
});

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    cat_json_exit(['ok' => false, 'error' => 'Bad request']);
}

if (!verify_csrf($body['csrf'] ?? '')) {
    cat_json_exit(['ok' => false, 'error' => 'CSRF mismatch'], 403);
}

require_once __DIR__ . '/catalog_generator.php';

$action = $body['action'] ?? '';
$brand  = $body['brand']  ?? '';

if (!in_array($brand, ['skywave', 'viachain'], true)) {
    cat_json_exit(['ok' => false, 'error' => 'Invalid brand']);
}

$json_path = __DIR__ . "/{$brand}_catalog.json";

function cat_load(string $path): array {
    if (!file_exists($path)) return ['products'=>[],'accessories'=>[],'datasheets'=>(object)[],'hardwareGuides'=>(object)[],'installGuides'=>(object)[]];
    $d = json_decode(file_get_contents($path), true);
    return is_array($d) ? $d : ['products'=>[],'accessories'=>[],'datasheets'=>(object)[],'hardwareGuides'=>(object)[],'installGuides'=>(object)[]];
}

function cat_save(string $path, array $catalog): bool {
    $tmp = $path . '.tmp';
    $ok  = file_put_contents($tmp, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($ok === false) return false;
    return rename($tmp, $path);
}

function cat_respond(array $catalog, string $brand): void {
    $errors = catalog_regenerate();
    if ($errors) {
        cat_json_exit(['ok' => false, 'error' => implode('; ', $errors)]);
    }
    cat_json_exit(['ok' => true, 'catalog' => $catalog, 'brand' => $brand]);
}

// ─── PRODUCTS ───────────────────────────────────────────────────────────────

if ($action === 'save_content') {
    $hero = $body['hero'] ?? null;
    $closing = $body['closing'] ?? null;
    if (!is_array($hero) || !is_array($closing)) {
        cat_json_exit(['ok' => false, 'error' => 'Contenido inválido']);
    }

    $cat = cat_load($json_path);
    $cat['hero'] = $hero;
    $cat['closing'] = $closing;

    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

if ($action === 'save_product') {
    $p = $body['product'] ?? null;
    if (!$p || empty($p['id']) || empty($p['name'])) {
        cat_json_exit(['ok' => false, 'error' => 'ID y Nombre son obligatorios']);
    }
    $p['id'] = preg_replace('/[^a-z0-9_]/', '', strtolower($p['id']));
    if (!$p['id']) cat_json_exit(['ok' => false, 'error' => 'ID inválido']);

    $cat = cat_load($json_path);
    $idx = array_search($p['id'], array_column($cat['products'], 'id'));
    if ($idx !== false) {
        $cat['products'][$idx] = $p;
    } else {
        $cat['products'][] = $p;
    }
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

if ($action === 'delete_product') {
    $id = $body['id'] ?? '';
    $cat = cat_load($json_path);
    $cat['products'] = array_values(array_filter($cat['products'], fn($p) => $p['id'] !== $id));
    unset($cat['datasheets'][$id], $cat['hardwareGuides'][$id], $cat['installGuides'][$id]);
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

if ($action === 'move_product') {
    $id  = $body['id']  ?? '';
    $dir = $body['dir'] ?? 'up';
    $cat = cat_load($json_path);
    $arr = &$cat['products'];
    $idx = array_search($id, array_column($arr, 'id'));
    if ($idx !== false) {
        $swap = $dir === 'up' ? $idx - 1 : $idx + 1;
        if ($swap >= 0 && $swap < count($arr)) {
            [$arr[$idx], $arr[$swap]] = [$arr[$swap], $arr[$idx]];
        }
    }
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

// ─── ACCESSORIES ─────────────────────────────────────────────────────────────

if ($action === 'save_accessory') {
    $a = $body['accessory'] ?? null;
    if (!$a || empty($a['id']) || empty($a['name'])) {
        cat_json_exit(['ok' => false, 'error' => 'ID y Nombre son obligatorios']);
    }
    $a['id'] = preg_replace('/[^a-z0-9_]/', '', strtolower($a['id']));
    if (!$a['id']) cat_json_exit(['ok' => false, 'error' => 'ID inválido']);

    $cat = cat_load($json_path);
    $idx = array_search($a['id'], array_column($cat['accessories'], 'id'));
    if ($idx !== false) {
        $cat['accessories'][$idx] = $a;
    } else {
        $cat['accessories'][] = $a;
    }
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

if ($action === 'delete_accessory') {
    $id  = $body['id'] ?? '';
    $cat = cat_load($json_path);
    $cat['accessories'] = array_values(array_filter($cat['accessories'], fn($a) => $a['id'] !== $id));
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

if ($action === 'move_accessory') {
    $id  = $body['id']  ?? '';
    $dir = $body['dir'] ?? 'up';
    $cat = cat_load($json_path);
    $arr = &$cat['accessories'];
    $idx = array_search($id, array_column($arr, 'id'));
    if ($idx !== false) {
        $swap = $dir === 'up' ? $idx - 1 : $idx + 1;
        if ($swap >= 0 && $swap < count($arr)) {
            [$arr[$idx], $arr[$swap]] = [$arr[$swap], $arr[$idx]];
        }
    }
    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

// ─── DOCUMENTS ───────────────────────────────────────────────────────────────

if ($action === 'save_docs') {
    $product_id = $body['product_id'] ?? '';
    if (!$product_id) cat_json_exit(['ok' => false, 'error' => 'product_id requerido']);

    $cat = cat_load($json_path);

    // Datasheets: can be a single {es,en,pt} object or an array [{en,label},...] or null
    $ds = $body['datasheets'] ?? null;
    if ($ds !== null) {
        $cat['datasheets'][$product_id] = $ds;
    } else {
        unset($cat['datasheets'][$product_id]);
    }

    $hw = $body['hardwareGuide'] ?? null;
    if ($hw) $cat['hardwareGuides'][$product_id] = $hw;
    else      unset($cat['hardwareGuides'][$product_id]);

    $ig = $body['installGuide'] ?? null;
    if ($ig) $cat['installGuides'][$product_id] = $ig;
    else      unset($cat['installGuides'][$product_id]);

    if (!cat_save($json_path, $cat)) cat_json_exit(['ok' => false, 'error' => 'Error guardando JSON']);
    cat_respond($cat, $brand);
}

cat_json_exit(['ok' => false, 'error' => "Unknown action: {$action}"]);
