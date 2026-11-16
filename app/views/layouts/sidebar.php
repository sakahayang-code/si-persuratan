<?php
// persuratan/app/views/layouts/sidebar.php
if (!defined('APP_INIT')) exit;

$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
function isSidebarActive(string $pattern, string $uri): bool {
    return str_contains($uri, $pattern);
}

// Fetch real database counts safely
$db = Database::getInstance();
$badgeSm = (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE deleted_at IS NULL")->fetchColumn();
$badgeSk = (int)$db->query("SELECT COUNT(*) FROM surat_keluar WHERE deleted_at IS NULL")->fetchColumn();
$badgeDisp = (int)$db->query("SELECT COUNT(*) FROM disposisi")->fetchColumn();

$currentUser = Auth::user();
$currentRoleSlug = $currentUser['role_slug'] ?? 'superadmin';
?>
<!-- SIDEBAR NAVIGATION -->
<aside id="sidebar" class="fixed lg:sticky top-0 left-0 z-50 h-screen w-72 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0 shrink-0">
  <!-- Brand Header -->
  <div class="p-5 border-b border-slate-100 flex items-center justify-between">
    <a href="<?= url('dashboard.php') ?>" class="flex items-center gap-3 group text-decoration-none">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-blue-600/20 group-hover:scale-105 transition-transform">
        <i data-lucide="mail-check" class="w-5 h-5"></i>
      </div>
      <div>
        <h1 class="font-display font-bold text-slate-900 text-lg leading-tight tracking-tight">SI-PERSURATAN</h1>
        <p class="text-[11px] font-medium text-slate-500">Arsip & Persuratan Digital</p>
      </div>
    </a>
    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-slate-400 hover:text-slate-600 rounded-lg">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Role Switcher (RBAC) -->
  <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-100">
    <div class="flex items-center justify-between mb-1">
      <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Peran Aktif (RBAC)</label>
      <span class="text-[10px] text-blue-600 font-semibold"><?= htmlspecialchars($currentUser['role_name'] ?? 'User') ?></span>
    </div>
    <div class="relative">
      <select onchange="window.location.href='<?= url('switch_role.php?role=') ?>' + this.value" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer">
        <option value="superadmin" <?= $currentRoleSlug === 'superadmin' ? 'selected' : '' ?>>👑 Super Admin (Akses Penuh)</option>
        <option value="pimpinan" <?= $currentRoleSlug === 'pimpinan' ? 'selected' : '' ?>>👔 Pimpinan (Disposisi & Acc)</option>
        <option value="admin_persuratan" <?= $currentRoleSlug === 'admin_persuratan' ? 'selected' : '' ?>>📑 Admin Persuratan</option>
        <option value="sekretaris" <?= $currentRoleSlug === 'sekretaris' ? 'selected' : '' ?>>💼 Sekretaris</option>
        <option value="petugas" <?= $currentRoleSlug === 'petugas' ? 'selected' : '' ?>>📋 Petugas Registrasi</option>
        <option value="pegawai" <?= $currentRoleSlug === 'pegawai' ? 'selected' : '' ?>>👤 Pegawai (Tindak Lanjut)</option>
      </select>
    </div>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Menu Utama</p>

    <!-- Dashboard -->
    <a href="<?= url('dashboard.php') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('dashboard', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
      <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
      <span>Dashboard</span>
    </a>

    <!-- Surat Masuk -->
    <?php if (Auth::can('surat_masuk.view')): ?>
      <a href="<?= url('surat_masuk/') ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('surat_masuk', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="inbox" class="w-4 h-4 shrink-0"></i>
          <span>Surat Masuk</span>
        </div>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700"><?= $badgeSm ?></span>
      </a>
    <?php endif; ?>

    <!-- Disposisi & Tindak Lanjut -->
    <?php if (Auth::can('disposisi.view') || Auth::can('disposisi.create') || Auth::can('disposisi.action')): ?>
      <a href="<?= url('disposisi/') ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('disposisi', $currentUri) ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="git-branch" class="w-4 h-4 shrink-0"></i>
          <span>Disposisi & Tugas</span>
        </div>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800"><?= $badgeDisp ?></span>
      </a>
    <?php endif; ?>

    <!-- Surat Keluar -->
    <?php if (Auth::can('surat_keluar.view')): ?>
      <a href="<?= url('surat_keluar/') ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('surat_keluar', $currentUri) ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
          <span>Surat Keluar</span>
        </div>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700"><?= $badgeSk ?></span>
      </a>
    <?php endif; ?>

    <!-- Generator Surat -->
    <?php if (Auth::can('template.manage') || Auth::can('surat_keluar.create')): ?>
      <a href="<?= url('generator/') ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('generator', $currentUri) ? 'bg-teal-50 text-teal-800 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="file-pen-line" class="w-4 h-4 shrink-0 text-teal-600"></i>
          <span>Generator Surat</span>
        </div>
        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-800">16 Tpl</span>
      </a>
    <?php endif; ?>

    <p class="px-3 pt-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Arsip & Pelaporan</p>

    <!-- Agenda -->
    <?php if (Auth::can('agenda.view')): ?>
      <a href="<?= url('agenda/') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('agenda', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="book-open" class="w-4 h-4 shrink-0"></i>
        <span>Buku Agenda</span>
      </a>
    <?php endif; ?>

    <!-- Arsip Digital -->
    <?php if (Auth::can('arsip.manage')): ?>
      <a href="<?= url('arsip/') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('arsip', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="archive" class="w-4 h-4 shrink-0"></i>
        <span>Arsip Digital</span>
      </a>
    <?php endif; ?>

    <!-- Laporan -->
    <?php if (Auth::can('laporan.view')): ?>
      <a href="<?= url('laporan/') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('laporan', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
        <span>Laporan & Rekapitulasi</span>
      </a>
    <?php endif; ?>

    <p class="px-3 pt-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Administrasi Sistem</p>

    <!-- Master Klasifikasi -->
    <?php if (Auth::can('master.manage')): ?>
      <a href="<?= url('master/klasifikasi.php') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('klasifikasi', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="tag" class="w-4 h-4 shrink-0"></i>
        <span>Klasifikasi Surat</span>
      </a>
      <a href="<?= url('master/pejabat.php') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('pejabat', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="users-round" class="w-4 h-4 shrink-0"></i>
        <span>Pejabat & TTE</span>
      </a>
    <?php endif; ?>

    <!-- User Management -->
    <?php if (Auth::can('users.manage')): ?>
      <a href="<?= url('users/') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('users', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="user-cog" class="w-4 h-4 shrink-0"></i>
        <span>Manajemen Pengguna</span>
      </a>
    <?php endif; ?>

    <!-- Settings & Backup -->
    <?php if (Auth::can('system.backup') || Auth::can('master.manage')): ?>
      <a href="<?= url('pengaturan.php') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('pengaturan', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
        <span>Pengaturan & Backup</span>
      </a>
    <?php endif; ?>

    <!-- Audit Log -->
    <?php if (Auth::can('audit.view')): ?>
      <a href="<?= url('audit_log.php') ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isSidebarActive('audit_log', $currentUri) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
        <i data-lucide="history" class="w-4 h-4 shrink-0"></i>
        <span>Audit Trail Aktivitas</span>
      </a>
    <?php endif; ?>
  </nav>

  <!-- Storage Integrity & PWA Footer -->
  <div class="p-4 border-t border-slate-100 bg-slate-50/70 space-y-2">
    <div class="flex items-center justify-between text-xs">
      <span class="text-slate-500 flex items-center gap-1.5">
        <i data-lucide="database" class="w-3.5 h-3.5 text-blue-600"></i>
        SQLite Storage
      </span>
      <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">WAL Aktif</span>
    </div>
    <a href="<?= url('logout.php') ?>" class="w-full py-2 px-3 bg-white hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl flex items-center justify-center gap-2 transition text-decoration-none">
      <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
      <span>Keluar Sistem</span>
    </a>
  </div>
</aside>
