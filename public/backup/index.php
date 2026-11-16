<?php
// persuratan/public/backup/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('backup.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Backup, Restore & Integritas Data';

// Live Integrity and Storage Inspection (Section 8.1 & 12)
$integrity = Database::checkIntegrity();
$dbSize = Database::getDatabaseSize();
$backupList = BackupService::getBackupList();
$migrationStatus = Migration::getStatus();

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Perlindungan Data: Backup & Restore</h4>
        <p class="text-muted small m-0">Menjamin keberlanjutan data persuratan dan dokumen arsip sesuai standar Section 8.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/backup/action.php?act=create&csrf=<?= Auth::csrfToken() ?>" class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm" onclick="return confirm('Mulai proses pencadangan (backup) database dan seluruh dokumen?')">
            <i data-lucide="archive" style="width: 16px;"></i>
            <span>Buat Backup Baru</span>
        </a>
    </div>
</div>

<!-- Storage and Integrity Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">STATUS INTEGRITAS DATABASE</span>
                <i data-lucide="shield-check" class="text-success" style="width: 20px;"></i>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge <?= $integrity['status'] === 'OK' ? 'bg-success' : 'bg-danger' ?> fs-6">
                    <?= $integrity['status'] ?>
                </span>
                <span class="text-secondary small">PRAGMA integrity_check</span>
            </div>
            <div class="text-muted small mt-2">Struktur tabel, indeks B-tree, dan foreign keys SQLite valid.</div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">UKURAN STORAGE PERSISTEN</span>
                <i data-lucide="database" class="text-primary" style="width: 20px;"></i>
            </div>
            <div class="fs-4 fw-bold text-primary mb-1"><?= $dbSize ?></div>
            <div class="text-muted small">Database SQLite WAL + Dokumen terpisah dari program.</div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">MIGRATION BERVERSI</span>
                <i data-lucide="git-branch" class="text-purple" style="width: 20px; color:#7C3AED;"></i>
            </div>
            <div class="fs-4 fw-bold text-dark mb-1"><?= count($migrationStatus) ?> Skema</div>
            <div class="text-muted small">Versi migration berurutan & transaksional aktif.</div>
        </div>
    </div>
</div>

<!-- Restore From Archive Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="upload-cloud" class="text-warning" style="width: 20px;"></i>
            <h6 class="fw-bold m-0">Pemulihan Sistem (Restore dari Arsip ZIP)</h6>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-warning small d-flex align-items-start gap-3 mb-3">
            <i data-lucide="alert-triangle" style="width: 24px; height: 24px;" class="flex-shrink-0"></i>
            <div>
                <strong>Protokol Keamanan Restore:</strong> Sistem akan secara otomatis membuat cadangan darurat (<em>safety backup</em>) dari data saat ini sebelum menjalankan pemulihan. Setelah pemulihan selesai, pemeriksaan integritas database SQLite otomatis dijalankan.
            </div>
        </div>

        <form action="/backup/action.php?act=restore" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan sistem dari file backup ini? Sistem akan membuat safety backup terlebih dahulu.')">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <input type="file" name="backup_file" class="form-control" accept=".zip" required>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-warning w-100 fw-semibold text-dark">
                        Mulai Restore & Uji Integritas
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Backup Log History Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Riwayat Berkas Backup Terdaftar (<?= count($backupList) ?>)</h6>
        <span class="text-muted small">Mencakup Database SQLite & Seluruh Dokumen Lampiran</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Nama File Arsip</th>
                        <th>Tipe Cadangan</th>
                        <th>Ukuran File</th>
                        <th>Checksum SHA-256</th>
                        <th>Waktu Pembuatan</th>
                        <th>Pembuat</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($backupList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i data-lucide="database" style="width:40px; height:40px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Belum ada arsip backup yang dibuat. Silakan klik tombol "Buat Backup Baru".</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($backupList as $b): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark font-monospace small"><?= htmlspecialchars($b['filename']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($b['notes'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?php
                                    $typeBadge = match($b['type']) {
                                        'manual' => 'bg-primary-subtle text-primary',
                                        'pre_restore' => 'bg-warning-subtle text-warning',
                                        'auto_daily' => 'bg-info-subtle text-info',
                                        default => 'bg-light text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $typeBadge ?>"><?= ucfirst(str_replace('_', ' ', $b['type'])) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= round($b['filesize'] / 1024, 2) ?> KB</span>
                                </td>
                                <td>
                                    <span class="font-monospace text-muted" style="font-size: 10px;" title="<?= htmlspecialchars($b['checksum']) ?>">
                                        <?= substr($b['checksum'], 0, 16) ?>...
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark small"><?= date('d/m/Y H:i:s', strtotime($b['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?= htmlspecialchars($b['author_name'] ?? 'System') ?></span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="/backup/action.php?act=download&id=<?= $b['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="btn btn-sm btn-light border p-1 text-primary" title="Unduh Arsip ZIP">
                                            <i data-lucide="download" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <a href="/backup/action.php?act=restore_existing&id=<?= $b['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="btn btn-sm btn-light border p-1 text-warning" title="Pulihkan dari Berkas Ini" onclick="return confirm('Pulihkan database & dokumen dari arsip ini?')">
                                            <i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
