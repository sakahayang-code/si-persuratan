<?php
// persuratan/public/audit_log.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check() || !Auth::hasRole('superadmin')) {
    Flash::set('danger', 'Hanya Super Administrator yang berwenang melihat Audit Trail Log.');
    redirect('dashboard.php');
}

$pageTitle = 'Audit Trail Log Aktivitas Sistem';
$db = Database::getInstance();

$moduleFilter = $_GET['module'] ?? '';
$actionFilter = $_GET['action'] ?? '';

$sql = "
    SELECT a.*, u.full_name, u.username, r.name as role_name
    FROM aktivitas_log a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE 1=1
";
$params = [];

if (!empty($moduleFilter)) {
    $sql .= " AND a.module = :module";
    $params[':module'] = $moduleFilter;
}
if (!empty($actionFilter)) {
    $sql .= " AND a.action = :action";
    $params[':action'] = $actionFilter;
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Audit Trail Log Aktivitas Sistem</h4>
        <p class="text-muted small m-0">Catatan kronologis tidak dapat diubah (immutable log) dari seluruh tindakan pengguna.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="<?= url('audit_log.php') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small text-secondary fw-semibold">Filter Modul</label>
                <select name="module" class="form-select">
                    <option value="">Semua Modul</option>
                    <option value="auth" <?= $moduleFilter === 'auth' ? 'selected' : '' ?>>Autentikasi (Login/Logout)</option>
                    <option value="surat_masuk" <?= $moduleFilter === 'surat_masuk' ? 'selected' : '' ?>>Surat Masuk</option>
                    <option value="surat_keluar" <?= $moduleFilter === 'surat_keluar' ? 'selected' : '' ?>>Surat Keluar</option>
                    <option value="disposisi" <?= $moduleFilter === 'disposisi' ? 'selected' : '' ?>>Disposisi</option>
                    <option value="arsip" <?= $moduleFilter === 'arsip' ? 'selected' : '' ?>>Arsip Digital</option>
                    <option value="backup" <?= $moduleFilter === 'backup' ? 'selected' : '' ?>>Backup & Restore</option>
                    <option value="settings" <?= $moduleFilter === 'settings' ? 'selected' : '' ?>>Pengaturan Sistem</option>
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label small text-secondary fw-semibold">Filter Aksi</label>
                <select name="action" class="form-select">
                    <option value="">Semua Aksi</option>
                    <option value="CREATE" <?= $actionFilter === 'CREATE' ? 'selected' : '' ?>>CREATE (Pembuatan)</option>
                    <option value="UPDATE" <?= $actionFilter === 'UPDATE' ? 'selected' : '' ?>>UPDATE (Perubahan)</option>
                    <option value="DELETE" <?= $actionFilter === 'DELETE' ? 'selected' : '' ?>>DELETE (Penghapusan)</option>
                    <option value="APPROVE" <?= $actionFilter === 'APPROVE' ? 'selected' : '' ?>>APPROVE (Persetujuan)</option>
                    <option value="BACKUP" <?= $actionFilter === 'BACKUP' ? 'selected' : '' ?>>BACKUP</option>
                    <option value="RESTORE" <?= $actionFilter === 'RESTORE' ? 'selected' : '' ?>>RESTORE</option>
                    <option value="LOGIN" <?= $actionFilter === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter Log</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold m-0">Daftar Audit Log Terkini (<?= count($logs) ?> Rekaman)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="160">Waktu (WIB)</th>
                        <th>Pengguna & Peran</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Keterangan Aktivitas</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada log aktivitas yang cocok.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="text-muted font-monospace small"><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($l['full_name'] ?? 'System') ?></div>
                                    <span class="badge bg-light text-secondary border" style="font-size:10px;"><?= htmlspecialchars($l['role_name'] ?? 'Guest') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $actBadge = match($l['action']) {
                                        'CREATE' => 'badge-soft-success',
                                        'UPDATE' => 'badge-soft-warning',
                                        'DELETE' => 'badge-soft-danger',
                                        'APPROVE' => 'badge-soft-purple',
                                        'LOGIN' => 'badge-soft-primary',
                                        'BACKUP', 'RESTORE' => 'badge-soft-info',
                                        default => 'badge-soft-primary'
                                    };
                                    ?>
                                    <span class="badge <?= $actBadge ?>"><?= htmlspecialchars($l['action']) ?></span>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($l['module']) ?></span></td>
                                <td class="small text-secondary"><?= htmlspecialchars($l['details'] ?? '-') ?></td>
                                <td class="font-monospace text-muted small"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
