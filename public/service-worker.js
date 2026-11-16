// persuratan/public/service-worker.js
const CACHE_NAME = 'si-persuratan-v1.0.0';

const STATIC_ASSETS = [
    './',
    './dashboard.php',
    './offline.html',
    './assets/css/bootstrap.min.css',
    './assets/css/custom.css',
    './assets/js/bootstrap.bundle.min.js',
    './assets/js/chart.umd.min.js',
    './assets/js/lucide.min.js',
    './assets/js/offline-sync.js',
    './assets/img/icon-192.png',
    './assets/img/icon-512.png',
    './assets/img/logo-instansi.svg',
    './manifest.json'
];

// 1. Install event: pre-cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Caching static assets');
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[Service Worker] Pre-cache partial fail:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate event: clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        console.log('[Service Worker] Removing old cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch event: Stale-While-Revalidate for static assets, Network-First for dynamic pages
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and external domains
    if (request.method !== 'GET' || url.origin !== location.origin) {
        return;
    }

    // Dynamic pages (HTML requests): Network first, then cache, then offline fallback
    if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                })
                .catch(() => {
                    return caches.match(request).then((cachedResponse) => {
                        return cachedResponse || caches.match('./offline.html');
                    });
                })
        );
        return;
    }

    // Static assets (CSS, JS, Images): Cache first, fallback to network
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                // Fetch in background to revalidate cache
                fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                    }
                }).catch(() => {});
                return cachedResponse;
            }
            return fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            });
        })
    );
});
