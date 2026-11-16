<?php
// persuratan/app/views/layouts/mobile_nav.php
if (!defined('APP_INIT')) exit;
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!-- Mobile Bottom Navigation (Floating on Mobile Screens) -->
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 flex justify-around items-center h-16 lg:hidden px-2 shadow-lg">
    <a href="<?= url('dashboard.php') ?>" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-decoration-none transition <?= str_contains($currentUri, 'dashboard') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-blue-600' ?>">
        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
        <span>Beranda</span>
    </a>
    <a href="<?= url('surat_masuk/') ?>" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-decoration-none transition <?= str_contains($currentUri, 'surat_masuk') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-blue-600' ?>">
        <i data-lucide="inbox" class="w-5 h-5"></i>
        <span>Masuk</span>
    </a>
    <a href="<?= url('generator/') ?>" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-decoration-none transition <?= str_contains($currentUri, 'generator') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-blue-600' ?>">
        <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center -mt-4 shadow-md shadow-blue-500/30">
            <i data-lucide="plus" class="w-5 h-5"></i>
        </div>
        <span>Buat</span>
    </a>
    <a href="<?= url('disposisi/') ?>" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-decoration-none transition <?= str_contains($currentUri, 'disposisi') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-blue-600' ?>">
        <i data-lucide="git-branch" class="w-5 h-5"></i>
        <span>Disposisi</span>
    </a>
    <button onclick="toggleSidebar()" class="flex flex-col items-center justify-center gap-1 text-[10px] font-medium text-slate-500 hover:text-blue-600 transition">
        <i data-lucide="menu" class="w-5 h-5"></i>
        <span>Menu</span>
    </button>
</nav>
