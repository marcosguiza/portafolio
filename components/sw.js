// SKYWAVE PWA service worker
// Strategy: cache-first for app shell + assets, network-first for HTML.
// Bump CACHE_VERSION to force clients to refetch everything.

const CACHE_VERSION = 'skywave-v5';
const APP_SHELL = [
  './index.html',
  './Portafolio%20Skywave.html',
  './Portafolio%20Viachain.html',
  './manifest.webmanifest',
  './data.js',
  './viachain_data.js',
  './components/00_core.jsx',
  './components/01_overview.jsx',
  './components/02_showroom.jsx',
  './components/03_scenes.jsx',
  './components/04_apps.jsx',
  './components/05_accessories.jsx',
  // Skywave assets
  './assets/skywave.png',
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
  // Viachain brand
  './assets/viachain.png',
  // Viachain product images
  './assets/products/CT1000-VIACHAIN.png',
  './assets/products/CT1010-VIACHAIN.png',
  './assets/products/CT3600-VIACHAIN.png',
  './assets/products/CT5300-VIACHAIN.png',
  './assets/products/GT1030-VIACHAIN.png',
  './assets/products/GT12XX-VIACHAIN.png',
  './assets/products/PT6X00-VIACHAIN.png',
  './assets/products/PT7000-VIACHAIN.png',
  './assets/products/SC1000-VIACHAIN.png',
  // Shared accessory images (Skywave + Viachain)
  './assets/accessories/DB300_SKYWAVE.png',
  './assets/accessories/TS%20310-VIACHAIN.png',
  './assets/accessories/DS300-VIACHAIN.png',
  // Skywave-only accessory images
  './assets/accessories/Sentrius_BT610-SKYWAVE.png',
  './assets/accessories/BTSMP1_Eye_Sensor-SKYWAVE.png',
  './assets/accessories/dute_s7_ble_SKYWAVE.png',
  // Viachain accessory images
  './assets/accessories/CS500_VIACHAIN.png',
  './assets/accessories/CS511-VIACHAIN.png',
  './assets/accessories/DS100-VIACHAIN.png',
  './assets/accessories/DS300-VIACHAIN.png',
  './assets/accessories/Euroscan_Back_Door-VIACHAIN.png',
  './assets/accessories/Euroscan_Side_Door-VIACHAIN.png',
  './assets/accessories/Euroscan_Temperature-VIACHAIN.png',
  './assets/accessories/FS210-VIACHAIN.png',
  './assets/accessories/IS400-VIACHAIN.png',
  './assets/accessories/TS%20310-VIACHAIN.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_VERSION);
    await Promise.all(APP_SHELL.map((url) => cache.add(url).catch(() => null)));
    self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(k => k !== CACHE_VERSION).map(k => caches.delete(k)));
    self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);

  // Ignore cross-origin (fonts from googleapis, CDN scripts — let the browser cache them)
  if (url.origin !== self.location.origin) return;

  const isHTML = req.mode === 'navigate' || req.destination === 'document';

  if (isHTML) {
    // Network-first for HTML so updates land fast; fall back to cache offline.
    event.respondWith((async () => {
      try {
        const fresh = await fetch(req);
        const cache = await caches.open(CACHE_VERSION);
        cache.put(req, fresh.clone());
        return fresh;
      } catch {
        return (await caches.match(req)) || caches.match('./Portafolio%20Skywave.html');
      }
    })());
    return;
  }

  // Cache-first for all static assets (js, jsx, png, pdf, webmanifest)
  event.respondWith((async () => {
    const cached = await caches.match(req);
    if (cached) return cached;
    try {
      const fresh = await fetch(req);
      if (fresh.ok) {
        const cache = await caches.open(CACHE_VERSION);
        cache.put(req, fresh.clone());
      }
      return fresh;
    } catch {
      return new Response('', { status: 503 });
    }
  })());
});
