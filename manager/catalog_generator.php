<?php
// Regenerates data.js and viachain_data.js from JSON catalogs and bumps sw.js cache version.
// Called internally after any catalog save/delete. Never called directly via HTTP.

function catalog_regenerate(): array {
    $base    = dirname(__DIR__);
    $errors  = [];
    $changed = false;

    $map = [
        'skywave'  => ['json' => __DIR__ . '/skywave_catalog.json',  'js' => $base . '/data.js'],
        'viachain' => ['json' => __DIR__ . '/viachain_catalog.json', 'js' => $base . '/viachain_data.js'],
    ];

    foreach ($map as $brand => $paths) {
        if (!file_exists($paths['json'])) { $errors[] = "Missing {$brand} catalog JSON"; continue; }
        if (!file_exists($paths['js']))   { $errors[] = "Missing {$brand} JS file"; continue; }

        $cat = json_decode(file_get_contents($paths['json']), true);
        if (!is_array($cat)) { $errors[] = "Invalid JSON for {$brand}"; continue; }

        $content = file_get_contents($paths['js']);
        $orig    = $content;

        $products    = $cat['products']      ?? [];
        $accessories = $cat['accessories']   ?? [];
        $hero        = $cat['hero']          ?? null;
        $closing     = $cat['closing']       ?? null;
        $hw          = $cat['hardwareGuides'] ?? (object)[];
        $inst        = $cat['installGuides']  ?? (object)[];
        $ds          = $cat['datasheets']     ?? (object)[];

        // Build JS strings for each mutable section
        $enc = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        $prod_body  = "  products: " . catalog_indent(json_encode($products, $enc), 2) . ",";
        $acc_body   = "  accessories: " . catalog_indent(json_encode($accessories, $enc), 2) . ",";
        $hero_body = is_array($hero)
            ? "  hero: " . catalog_indent(json_encode($hero, $enc), 2) . ","
            : null;
        $closing_body = is_array($closing)
            ? "  closing: " . catalog_indent(json_encode($closing, $enc), 2) . ","
            : null;

        // Docs section differs between skywave and viachain (key order)
        if ($brand === 'skywave') {
            $docs_body = implode("\n\n", [
                "  hardwareGuides: " . catalog_indent(json_encode($hw, $enc), 2) . ",",
                "  // Installation guides — disponibles en inglés. ST4000/ST6000 sin guía de instalación.",
                "  installGuides: " . catalog_indent(json_encode($inst, $enc), 2) . ",",
                "  // Datasheet por producto y por idioma — archivo local en assets/datasheets/",
                "  // Si el idioma seleccionado no existe, se hace fallback a ES → EN → PT.",
                "  datasheets: " . catalog_indent(json_encode($ds, $enc), 2) . ",",
            ]);
        } else {
            $docs_body = implode("\n\n", [
                "  datasheets: " . catalog_indent(json_encode($ds, $enc), 2) . ",",
                "  hardwareGuides: " . catalog_indent(json_encode($hw, $enc), 2) . ",",
                "  installGuides: " . catalog_indent(json_encode($inst, $enc), 2) . ",",
            ]);
        }

        if ($hero_body !== null) {
            $content = catalog_replace_section($content, 'HERO', $hero_body);
        }
        if ($closing_body !== null) {
            $content = catalog_replace_section($content, 'CLOSING', $closing_body);
        }
        $content = catalog_replace_section($content, 'PROD', $prod_body);
        $content = catalog_replace_section($content, 'ACC',  $acc_body);
        $content = catalog_replace_section($content, 'DOCS', $docs_body);

        if ($content !== $orig) {
            $tmp = $paths['js'] . '.tmp';
            if (file_put_contents($tmp, $content) !== false) {
                rename($tmp, $paths['js']);
                $changed = true;
            } else {
                $errors[] = "Cannot write {$brand} JS file";
            }
        }
    }

    if ($changed && empty($errors)) {
        catalog_bump_sw($base . '/sw.js');
    }

    return $errors;
}

function catalog_replace_section(string $content, string $marker, string $body): string {
    $s = "/*{$marker}_S*/";
    $e = "/*{$marker}_E*/";
    $pat = '/' . preg_quote($s, '/') . '.*?' . preg_quote($e, '/') . '/s';
    $rep = $s . "\n" . $body . "\n  " . $e;
    $result = preg_replace($pat, $rep, $content);
    return $result ?? $content;
}

function catalog_indent(string $json, int $spaces): string {
    $pad = str_repeat(' ', $spaces);
    return str_replace("\n", "\n" . $pad, $json);
}

function catalog_bump_sw(string $sw_path): void {
    if (!file_exists($sw_path)) return;
    $content = file_get_contents($sw_path);
    $new = preg_replace_callback(
        "/'((?:skywave|portfolio-shell)-v)(\d+)'/",
        fn($m) => "'" . $m[1] . ((int)$m[2] + 1) . "'",
        $content
    );
    if ($new && $new !== $content) {
        file_put_contents($sw_path, $new);
    }
}
