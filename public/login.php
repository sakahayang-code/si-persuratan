<?php
// persuratan/public/login.php

require_once __DIR__ . '/../config/app.php';

// Redirect if already logged in
if (Auth::check()) {
    redirect('dashboard.php');
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrf($csrf)) {
        $error = 'Sesi form telah kedaluwarsa. Silakan muat ulang halaman.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Silakan masukkan username/email dan kata sandi.';
    } else {
        if (Auth::login($username, $password)) {
            Flash::set('success', 'Selamat datang kembali, ' . htmlspecialchars(Auth::user()['full_name']));
            redirect('dashboard.php');
        } else {
            $error = 'Username atau kata sandi yang Anda masukkan salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563EB">
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <link rel="icon" type="image/png" href="<?= url('assets/img/favicon.png') ?>">
    <title>Masuk ke SI-PERSURATAN | Sistem Informasi Persuratan Digital</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

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
                        surface: '#F1F5F9',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #F1F5F9;
            color: #0F172A;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 antialiased selection:bg-blue-100 selection:text-blue-900">

    <div class="w-full max-w-md">
        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden">
            <!-- Header Section -->
            <div class="pt-8 pb-6 px-8 text-center bg-gradient-to-b from-slate-50 to-white border-b border-slate-100">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-blue-600/25 mx-auto mb-3.5">
                    <i data-lucide="mail-check" class="w-7 h-7"></i>
                </div>
                <h1 class="font-display font-extrabold text-2xl text-slate-900 tracking-tight">SI-PERSURATAN</h1>
                <p class="text-xs font-medium text-slate-500 mt-1">Sistem Informasi Persuratan & Arsip Digital Terpadu</p>
                <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold border border-blue-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <span>Database: SQLite Persistent WAL</span>
                </div>
            </div>

            <!-- Body Section -->
            <div class="p-6 sm:p-8 space-y-5">
                <?php if (!empty($error)): ?>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= url('login.php') ?>" method="POST" class="space-y-4" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

                    <!-- Username Field -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username atau Email</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="username" id="usernameInput" value="admin" required autofocus
                                placeholder="Masukkan username..."
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition" />
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-700">Kata Sandi</label>
                            <span class="text-[11px] text-slate-400">Default: admin123</span>
                        </div>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" name="password" id="passwordInput" value="admin123" required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 shadow-md shadow-blue-600/25 transition">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Sistem</span>
                    </button>
                </form>

                <!-- Quick Demo Role Switcher (Auto Fill & Submit) -->
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Akun Demo Instan (Klik untuk Masuk):</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        <button type="button" onclick="fillAndSubmit('admin', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>👑</span> <span>Super Admin</span>
                        </button>
                        <button type="button" onclick="fillAndSubmit('pimpinan', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>👔</span> <span>Pimpinan</span>
                        </button>
                        <button type="button" onclick="fillAndSubmit('admin_surat', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>📑</span> <span>Admin Surat</span>
                        </button>
                        <button type="button" onclick="fillAndSubmit('sekretaris', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>💼</span> <span>Sekretaris</span>
                        </button>
                        <button type="button" onclick="fillAndSubmit('petugas', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>📋</span> <span>Petugas Surat</span>
                        </button>
                        <button type="button" onclick="fillAndSubmit('pegawai', 'admin123')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 text-left transition flex items-center gap-1.5">
                            <span>👤</span> <span>Pegawai / Staf</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-center text-[11px] text-slate-400">
                &copy; <?= date('Y') ?> SI-PERSURATAN • Versi Produksi cPanel
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        function fillAndSubmit(u, p) {
            document.getElementById('usernameInput').value = u;
            document.getElementById('passwordInput').value = p;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
