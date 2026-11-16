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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563EB">
    <meta name="description" content="SI-PERSURATAN - Sistem Informasi Persuratan Digital dan Arsip Terpadu">
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <link rel="icon" type="image/png" href="<?= url('assets/img/favicon.png') ?>">
    <title><?= htmlspecialchars($pageTitle) ?> - SI-PERSURATAN</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- QRCode.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: '#2563EB',
                        secondary: '#7C3AED',
                        tealAccent: '#0D9488',
                        amberAccent: '#F59E0B',
                        orangeAccent: '#F97316',
                        surface: '#F1F5F9',
                    }
                }
            }
        }
    </script>

    <!-- Modern Design & Bootstrap Compatibility Bridge Stylesheet -->
    <style>
        body {
            background-color: #F1F5F9;
            color: #0F172A;
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Modern Styling Bridge for Existing PHP Views */
        .card {
            background-color: #FFFFFF;
            border-radius: 1rem !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #FFFFFF !important;
            border-bottom: 1px solid #F1F5F9 !important;
            padding: 1rem 1.25rem !important;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #0F172A;
        }
        .card-body {
            padding: 1.25rem !important;
        }
        .card-footer {
            background-color: #F8FAFC !important;
            border-top: 1px solid #F1F5F9 !important;
            padding: 0.875rem 1.25rem !important;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.15s ease-in-out !important;
            text-decoration: none !important;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-sm {
            padding: 0.35rem 0.75rem !important;
            font-size: 0.75rem !important;
            border-radius: 0.5rem !important;
        }
        .btn-primary {
            background-color: #2563EB !important;
            border-color: #2563EB !important;
            color: #FFFFFF !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2) !important;
        }
        .btn-primary:hover {
            background-color: #1D4ED8 !important;
            border-color: #1D4ED8 !important;
        }
        .btn-secondary {
            background-color: #64748B !important;
            border-color: #64748B !important;
            color: #FFFFFF !important;
        }
        .btn-success {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #FFFFFF !important;
        }
        .btn-danger {
            background-color: #E11D48 !important;
            border-color: #E11D48 !important;
            color: #FFFFFF !important;
        }
        .btn-warning {
            background-color: #D97706 !important;
            border-color: #D97706 !important;
            color: #FFFFFF !important;
        }
        .btn-info {
            background-color: #0284C7 !important;
            border-color: #0284C7 !important;
            color: #FFFFFF !important;
        }
        .btn-light, .btn-outline-secondary {
            background-color: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            color: #334155 !important;
        }
        .btn-light:hover, .btn-outline-secondary:hover {
            background-color: #F1F5F9 !important;
            color: #0F172A !important;
        }

        .form-control, .form-select {
            display: block;
            width: 100%;
            border-radius: 0.75rem !important;
            border: 1px solid #CBD5E1 !important;
            background-color: #F8FAFC !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            color: #0F172A !important;
            transition: all 0.15s ease-in-out;
        }
        .form-control:focus, .form-select:focus {
            background-color: #FFFFFF !important;
            border-color: #2563EB !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .table thead th {
            background-color: #F8FAFC !important;
            color: #64748B !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-bottom: 1px solid #E2E8F0 !important;
            padding: 0.75rem 1rem !important;
        }
        .table tbody td {
            padding: 0.875rem 1rem !important;
            border-bottom: 1px solid #F1F5F9 !important;
            vertical-align: middle !important;
            color: #1E293B;
        }
        .table tbody tr:hover td {
            background-color: rgba(248, 250, 252, 0.8) !important;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border-radius: 9999px !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.25rem 0.625rem !important;
        }
        .bg-primary, .badge.bg-primary { background-color: #DBEAFE !important; color: #1D4ED8 !important; border: 1px solid #BFDBFE; }
        .bg-success, .badge.bg-success { background-color: #D1FAE5 !important; color: #047857 !important; border: 1px solid #A7F3D0; }
        .bg-warning, .badge.bg-warning { background-color: #FEF3C7 !important; color: #B45309 !important; border: 1px solid #FDE68A; }
        .bg-danger, .badge.bg-danger { background-color: #FFE4E6 !important; color: #BE123C !important; border: 1px solid #FECDD3; }
        .bg-info, .badge.bg-info { background-color: #E0F2FE !important; color: #0369A1 !important; border: 1px solid #BAE6FD; }
        .bg-secondary, .badge.bg-secondary { background-color: #F1F5F9 !important; color: #475569 !important; border: 1px solid #E2E8F0; }

        .alert {
            border-radius: 1rem !important;
            padding: 1rem !important;
            margin-bottom: 1rem !important;
            font-size: 0.875rem !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success { background-color: #ECFDF5 !important; color: #065F46 !important; border: 1px solid #A7F3D0 !important; }
        .alert-danger { background-color: #FFF1F2 !important; color: #9F1239 !important; border: 1px solid #FECDD3 !important; }
        .alert-warning { background-color: #FFFBEB !important; color: #92400E !important; border: 1px solid #FDE68A !important; }
        .alert-info { background-color: #F0F9FF !important; color: #075985 !important; border: 1px solid #BAE6FD !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #F1F5F9; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
    </style>
</head>
<body class="antialiased selection:bg-blue-100 selection:text-blue-900">

<!-- Main App Wrapper -->
<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 pb-20 lg:pb-0">

        <!-- Top Header Bar -->
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 flex-1">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 text-slate-600 hover:bg-slate-100 rounded-xl" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Global Search Input -->
                <div class="relative max-w-md w-full">
                    <form action="<?= url('pencarian.php') ?>" method="GET" class="relative m-0">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Cari nomor surat, perihal, atau arsip..." class="w-full pl-10 pr-4 py-2 bg-slate-100/80 hover:bg-slate-100 focus:bg-white border border-transparent focus:border-blue-500 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500/15" />
                    </form>
                </div>
            </div>

            <!-- Right Actions & Profile -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Network Online Pill -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="hidden sm:inline">Online</span>
                </div>

                <!-- Notifications Dropdown -->
                <div class="relative">
                    <button onclick="toggleDropdown('notif-menu')" class="p-2 text-slate-600 hover:bg-slate-100 rounded-xl relative transition">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <?php if (count($unreadNotifications) > 0): ?>
                            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-orange-500 rounded-full ring-2 ring-white"></span>
                        <?php endif; ?>
                    </button>

                    <div id="notif-menu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-display font-bold text-xs text-slate-800">Notifikasi Masuk</span>
                            <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full"><?= count($unreadNotifications) ?> Baru</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-50 text-xs">
                            <?php if (empty($unreadNotifications)): ?>
                                <div class="p-4 text-center text-slate-400">Tidak ada notifikasi baru</div>
                            <?php else: ?>
                                <?php foreach ($unreadNotifications as $n): ?>
                                    <a href="<?= htmlspecialchars($n['link_url'] ?? '#') ?>" class="block p-3 hover:bg-slate-50 text-decoration-none">
                                        <p class="font-semibold text-slate-800 m-0"><?= htmlspecialchars($n['judul']) ?></p>
                                        <p class="text-slate-500 text-[11px] m-0 mt-0.5 truncate"><?= htmlspecialchars($n['pesan']) ?></p>
                                        <p class="text-[10px] text-slate-400 m-0 mt-1"><?= date('d M, H:i', strtotime($n['created_at'])) ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- User Profile & Switcher Dropdown -->
                <div class="relative">
                    <button onclick="toggleDropdown('user-menu')" class="flex items-center gap-2.5 pl-2 border-l border-slate-200 hover:opacity-90 transition">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-xs font-bold text-slate-800 leading-none"><?= htmlspecialchars($currentUser['full_name'] ?? 'User') ?></p>
                            <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($currentUser['role_name'] ?? 'Role') ?></p>
                        </div>
                    </button>

                    <div id="user-menu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900 m-0"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></p>
                            <p class="text-[11px] text-slate-500 m-0"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            <span class="inline-block mt-1 text-[10px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full"><?= htmlspecialchars($currentUser['role_name'] ?? '') ?></span>
                        </div>
                        <div class="py-1 text-xs">
                            <a href="<?= url('profil.php') ?>" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 text-decoration-none">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i> Profil Saya
                            </a>
                            <a href="<?= url('check.php') ?>" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 text-decoration-none">
                                <i data-lucide="activity" class="w-4 h-4 text-slate-400"></i> Diagnosa Server (check.php)
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <p class="px-4 py-1 text-[10px] font-bold uppercase text-slate-400 m-0">Ganti Peran Instan (Demo):</p>
                            <a href="<?= url('switch_role.php?role=superadmin') ?>" class="flex items-center gap-2 px-4 py-1.5 text-slate-700 hover:bg-slate-50 text-decoration-none">👑 Super Admin</a>
                            <a href="<?= url('switch_role.php?role=pimpinan') ?>" class="flex items-center gap-2 px-4 py-1.5 text-slate-700 hover:bg-slate-50 text-decoration-none">👔 Pimpinan (Bambang)</a>
                            <a href="<?= url('switch_role.php?role=admin_persuratan') ?>" class="flex items-center gap-2 px-4 py-1.5 text-slate-700 hover:bg-slate-50 text-decoration-none">📑 Admin Persuratan</a>
                            <a href="<?= url('switch_role.php?role=sekretaris') ?>" class="flex items-center gap-2 px-4 py-1.5 text-slate-700 hover:bg-slate-50 text-decoration-none">💼 Sekretaris (Dewi)</a>
                            <a href="<?= url('switch_role.php?role=pegawai') ?>" class="flex items-center gap-2 px-4 py-1.5 text-slate-700 hover:bg-slate-50 text-decoration-none">👤 Pegawai (Andi)</a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="<?= url('logout.php') ?>" class="flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 text-decoration-none font-semibold">
                                <i data-lucide="log-out" class="w-4 h-4 text-rose-600"></i> Keluar Sistem
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages Banner -->
        <?php if (!empty($flashMessages)): ?>
            <div class="px-4 lg:px-8 pt-4 space-y-2">
                <?php foreach ($flashMessages as $msg): ?>
                    <div class="alert alert-<?= htmlspecialchars($msg['type']) ?> flex items-center justify-between m-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                            <span><?= htmlspecialchars($msg['message']) ?></span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Dynamic Main Content -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
