// PWA Installation and Push Notifications
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.swRegistration = null;
        this.init();
    }

    async init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register('/sw.js');
                console.log('SW registered:', this.swRegistration);
                
                // Handle updates
                this.swRegistration.addEventListener('updatefound', () => {
                    this.showUpdateAvailable();
                });
            } catch (error) {
                console.error('SW registration failed:', error);
            }
        }

        // Handle install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        // Handle app installed
        window.addEventListener('appinstalled', () => {
            this.hideInstallButton();
            this.showToast('App installed successfully!');
        });

        // Request notification permission
        this.requestNotificationPermission();
    }

    showInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
            installBtn.addEventListener('click', () => this.installApp());
        }
    }

    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    }

    async installApp() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            this.showToast('Installing app...');
        }
        
        this.deferredPrompt = null;
        this.hideInstallButton();
    }

    showUpdateAvailable() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'fixed top-0 left-0 right-0 bg-blue-600 text-white p-4 z-50';
        updateBanner.innerHTML = `
            <div class="flex justify-between items-center">
                <span>A new version is available!</span>
                <button onclick="window.location.reload()" 
                        class="bg-white text-blue-600 px-4 py-2 rounded text-sm font-medium">
                    Update
                </button>
            </div>
        `;
        document.body.prepend(updateBanner);
    }

    async requestNotificationPermission() {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                this.subscribeToPush();
            }
        } else if (Notification.permission === 'granted') {
            this.subscribeToPush();
        }
    }

    async subscribeToPush() {
        if (!this.swRegistration) return;

        try {
            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(window.vapidPublicKey)
            });

            // Send subscription to server
            await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(subscription)
            });

            console.log('Push subscription successful');
        } catch (error) {
            console.error('Push subscription failed:', error);
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Offline data management
    async saveOfflineOrder(orderData) {
        if ('caches' in window) {
            const cache = await caches.open('fofana-stock-v1.2.0');
            const offlineOrders = await this.getOfflineOrders();
            offlineOrders.push({
                ...orderData,
                timestamp: Date.now(),
                synced: false
            });
            
            await cache.put('/offline-orders', new Response(JSON.stringify(offlineOrders)));
            
            // Register background sync
            if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('background-sync-orders');
            }
        }
    }

    async getOfflineOrders() {
        if ('caches' in window) {
            const cache = await caches.open('fofana-stock-v1.2.0');
            const response = await cache.match('/offline-orders');
            return response ? await response.json() : [];
        }
        return [];
    }
}

// Initialize PWA
const pwaManager = new PWAManager();
window.pwaManager = pwaManager;