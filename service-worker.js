const CACHE_NAME = 'neuralpress-core-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/assets/css/tailwind.css',
  '/manifest.json',
  'https://cdn.tailwindcss.com',
  'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Pre-caching core assets');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            console.log('[Service Worker] Dropping outdated cache', key);
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch Event with Network-Falling-Back-to-Cache Strategy
self.addEventListener('fetch', (event) => {
  // Let the browser handle POST, non-http, or admin routes naturally
  if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Bypass for Translate paths and dynamic API queries if needed
  if (event.request.url.includes('/api/') || event.request.url.includes('translate.google')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }
        // Cache clone of newly-resolved site pages/assets dynamically
        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        return networkResponse;
      })
      .catch(() => {
        // Fallback to cache
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // offline webpage fallback can be added here or standard text
          if (event.request.headers.get('accept').includes('text/html')) {
            return new Response(`
              <div style="font-family: sans-serif; text-align: center; padding: 50px; background: #121212; color: #fff;">
                <h1 style="color: #bb1919;">Offline Mode - NeuralPress</h1>
                <p>You are currently offline. Pages you read previously have been stored locally.</p>
                <a href="/" style="display: inline-block; background: #bb1919; color: #fff; text-decoration: none; padding: 10px 20px; margin-top: 15px; border-radius: 4px;">Retry Connection</a>
              </div>
            `, {
              headers: { 'Content-Type': 'text/html' }
            });
          }
        });
      })
  );
});
