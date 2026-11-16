// persuratan/public/assets/js/offline-sync.js

/**
 * Offline Sync and PWA Manager for SI-PERSURATAN
 */
const OfflineManager = {
    dbName: 'persuratan_offline_db',
    dbVersion: 1,
    storeName: 'offline_queue',
    db: null,

    init() {
        this.openDatabase().then(() => {
            this.updateQueueBadge();
            this.listenConnection();
            this.initPwaInstall();
        });
    },

    openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(this.storeName)) {
                    db.createObjectStore(this.storeName, { keyPath: 'uuid' });
                }
            };
            request.onsuccess = (e) => {
                this.db = e.target.result;
                resolve(this.db);
            };
            request.onerror = (e) => reject(e);
        });
    },

    saveToQueue(endpoint, payload, typeName) {
        return new Promise((resolve, reject) => {
            const uuid = 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const item = {
                uuid: uuid,
                endpoint: endpoint,
                payload: payload,
                typeName: typeName,
                createdAt: new Date().toISOString(),
                status: 'pending'
            };

            const tx = this.db.transaction([this.storeName], 'readwrite');
            const store = tx.objectStore(this.storeName);
            store.add(item);

            tx.oncomplete = () => {
                this.updateQueueBadge();
                resolve(item);
            };
            tx.onerror = (e) => reject(e);
        });
    },

    getAllQueue() {
        return new Promise((resolve) => {
            if (!this.db) {
                resolve([]);
                return;
            }
            const tx = this.db.transaction([this.storeName], 'readonly');
            const store = tx.objectStore(this.storeName);
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => resolve([]);
        });
    },

    removeFromQueue(uuid) {
        return new Promise((resolve) => {
            const tx = this.db.transaction([this.storeName], 'readwrite');
            const store = tx.objectStore(this.storeName);
            store.delete(uuid);
            tx.oncomplete = () => {
                this.updateQueueBadge();
                resolve();
            };
        });
    },

    async syncAll() {
        const items = await this.getAllQueue();
        if (items.length === 0) {
            console.log('[OfflineManager] Tidak ada antrean offline untuk disinkron.');
            return { count: 0 };
        }

        let syncedCount = 0;
        for (const item of items) {
            try {
                const res = await fetch(item.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item.payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        await this.removeFromQueue(item.uuid);
                        syncedCount++;
                    }
                }
            } catch (err) {
                console.warn('[OfflineManager] Gagal sinkron item:', item.uuid, err);
                // Keep in IndexedDB as per Section 10 ("Data lokal tidak dihapus sebelum server konfirmasi sukses")
            }
        }

        this.updateQueueBadge();
        if (syncedCount > 0) {
            alert(`Berhasil menyinkronkan ${syncedCount} data dari antrean offline!`);
            window.location.reload();
        }
        return { count: syncedCount };
    },

    async updateQueueBadge() {
        const items = await this.getAllQueue();
        const badge = document.getElementById('offlineQueueBadge');
        const banner = document.getElementById('offlineSyncBanner');

        if (badge) {
            if (items.length > 0) {
                badge.innerText = items.length;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }

        if (banner) {
            if (items.length > 0) {
                banner.classList.remove('d-none');
                const textEl = banner.querySelector('.queue-text');
                if (textEl) textEl.innerText = `${items.length} draf offline siap disinkronkan.`;
            } else {
                banner.classList.add('d-none');
            }
        }
    },

    listenConnection() {
        const updateStatus = () => {
            const isOnline = navigator.onLine;
            const statusDot = document.getElementById('networkStatusIndicator');
            const statusText = document.getElementById('networkStatusText');

            if (statusDot) {
                if (isOnline) {
                    statusDot.className = 'status-indicator bg-success';
                    if (statusText) statusText.innerText = 'Online';
                    // Auto attempt sync when connection returns
                    this.syncAll();
                } else {
                    statusDot.className = 'status-indicator bg-warning';
                    if (statusText) statusText.innerText = 'Offline';
                }
            }
        };

        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);
        updateStatus();
    },

    initPwaInstall() {
        let deferredPrompt;
        const installBtn = document.getElementById('btnInstallPwa');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installBtn) {
                installBtn.classList.remove('d-none');
                installBtn.addEventListener('click', async () => {
                    installBtn.classList.add('d-none');
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to install: ${outcome}`);
                    deferredPrompt = null;
                });
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Register Service Worker if supported
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then((reg) => console.log('[PWA] Service Worker terdaftar:', reg.scope))
            .catch((err) => console.warn('[PWA] Service Worker gagal daftar:', err));
    }

    OfflineManager.init();

    // Trigger sync button if present
    const btnSync = document.getElementById('btnManualSync');
    if (btnSync) {
        btnSync.addEventListener('click', () => OfflineManager.syncAll());
    }
});
