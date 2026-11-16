<?php
// persuratan/app/views/layouts/sidebar.php
if (!defined('APP_INIT')) exit;

$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
function isSidebarActive(string $pattern, string $uri): string {
    return str_contains($uri, $pattern) ? 'active' : '';
}
?>
<aside class="app-sidebar" id="appSidebar">
    <!-- Brand Info -->
    <div class="sidebar-brand">
        <img src="<?= app_logo_data_uri() ?>" alt="Logo" width="36" height="36" class="rounded">
        <div>
            <h1 class="sidebar-brand-title">SI-PERSURATAN</h1>
            <p class="sidebar-brand-subtitle">Digital Archive & Mail</p>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <ul class="sidebar-menu">
        <li class="menu-header">MENU UTAMA</li>
        <li class="menu-item">
            <a href="<?= url('dashboard.php') ?>" class="menu-link <?= isSidebarActive('dashboard', $currentUri) ?>">
                <i data-lucide="layout-dashboard" class="menu-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if (Auth::can('surat_masuk.view')): ?>
            <li class="menu-item">
                <a href="<?= url('surat_masuk/') ?>" class="menu-link <?= isSidebarActive('surat_masuk', $currentUri) ?>">
                    <i data-lucide="inbox" class="menu-icon"></i>
                    <span>Surat Masuk</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('surat_keluar.view')): ?>
            <li class="menu-item">
                <a href="<?= url('surat_keluar/') ?>" class="menu-link <?= isSidebarActive('surat_keluar', $currentUri) ?>">
                    <i data-lucide="send" class="menu-icon"></i>
                    <span>Surat Keluar</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('template.manage') || Auth::can('surat_keluar.create')): ?>
            <li class="menu-item">
                <a href="<?= url('generator/') ?>" class="menu-link <?= isSidebarActive('generator', $currentUri) ?>">
                    <i data-lucide="file-edit" class="menu-icon"></i>
                    <span>Generator Surat</span>
                    <span class="menu-badge bg-primary-subtle text-primary">16 Tpl</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('disposisi.view') || Auth::can('disposisi.create') || Auth::can('disposisi.action')): ?>
            <li class="menu-item">
                <a href="<?= url('disposisi/') ?>" class="menu-link <?= isSidebarActive('disposisi', $currentUri) ?>">
                    <i data-lucide="git-pull-request" class="menu-icon"></i>
                    <span>Disposisi & Tugas</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-header">AGENDA & ARSIP</li>
        <?php if (Auth::can('agenda.view')): ?>
            <li class="menu-item">
                <a href="<?= url('agenda/') ?>" class="menu-link <?= isSidebarActive('agenda', $currentUri) ?>">
                    <i data-lucide="book-open" class="menu-icon"></i>
                    <span>Buku Agenda</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('arsip.manage')): ?>
            <li class="menu-item">
                <a href="<?= url('arsip/') ?>" class="menu-link <?= isSidebarActive('arsip', $currentUri) ?>">
                    <i data-lucide="archive" class="menu-icon"></i>
                    <span>Arsip Digital</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-header">ADMINISTRASI & MASTER</li>
        <?php if (Auth::can('master.manage')): ?>
            <li class="menu-item">
                <a href="<?= url('master/klasifikasi.php') ?>" class="menu-link <?= isSidebarActive('klasifikasi', $currentUri) ?>">
                    <i data-lucide="tag" class="menu-icon"></i>
                    <span>Klasifikasi Surat</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('master/pejabat.php') ?>" class="menu-link <?= isSidebarActive('pejabat', $currentUri) ?>">
                    <i data-lucide="users-round" class="menu-icon"></i>
                    <span>Pejabat & Tanda Tangan</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('master/penomoran.php') ?>" class="menu-link <?= isSidebarActive('penomoran', $currentUri) ?>">
                    <i data-lucide="hash" class="menu-icon"></i>
                    <span>Aturan Penomoran</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('laporan.view')): ?>
            <li class="menu-item">
                <a href="<?= url('laporan/') ?>" class="menu-link <?= isSidebarActive('laporan', $currentUri) ?>">
                    <i data-lucide="bar-chart-3" class="menu-icon"></i>
                    <span>Laporan & Rekap</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-header">PENGATURAN & KEAMANAN</li>
        <?php if (Auth::can('backup.manage')): ?>
            <li class="menu-item">
                <a href="<?= url('backup/') ?>" class="menu-link <?= isSidebarActive('backup', $currentUri) ?>">
                    <i data-lucide="database-backup" class="menu-icon"></i>
                    <span>Backup & Restore</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::can('users.manage')): ?>
            <li class="menu-item">
                <a href="<?= url('users/') ?>" class="menu-link <?= isSidebarActive('users', $currentUri) ?>">
                    <i data-lucide="shield-check" class="menu-icon"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (Auth::hasRole('superadmin')): ?>
            <li class="menu-item">
                <a href="<?= url('audit_log.php') ?>" class="menu-link <?= isSidebarActive('audit_log', $currentUri) ?>">
                    <i data-lucide="history" class="menu-icon"></i>
                    <span>Audit Trail Log</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('pengaturan.php') ?>" class="menu-link <?= isSidebarActive('pengaturan', $currentUri) ?>">
                    <i data-lucide="settings" class="menu-icon"></i>
                    <span>Identitas & Sistem</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Quick Footer in Sidebar -->
    <div class="p-3 border-top border-secondary border-opacity-25 text-center text-muted small" style="font-size: 11px;">
        <span class="text-light fw-semibold">SI-PERSURATAN v1.0</span><br>
        SQLite • Offline PWA • 2026
    </div>
</aside>
