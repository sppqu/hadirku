// Service Worker for HadirKU PWA
const CACHE_NAME = 'hadirku-cache-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Default network-first strategy
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
