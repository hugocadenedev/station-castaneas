const STATIC_CACHE = 'castaneas-static-v1';
const DYNAMIC_CACHE = 'castaneas-dynamic-v1';
const APP_SHELL = ['/', '/manifest.webmanifest', '/favicon.ico', '/icon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(APP_SHELL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => ![STATIC_CACHE, DYNAMIC_CACHE].includes(key))
                .map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    const isStaticAsset = requestUrl.pathname.startsWith('/build/') || /\.(?:js|css|png|jpg|jpeg|svg|ico|webp)$/i.test(requestUrl.pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(event.request).then((networkResponse) => {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => cache.put(event.request, responseClone));
                    return networkResponse;
                });
            })
        );

        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                const responseClone = networkResponse.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => cache.put(event.request, responseClone));
                return networkResponse;
            })
            .catch(() => caches.match(event.request).then((cachedResponse) => cachedResponse || caches.match('/')))
    );
});