const CACHE_NAME = 'zolder-v3';
const ASSETS = [
    './',
    './index.php',
    './header.php',
    // './assets/css/style.css', // Removed as we use inline styles in header.php
    './assets/icon_v3.png',
    './assets/js/offline-db.js?v=2.1', // Version query string to bust cache
    './assets/lib/html5-qrcode.min.js',
    './assets/lib/qrcode.min.js',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.map(key => {
                if (key !== CACHE_NAME) return caches.delete(key);
            })
        )).then(() => self.clients.claim()) // Take control immediately
    );
});

self.addEventListener('fetch', event => {
    if (event.request.mode === 'navigate') {
        // Network first for pages to ensure fresh content
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request) || caches.match('./index.php');
            })
        );
    } else {
        // Stale-while-revalidate for assets
        event.respondWith(
            caches.open(CACHE_NAME).then(cache => {
                return cache.match(event.request).then(response => {
                    const fetchPromise = fetch(event.request).then(networkResponse => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                    return response || fetchPromise;
                });
            })
        );
    }
});
