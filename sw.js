const CACHE_NAME = 'qiroatul-kutub-v1';
const urlsToCache = [
  '/',
  '/index.html',
  '/menu.html',
  '/assets/images/logo.png'
];

// Instalasi Service Worker dan Cache Awal
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Mencegat request untuk mode PWA
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});