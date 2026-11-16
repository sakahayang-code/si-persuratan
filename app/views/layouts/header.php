<?php
// persuratan/app/views/layouts/header.php
if (!defined('APP_INIT')) exit;

$currentUser = Auth::user();
$unreadNotifications = $currentUser ? Notifikasi::getUnread($currentUser['id']) : [];
$flashMessages = Flash::get();
$pageTitle = $pageTitle ?? 'Dashboard Persuratan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563EB">
    <meta name="description" content="SI-PERSURATAN - Sistem Informasi Persuratan Digital dan Arsip Terintegrasi">
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <link rel="icon" type="image/png" href="<?= url('assets/img/favicon.png') ?>">
    <title><?= htmlspecialchars($pageTitle) ?> - SI-PERSURATAN</title>
    
    <!-- Local CSS (Works 100% Offline) -->
    <link rel="stylesheet" href="<?= url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/custom.css') ?>">
    
    <!-- Local JS Libraries -->
    <script src="<?= url('assets/js/chart.umd.min.js') ?>"></script>
    <script src="<?= url('assets/js/lucide.min.js') ?>"></script>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar Navigation -->
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="app-main flex-grow-1">
        <!-- Top Header Bar -->
        <header class="app-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none p-2" id="sidebarToggle" aria-label="Buka Menu">
                    <i data-lucide="menu"></i>
                </button>
                <div class="header-search d-none d-md-block">
                    <form action="<?= url('pencarian.php') ?>" method="GET">
                        <i data-lucide="search" class="header-search-icon"></i>
                        <input type="text" name="q" placeholder="Cari nomor surat, perihal, atau arsip..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    </form>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Network & Sync Status -->
                <div class="d-flex align-items-center bg-light px-3 py-1 rounded-pill small border">
                    <span id="networkStatusIndicator" class="status-indicator bg-success"></span>
                    <span id="networkStatusText" class="fw-medium text-secondary">Online</span>
                    <button id="btnManualSync" class="btn btn-sm btn-link p-0 ms-2 text-decoration-none text-primary" title="Sinkronkan Antrean Offline">
                        <span id="offlineQueueBadge" class="badge bg-warning text-dark d-none">0</span>
                    </button>
                </div>

                <!-- PWA Install Button -->
                <button id="btnInstallPwa" class="btn btn-sm btn-outline-primary d-none align-items-center gap-1 rounded-pill px-3">
                    <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                    <span>Pasang Aplikasi</span>
                </button>

                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light position-relative p-2 rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
                        <?php if (count($unreadNotifications) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                <?= count($unreadNotifications) ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="width: 320px;">
                        <li class="d-flex justify-content-between align-items-center px-2 py-1 mb-2 border-bottom">
                            <span class="fw-bold small">Notifikasi Masuk</span>
                            <span class="badge bg-primary rounded-pill"><?= count($unreadNotifications) ?> Baru</span>
                        </li>
                        <?php if (empty($unreadNotifications)): ?>
                            <li class="text-center text-muted py-3 small">Tidak ada notifikasi baru</li>
                        <?php else: ?>
                            <?php foreach ($unreadNotifications as $notif): ?>
                                <li>
                                    <a class="dropdown-item p-2 rounded small" href="<?= htmlspecialchars($notif['link_url'] ?? '#') ?>">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($notif['judul']) ?></div>
                                        <div class="text-muted text-truncate"><?= htmlspecialchars($notif['pesan']) ?></div>
                                        <div class="text-xs text-secondary mt-1" style="font-size: 11px;"><?= date('d M, H:i', strtotime($notif['created_at'])) ?></div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- User Profile & Quick Role Switcher -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 p-1 pe-2 rounded-pill border" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                            <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-sm-block" style="line-height: 1.1;">
                            <div class="fw-semibold small text-truncate" style="max-width: 120px;"><?= htmlspecialchars($currentUser['full_name'] ?? 'Guest') ?></div>
                            <span class="badge bg-primary-subtle text-primary" style="font-size: 10px;"><?= htmlspecialchars($currentUser['role_name'] ?? 'User') ?></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px;">
                        <li class="px-2 py-1 border-bottom mb-2">
                            <div class="fw-bold small"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></div>
                            <div class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                            <div class="text-muted" style="font-size: 11px;">NIP: <?= htmlspecialchars($currentUser['nip'] ?? '-') ?></div>
                        </li>
                        <li><a class="dropdown-item py-1 small rounded" href="<?= url('profil.php') ?>"><i data-lucide="user" class="me-2" style="width:14px; height:14px;"></i> Profil Pengguna</a></li>
                        <li><a class="dropdown-item py-1 small rounded" href="<?= url('check.php') ?>"><i data-lucide="activity" class="me-2" style="width:14px; height:14px;"></i> Diagnosa Server (check.php)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-2 py-1 text-muted" style="font-size: 10px; font-weight: 600;">GANTI PERAN CEPAT (DEMO):</li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=superadmin') ?>">👑 Super Admin</a></li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=admin_persuratan') ?>">📑 Admin Persuratan</a></li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=pimpinan') ?>">👔 Pimpinan (Bambang)</a></li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=sekretaris') ?>">💼 Sekretaris (Dewi)</a></li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=petugas') ?>">📋 Petugas Registrasi</a></li>
                        <li><a class="dropdown-item py-1 small" href="<?= url('switch_role.php?role=pegawai') ?>">👤 Pegawai / Staf</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-1 small text-danger rounded" href="<?= url('logout.php') ?>"><i data-lucide="log-out" class="me-2" style="width:14px; height:14px;"></i> Keluar Sistem</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Offline Sync Banner Alert -->
        <div id="offlineSyncBanner" class="alert alert-warning m-3 mb-0 d-flex justify-content-between align-items-center d-none shadow-sm border-warning">
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="cloud-off"></i>
                <span class="queue-text fw-medium">Antrean surat tersimpan di memori perangkat offline.</span>
            </div>
            <button onclick="OfflineManager.syncAll()" class="btn btn-sm btn-warning fw-semibold px-3">
                Sinkronkan Sekarang
            </button>
        </div>

        <!-- Flash Notification Messages -->
        <?php if (!empty($flashMessages)): ?>
            <div class="px-4 pt-3">
                <?php foreach ($flashMessages as $msg): ?>
                    <div class="alert alert-<?= htmlspecialchars($msg['type']) ?> alert-dismissible fade show shadow-sm mb-2" role="alert">
                        <?= htmlspecialchars($msg['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Page Body Content Starts Here -->
        <main class="p-3 p-md-4">
