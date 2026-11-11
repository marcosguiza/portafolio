<?php
require_once __DIR__ . '/auth.php';
require_auth();
no_cache_headers();
header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$body = $raw ? (json_decode($raw, true) ?? []) : [];
$action = $body['action'] ?? '';

if (!verify_csrf($body['csrf'] ?? '')) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'CSRF mismatch']));
}

function respond(array $data): void { exit(json_encode($data)); }
function require_admin(): void {
    if (($_SESSION['manager_role'] ?? '') !== 'admin') {
        http_response_code(403);
        respond(['ok' => false, 'error' => 'Admin only']);
    }
}

function trilingual(array $b, string $key): array {
    return [
        'es' => trim($b[$key . '_es'] ?? ''),
        'en' => trim($b[$key . '_en'] ?? ''),
        'pt' => trim($b[$key . '_pt'] ?? ''),
    ];
}

function valid_brand(string $b): bool {
    return array_key_exists($b, BRANDS);
}

function valid_industry(string $i): bool {
    return empty($i) || array_key_exists($i, INDUSTRIES);
}

function nullable_url(?string $v): ?string {
    $v = trim($v ?? '');
    return $v !== '' ? $v : null;
}

switch ($action) {

    /* ── GET DATA ── */
    case 'get_data':
        respond(['ok' => true, 'data' => load_data()]);

    /* ── CASE STUDIES ── */
    case 'save_case': {
        $id    = trim($body['id'] ?? '');
        $title = trilingual($body, 'title');
        if (empty($title['es'])) respond(['ok' => false, 'error' => 'El título en español es obligatorio']);

        $brand    = $body['brand'] ?? 'skywave';
        $industry = $body['industry'] ?? '';
        if (!valid_brand($brand))    respond(['ok' => false, 'error' => 'Marca inválida']);
        if (!valid_industry($industry)) respond(['ok' => false, 'error' => 'Industria inválida']);

        $item = [
            'id'          => $id ?: gen_id('cs'),
            'title'       => $title,
            'description' => trilingual($body, 'desc'),
            'brand'       => $brand,
            'industry'    => $industry,
            'pdf'         => [
                'es' => nullable_url($body['pdf_es'] ?? ''),
                'en' => nullable_url($body['pdf_en'] ?? ''),
                'pt' => nullable_url($body['pdf_pt'] ?? ''),
            ],
            'cover'  => nullable_url($body['cover'] ?? ''),
            'date'   => $body['date'] ?? date('Y-m-d'),
            'active' => (bool)($body['active'] ?? true),
        ];

        $data = load_data();
        if ($id) {
            $found = false;
            foreach ($data['casestudies'] as &$cs) {
                if ($cs['id'] === $id) { $cs = $item; $found = true; break; }
            }
            if (!$found) respond(['ok' => false, 'error' => 'Item not found']);
        } else {
            $data['casestudies'][] = $item;
        }
        save_data($data);
        respond(['ok' => true, 'item' => $item, 'data' => $data]);
    }

    case 'delete_case': {
        $id   = trim($body['id'] ?? '');
        $data = load_data();
        $before = count($data['casestudies']);
        $data['casestudies'] = array_values(array_filter($data['casestudies'], fn($x) => $x['id'] !== $id));
        if (count($data['casestudies']) === $before) respond(['ok' => false, 'error' => 'Not found']);
        save_data($data);
        respond(['ok' => true, 'data' => $data]);
    }

    /* ── GALLERY ── */
    case 'save_gallery': {
        $id    = trim($body['id'] ?? '');
        $title = trilingual($body, 'title');
        if (empty($title['es'])) respond(['ok' => false, 'error' => 'El título en español es obligatorio']);

        $brand    = $body['brand'] ?? 'skywave';
        $industry = $body['industry'] ?? '';
        if (!valid_brand($brand))    respond(['ok' => false, 'error' => 'Marca inválida']);
        if (!valid_industry($industry)) respond(['ok' => false, 'error' => 'Industria inválida']);

        // Accept images[] array (new) or legacy image field
        $rawImgs = $body['images'] ?? null;
        if (is_array($rawImgs)) {
            $images = array_values(array_filter(array_map('trim', $rawImgs), fn($x) => $x !== ''));
        } else {
            $single = nullable_url($body['image'] ?? '');
            $images = $single ? [$single] : [];
        }
        if (empty($images)) respond(['ok' => false, 'error' => 'Al menos una imagen es obligatoria']);
        if (count($images) > 5) $images = array_slice($images, 0, 5);

        $item = [
            'id'      => $id ?: gen_id('gal'),
            'title'   => $title,
            'caption' => trilingual($body, 'caption'),
            'brand'   => $brand,
            'industry'=> $industry,
            'device'  => trim($body['device'] ?? ''),
            'images'  => $images,
            'active'  => (bool)($body['active'] ?? true),
        ];

        $data = load_data();
        if ($id) {
            $found = false;
            foreach ($data['gallery'] as &$g) {
                if ($g['id'] === $id) { $g = $item; $found = true; break; }
            }
            if (!$found) respond(['ok' => false, 'error' => 'Item not found']);
        } else {
            $data['gallery'][] = $item;
        }
        save_data($data);
        respond(['ok' => true, 'item' => $item, 'data' => $data]);
    }

    case 'delete_gallery': {
        $id   = trim($body['id'] ?? '');
        $data = load_data();
        $before = count($data['gallery']);
        $data['gallery'] = array_values(array_filter($data['gallery'], fn($x) => $x['id'] !== $id));
        if (count($data['gallery']) === $before) respond(['ok' => false, 'error' => 'Not found']);
        save_data($data);
        respond(['ok' => true, 'data' => $data]);
    }

    /* ── VIDEOS ── */
    case 'save_video': {
        $id    = trim($body['id'] ?? '');
        $title = trilingual($body, 'title');
        if (empty($title['es'])) respond(['ok' => false, 'error' => 'El título en español es obligatorio']);

        $ytId  = preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($body['youtube_id'] ?? ''));
        if (empty($ytId)) respond(['ok' => false, 'error' => 'El ID de YouTube es obligatorio']);

        $brand = $body['brand'] ?? 'skywave';
        if (!valid_brand($brand)) respond(['ok' => false, 'error' => 'Marca inválida']);

        $item = [
            'id'          => $id ?: gen_id('vid'),
            'title'       => $title,
            'description' => trilingual($body, 'desc'),
            'brand'       => $body['brand'] ?? 'skywave',
            'youtube_id'  => $ytId,
            'date'        => $body['date'] ?? date('Y-m-d'),
            'active'      => (bool)($body['active'] ?? true),
        ];

        $data = load_data();
        if ($id) {
            $found = false;
            foreach ($data['videos'] as &$v) {
                if ($v['id'] === $id) { $v = $item; $found = true; break; }
            }
            if (!$found) respond(['ok' => false, 'error' => 'Item not found']);
        } else {
            $data['videos'][] = $item;
        }
        save_data($data);
        respond(['ok' => true, 'item' => $item, 'data' => $data]);
    }

    case 'delete_video': {
        $id   = trim($body['id'] ?? '');
        $data = load_data();
        $before = count($data['videos']);
        $data['videos'] = array_values(array_filter($data['videos'], fn($x) => $x['id'] !== $id));
        if (count($data['videos']) === $before) respond(['ok' => false, 'error' => 'Not found']);
        save_data($data);
        respond(['ok' => true, 'data' => $data]);
    }

    /* ── USERS (admin only) ── */
    case 'get_users': {
        require_admin();
        $users = array_map(function($u) {
            unset($u['password_hash']);
            return $u;
        }, load_users());
        respond(['ok' => true, 'users' => $users]);
    }

    case 'save_user': {
        require_admin();
        $id       = trim($body['id'] ?? '');
        $username = trim($body['username'] ?? '');
        $role     = $body['role'] ?? 'editor';
        $pass     = $body['password'] ?? '';

        if (empty($username)) respond(['ok' => false, 'error' => 'El nombre de usuario es obligatorio']);
        if (!preg_match('/^[a-z0-9_]{3,30}$/', $username)) {
            respond(['ok' => false, 'error' => 'Usuario: solo letras minúsculas, números y _ (3-30 caracteres)']);
        }
        if (!in_array($role, ['admin', 'editor'])) respond(['ok' => false, 'error' => 'Rol inválido']);

        $users = load_users();

        if ($id) {
            // Edit
            $found = false;
            foreach ($users as &$u) {
                if ($u['id'] === $id) {
                    // Prevent removing last admin
                    if ($u['role'] === 'admin' && $role !== 'admin') {
                        $admins = array_filter($users, fn($x) => $x['role'] === 'admin');
                        if (count($admins) <= 1) respond(['ok' => false, 'error' => 'Debe quedar al menos un admin']);
                    }
                    $u['username'] = $username;
                    $u['role']     = $role;
                    if ($pass !== '') $u['password_hash'] = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                    $found = true;
                    break;
                }
            }
            if (!$found) respond(['ok' => false, 'error' => 'User not found']);
        } else {
            // Add
            if (empty($pass)) respond(['ok' => false, 'error' => 'La contraseña es obligatoria para nuevos usuarios']);
            if (strlen($pass) < 8) respond(['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
            foreach ($users as $u) {
                if ($u['username'] === $username) respond(['ok' => false, 'error' => 'El usuario ya existe']);
            }
            $users[] = [
                'id'            => gen_id('u'),
                'username'      => $username,
                'password_hash' => password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]),
                'role'          => $role,
                'created'       => date('Y-m-d'),
            ];
        }

        save_users($users);
        $safe = array_map(function($u) { unset($u['password_hash']); return $u; }, $users);
        respond(['ok' => true, 'users' => $safe]);
    }

    case 'delete_user': {
        require_admin();
        $id    = trim($body['id'] ?? '');
        $users = load_users();

        $target = null;
        foreach ($users as $u) { if ($u['id'] === $id) { $target = $u; break; } }
        if (!$target) respond(['ok' => false, 'error' => 'User not found']);

        // Cannot delete self
        if ($target['username'] === ($_SESSION['manager_user'] ?? '')) {
            respond(['ok' => false, 'error' => 'No puedes eliminar tu propio usuario']);
        }
        // Cannot delete last admin
        if ($target['role'] === 'admin') {
            $admins = array_filter($users, fn($x) => $x['role'] === 'admin');
            if (count($admins) <= 1) respond(['ok' => false, 'error' => 'Debe quedar al menos un admin']);
        }

        $users = array_values(array_filter($users, fn($x) => $x['id'] !== $id));
        save_users($users);
        $safe = array_map(function($u) { unset($u['password_hash']); return $u; }, $users);
        respond(['ok' => true, 'users' => $safe]);
    }

    /* ── LEADS (admin only) ── */
    case 'get_leads': {
        require_admin();
        respond(['ok' => true, 'leads' => load_leads()]);
    }

    case 'delete_lead': {
        require_admin();
        $id    = trim($body['id'] ?? '');
        $leads = load_leads();
        $before = count($leads);
        $leads = array_values(array_filter($leads, fn($x) => $x['id'] !== $id));
        if (count($leads) === $before) respond(['ok' => false, 'error' => 'Not found']);
        save_leads($leads);
        respond(['ok' => true, 'leads' => $leads]);
    }

    default:
        http_response_code(400);
        respond(['ok' => false, 'error' => 'Unknown action']);
}
