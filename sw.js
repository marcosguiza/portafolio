/* Global PWA service worker
   Strategy:
   - HTML / navigation: network-first, fallback to cache
   - Static local assets: cache-first
   - Same-origin PDFs and images: cache-first after first fetch
*/

const CACHE_VERSION = 'portfolio-shell-v10';
const CACHE_HTML = `${CACHE_VERSION}-html`;
const CACHE_ASSETS = `${CACHE_VERSION}-assets`;

const NETWORK_ONLY_PATHS = [
  '/manager/',
  '/apoyo/',
  '/login.php',
  '/logout.php',
  '/portfolio_auth.php',
  '/lead.php',
  '/lead_sync.php',
  '/card_upload.php'
];

const NETWORK_ONLY_FILE_RE = /\/(?:manager\/)?(?:api|catalog_api|sync|sync_status|sync_export|leads_export|upload|img_upload|site_toggle)\.php$/;

const APP_SHELL = [
  './',
  './index.html',
  './manifest.webmanifest',
  './Portafolio%20Skywave.html',
  './Portafolio%20Viachain.html',
  './data.js',
  './viachain_data.js',

  './components/00_core.jsx',
  './components/01_overview.jsx',
  './components/02_showroom.jsx',
  './components/03_scenes.jsx',
  './components/04_apps.jsx',
  './components/05_accessories.jsx',

  './assets/skywave.png',
  './assets/viachain.png',
  './assets/icon-192.png',
  './assets/icon-512.png',
  './assets/icon-maskable-512.png',

  './assets/products/SC1000.png',
  './assets/products/ST2100.png',
  './assets/products/ST4000.png',
  './assets/products/ST6000.png',
  './assets/products/ST6100.png',
  './assets/products/ST8100.png',
  './assets/products/ST9100.png',

  './assets/products/CT1000-VIACHAIN.png',
  './assets/products/CT1010-VIACHAIN.png',
  './assets/products/CT3600-VIACHAIN.png',
  './assets/products/CT5300-VIACHAIN.png',
  './assets/products/GT1030-VIACHAIN.png',
  './assets/products/GT12XX-VIACHAIN.png',
  './assets/products/PT6X00-VIACHAIN.png',
  './assets/products/PT7000-VIACHAIN.png',
  './assets/products/SC1000-VIACHAIN.png',

  './assets/accessories/DB300-SKYWAVE.png',
  './assets/accessories/Sentrius_BT610-SKYWAVE.png',
  './assets/accessories/BTSMP1_Eye_Sensor-SKYWAVE.png',
  './assets/accessories/dute_s7_ble_SKYWAVE.png',

  './assets/accessories/CS500_VIACHAIN.png',
  './assets/accessories/CS511-VIACHAIN.png',
  './assets/accessories/DS100-VIACHAIN.png',
  './assets/accessories/DS300-VIACHAIN.png',
  './assets/accessories/Euroscan_Back_Door-VIACHAIN.png',
  './assets/accessories/Euroscan_Side_Door-VIACHAIN.png',
  './assets/accessories/Euroscan_Temperature-VIACHAIN.png',
  './assets/accessories/FS210-VIACHAIN.png',
  './assets/accessories/IS400-VIACHAIN.png',
  './assets/accessories/TS%20310-VIACHAIN.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_ASSETS);
    await Promise.all(
      APP_SHELL.map((url) => cache.add(url).catch(() => null))
    );
    self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(
      keys
        .filter((key) => ![CACHE_HTML, CACHE_ASSETS].includes(key))
        .map((key) => caches.delete(key))
    );
    self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  if (url.origin !== self.location.origin) return;

  if (isNetworkOnly(url)) return;

  const isHTML =
    req.mode === 'navigate' ||
    req.destination === 'document' ||
    req.headers.get('accept')?.includes('text/html');

  if (isHTML) {
    event.respondWith(networkFirstHTML(req));
    return;
  }

  event.respondWith(cacheFirstAsset(req));
});

function isNetworkOnly(url) {
  const path = url.pathname;
  return NETWORK_ONLY_PATHS.some((prefix) => path === prefix || path.startsWith(prefix)) ||
    NETWORK_ONLY_FILE_RE.test(path);
}

async function networkFirstHTML(req) {
  const cache = await caches.open(CACHE_HTML);

  try {
    const fresh = await fetch(req);
    if (fresh && fresh.ok) {
      cache.put(req, fresh.clone());
    }
    return fresh;
  } catch (err) {
    const cached = await cache.match(req);
    if (cached) return cached;

    const fallbackSkywave = await cache.match('./Portafolio%20Skywave.html');
    if (fallbackSkywave) return fallbackSkywave;

    const fallbackRoot = await caches.match('./index.html');
    if (fallbackRoot) return fallbackRoot;

    return new Response('Offline', {
      status: 503,
      headers: { 'Content-Type': 'text/plain; charset=utf-8' }
    });
  }
}

async function cacheFirstAsset(req) {
  const cached = await caches.match(req);
  if (cached) return cached;

  try {
    const fresh = await fetch(req);
    if (fresh && fresh.ok) {
      const cache = await caches.open(CACHE_ASSETS);
      cache.put(req, fresh.clone());
    }
    return fresh;
  } catch (err) {
    return new Response('', { status: 503 });
  }
}
