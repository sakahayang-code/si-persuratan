<?php
// persuratan/app/views/layouts/footer.php
if (!defined('APP_INIT')) exit;
?>
        </main>

        <!-- Desktop Footer -->
        <footer class="mt-auto px-6 py-4 bg-white border-t border-slate-200 text-xs text-slate-500 hidden md:flex justify-between items-center">
            <span>&copy; <?= date('Y') ?> <strong>SI-PERSURATAN</strong> • Sistem Informasi Persuratan Digital & Arsip Terpadu</span>
            <span class="flex items-center gap-1.5">
                <i data-lucide="database" class="w-3.5 h-3.5 text-blue-600"></i>
                SQLite Persistent WAL • PWA Ready
            </span>
        </footer>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<?php require_once APP_PATH . '/views/layouts/mobile_nav.php'; ?>

<!-- Bootstrap Bundle JS & Offline Sync JS -->
<script src="<?= url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/offline-sync.js') ?>"></script>

<script>
    // Initialize Lucide Icons
    function initAppIcons() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }
    initAppIcons();
    document.addEventListener('DOMContentLoaded', initAppIcons);
    window.addEventListener('load', initAppIcons);

    // Sidebar Mobile Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (sidebar && backdrop) {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    }

    // Modern Dropdown Toggle
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        if (!dropdown) return;
        const isHidden = dropdown.classList.contains('hidden');
        // Close all dropdowns
        document.querySelectorAll('#notif-menu, #user-menu').forEach(el => el.classList.add('hidden'));
        if (isHidden) {
            dropdown.classList.remove('hidden');
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#notif-menu') && !e.target.closest('button[onclick*="notif-menu"]')) {
            const el = document.getElementById('notif-menu');
            if (el) el.classList.add('hidden');
        }
        if (!e.target.closest('#user-menu') && !e.target.closest('button[onclick*="user-menu"]')) {
            const el = document.getElementById('user-menu');
            if (el) el.classList.add('hidden');
        }
    });
</script>
</body>
</html>
