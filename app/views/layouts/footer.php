<?php
// persuratan/app/views/layouts/footer.php
if (!defined('APP_INIT')) exit;
?>
        </main>

        <!-- Desktop Footer -->
        <footer class="mt-auto px-4 py-3 bg-white border-top text-center text-muted small d-none d-md-block">
            <div class="d-flex justify-content-between align-items-center">
                <span>&copy; <?= date('Y') ?> <strong>SI-PERSURATAN</strong> • Badan Pengembangan Teknologi Informasi & Kearsipan</span>
                <span>Database: <strong class="text-primary">SQLite Persistent WAL</strong> • PWA Ready</span>
            </div>
        </footer>
    </div>
</div>

<!-- Mobile Bottom Navigation for Android PWA -->
<?php require_once APP_PATH . '/views/layouts/mobile_nav.php'; ?>

<!-- Bootstrap Bundle JS & Offline Sync JS (Local + CDN Fallback) -->
<script src="<?= url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/offline-sync.js') ?>"></script>
<script>
    // Initialize Lucide Icons reliably
    function initAppIcons() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }
    initAppIcons();
    window.addEventListener('DOMContentLoaded', initAppIcons);
    window.addEventListener('load', initAppIcons);

    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appSidebar = document.getElementById('appSidebar');
    if (sidebarToggle && appSidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            appSidebar.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && appSidebar.classList.contains('show')) {
                if (!appSidebar.contains(e.target) && e.target !== sidebarToggle) {
                    appSidebar.classList.remove('show');
                }
            }
        });
    }
</script>
</body>
</html>
