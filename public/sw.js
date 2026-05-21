// Solar Weather Station - Service Worker
// Cache-First strategy for static assets, Network-First for API/dynamic routes

const CACHE_NAME = 'solar-weather-v1';
const OFFLINE_PAGE = '/offline.html';

// Static assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/',
    '/css/style.css',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js',
];

// Install event: Pre-cache critical shell assets
self.addEventListener('install', event => {
    console.log('[SW] Installing Solar Weather Station PWA...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[SW] Pre-caching static assets...');
            // Use individual adds to avoid failing the whole install if one asset fails
            return Promise.allSettled(
                PRECACHE_ASSETS.map(url => cache.add(url).catch(err => console.warn('[SW] Skipped caching:', url, err)))
            );
        }).then(() => self.skipWaiting())
    );
});

// Activate event: Clean up old caches from previous versions
self.addEventListener('activate', event => {
    console.log('[SW] Activating Solar Weather Station PWA...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event: Smart routing strategy
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests entirely (POST for API, CSRF, etc.)
    if (event.request.method !== 'GET') return;

    // Skip Laravel API telemetry endpoints (always need live data)
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/dashboard/live') || url.pathname.startsWith('/dashboard/export')) {
        return; // Network only - no caching for live data
    }

    // For navigation requests: network-first, fall back to cache, then offline page
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Clone and cache fresh page responses
                    const cloned = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, cloned));
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request).then(cached => {
                        if (cached) return cached;
                        return caches.match(OFFLINE_PAGE) || new Response(
                            '<h1 style="font-family:sans-serif;text-align:center;padding:60px">You are offline. Please reconnect to view live telemetry.</h1>',
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    });
                })
        );
        return;
    }

    // For static assets (CSS, JS, fonts, images): Cache-First strategy
    event.respondWith(
        caches.match(event.request).then(cached => {
            if (cached) return cached;

            return fetch(event.request).then(response => {
                // Only cache successful responses for same-origin or CDN assets
                if (response && response.status === 200 && (response.type === 'basic' || response.type === 'cors')) {
                    const cloned = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, cloned));
                }
                return response;
            }).catch(() => {
                // Return nothing for non-critical asset failures
                return new Response('', { status: 404 });
            });
        })
    );
});
