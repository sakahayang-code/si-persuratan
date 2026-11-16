<?php
// persuratan/app/views/layouts/mobile_nav.php
if (!defined('APP_INIT')) exit;
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<nav class="mobile-bottom-nav">
    <a href="/dashboard.php" class="mobile-nav-item <?= str_contains($currentUri, 'dashboard') ? 'active' : '' ?>">
        <i data-lucide="layout-dashboard" style="width: 20px; height: 20px;"></i>
        <span>Beranda</span>
    </a>
    <a href="/surat_masuk/" class="mobile-nav-item <?= str_contains($currentUri, 'surat_masuk') ? 'active' : '' ?>">
        <i data-lucide="inbox" style="width: 20px; height: 20px;"></i>
        <span>Masuk</span>
    </a>
    <a href="/generator/" class="mobile-nav-item <?= str_contains($currentUri, 'generator') ? 'active' : '' ?>">
        <i data-lucide="file-plus" style="width: 20px; height: 20px;"></i>
        <span>Buat Surat</span>
    </a>
    <a href="/disposisi/" class="mobile-nav-item <?= str_contains($currentUri, 'disposisi') ? 'active' : '' ?>">
        <i data-lucide="git-pull-request" style="width: 20px; height: 20px;"></i>
        <span>Disposisi</span>
    </a>
    <a href="/profil.php" class="mobile-nav-item <?= str_contains($currentUri, 'profil') ? 'active' : '' ?>">
        <i data-lucide="user" style="width: 20px; height: 20px;"></i>
        <span>Profil</span>
    </a>
</nav>
