const CACHE_NAME = 'fofana-stock-v1.2.0';
const OFFLINE_URL = '/offline';

// Assets to cache on install
const STATIC_CACHE_URLS = [
    '/',
    '/dashboard',
    '/orders',
    '/products',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// API endpoints to cache
const API_CACHE_URLS = [
    '/api/orders',
    '/api/products',
    '/api/dashboard/stats'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName => cacheName !== CACHE_NAME)
                        .map(cacheName => caches.delete(cacheName))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - network strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Handle navigation requests
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Handle API requests - Network First with fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            networkFirstStrategy(request)
        );
        return;
    }

    // Handle static assets - Cache First
    if (request.destination === 'style' || 
        request.destination === 'script' || 
        request.destination === 'image') {
        event.respondWith(
            cacheFirstStrategy(request)
        );
        return;
    }

    // Default - Network First
    event.respondWith(
        networkFirstStrategy(request)
    );
});

// Network First Strategy
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        
        // Cache successful responses
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return cachedResponse || new Response('Offline', { status: 503 });
    }
}

// Cache First Strategy
async function cacheFirstStrategy(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        return new Response('Resource not available offline', { status: 503 });
    }
}

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync-orders') {
        event.waitUntil(syncOfflineOrders());
    }
});

// Sync offline orders when back online
async function syncOfflineOrders() {
    try {
        const cache = await caches.open(CACHE_NAME);
        const offlineOrders = await cache.match('/offline-orders');
        
        if (offlineOrders) {
            const orders = await offlineOrders.json();
            
            for (const order of orders) {
                try {
                    await fetch('/api/orders', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(order)
                    });
                } catch (error) {
                    console.error('Failed to sync order:', error);
                }
            }
            
            // Clear synced orders
            await cache.delete('/offline-orders');
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Push notification handling
self.addEventListener('push', event => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/dashboard'
        },
        actions: [
            {
                action: 'view',
                title: 'View',
                icon: '/icons/action-view.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/icons/action-dismiss.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        const url = event.notification.data.url;
        event.waitUntil(
            clients.openWindow(url)
        );
    }
});