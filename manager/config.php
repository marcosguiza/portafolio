<?php
define('MANAGER_VERSION', '1.0');
define('DATA_FILE',        realpath(__DIR__ . '/../apoyo') . '/content.json');
define('UPLOAD_DOCS_DIR',  realpath(__DIR__ . '/../apoyo') . '/assets/docs/');
define('UPLOAD_IMG_DIR',   realpath(__DIR__ . '/../apoyo') . '/assets/images/');
define('UPLOAD_DOCS_URL',  '/apoyo/assets/docs/');
define('UPLOAD_IMG_URL',   '/apoyo/assets/images/');
define('USERS_FILE',       __DIR__ . '/users.json');
define('SESSION_TIMEOUT',  7200);          // 2 hours
define('MAX_ATTEMPTS',     5);
define('LOCKOUT_SECONDS',  900);           // 15 min

// Allowed MIME types
define('ALLOWED_DOC_MIME',  ['application/pdf']);
define('ALLOWED_IMG_MIME',  ['image/jpeg','image/png','image/webp','image/gif']);
define('MAX_DOC_BYTES',     25 * 1024 * 1024);   // 25 MB
define('MAX_IMG_BYTES',     10 * 1024 * 1024);   // 10 MB

// Industries (shared with portfolios)
define('INDUSTRIES', [
    'container'  => 'Containers / Intermodal',
    'coldchain'  => 'Cold Chain',
    'drytrailer' => 'Dry Trailer',
    'heavyequip' => 'Heavy Equipment',
    'maritime'   => 'Maritime / Ports',
    'rail'       => 'Rail',
    'agro'       => 'Agro / Agriculture',
    'oilgas'     => 'Oil & Gas',
    'mining'     => 'Mining',
    'general'    => 'General / Cross-industry',
    'other'      => 'Other',
]);

// Brands
define('BRANDS', [
    'skywave'  => 'Skywave',
    'viachain' => 'VIACHAIN',
    'orbcomm'  => 'ORBCOMM',
]);

// Leads
define('LEADS_FILE', __DIR__ . '/leads.json');

// Site on/off switch — file exists = site is active/public
define('SITE_ACTIVE_FILE', __DIR__ . '/site_active');

// Docs access code — gates Hardware Guides and Installation Guides in the showroom
define('DOCS_ACCESS_CODE', 'SKYWAVE2026');

// Card photos captured from lead form (badge / business-card camera)
define('CARD_IMG_DIR', realpath(__DIR__ . '/../assets') . '/cards/');
define('CARD_IMG_URL', '/assets/cards/');

// Sync between live server and local router
define('LIVE_SERVER_URL', 'https://portafolio.magcmedia.cl');
define('SYNC_SECRET',     'sw2026_sync_K8mNpQ3rTv');
define('SYNC_STATUS_FILE', __DIR__ . '/sync_status.json');

// Telegram bot notification for new leads (leave empty to disable)
define('TELEGRAM_BOT_TOKEN', '8752487986:AAF1SDM13FJjUHU1reesM2VsNeWPzTsdyEQ');
define('TELEGRAM_CHAT_ID',   '-5247884166');

// Terminals for lead capture form
define('TERMINALS', [
    'skywave'  => ['label' => 'Skywave',  'items' => ['ST 8100','ST 9101','ST 6101','ST 2101','SC 1000']],
    'viachain' => ['label' => 'VIACHAIN', 'items' => ['CT 1000','CT 1010','CT 3600','CT 5300','GT 1030','GT 12XX','PT 6100','PT 7000','SC 1000']],
]);
