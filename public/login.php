<?php
// persuratan/public/login.php

require_once __DIR__ . '/../config/app.php';

// Redirect if already logged in
if (Auth::check()) {
    redirect('dashboard.php');
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrf($csrf)) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Silakan masukkan username/email dan password.';
    } else {
        if (Auth::login($username, $password)) {
            Flash::set('success', 'Selamat datang kembali, ' . htmlspecialchars(Auth::user()['full_name']));
            redirect('dashboard.php');
        } else {
            $error = 'Username/email atau password yang Anda masukkan salah.';
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
    <title>Login Masuk - SI-PERSURATAN</title>
    <link rel="stylesheet" href="<?= url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('assets/css/custom.css') ?>">
    <script src="<?= url('assets/js/lucide.min.js') ?>"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: linear-gradient(135deg, #EFF6FF 0%, #F1F5F9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: #0F172A;
            color: #FFFFFF;
            padding: 2rem 2rem 1.75rem 2rem;
            text-align: center;
        }
        .demo-chip {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .demo-chip:hover {
            transform: translateY(-1px);
            background-color: #E2E8F0 !important;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <img src="<?= app_logo_data_uri() ?>" alt="Logo" width="56" height="56" class="mb-2">
        <h3 class="fw-bold m-0" style="letter-spacing: -0.02em;">SI-PERSURATAN</h3>
        <p class="text-secondary small m-0 mt-1">Sistem Informasi Persuratan & Arsip Terintegrasi</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
                <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('login.php') ?>" method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username atau Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i data-lucide="user" style="width:16px;"></i></span>
                    <input type="text" name="username" id="usernameInput" class="form-control border-start-0" placeholder="admin" value="admin" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label small fw-semibold text-secondary">Kata Sandi (Password)</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i data-lucide="lock" style="width:16px;"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0" placeholder="••••••••" value="admin123" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3 d-flex align-items-center justify-content-center gap-2">
                <i data-lucide="log-in" style="width:18px;"></i>
                <span>Masuk ke Sistem</span>
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <div class="text-secondary small fw-semibold mb-2">Akun Demo (Klik untuk Isi Otomatis):</div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('admin', 'admin123')">👑 Super Admin</span>
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('admin_surat', 'admin123')">📑 Admin Surat</span>
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('pimpinan', 'admin123')">👔 Pimpinan</span>
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('sekretaris', 'admin123')">💼 Sekretaris</span>
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('petugas', 'admin123')">📋 Petugas</span>
                <span class="badge bg-light text-dark border demo-chip p-2" onclick="fillLogin('pegawai', 'admin123')">👤 Pegawai</span>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
    function fillLogin(u, p) {
        document.getElementById('usernameInput').value = u;
        document.getElementById('passwordInput').value = p;
    }
</script>
</body>
</html>
