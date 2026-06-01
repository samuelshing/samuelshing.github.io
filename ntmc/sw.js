const CACHE_NAME = 'pwa-demo-v1';
const urlsToCache = [
    './',
    './index.html',
    './manifest.json'
];

// 安裝 Service Worker 並快取基本檔案
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

// 攔截網路請求，若無網路則提供快取內容 (離線支援)
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});