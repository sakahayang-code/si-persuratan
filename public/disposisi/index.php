<?php
// persuratan/public/disposisi/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$pageTitle = 'Disposisi & Tindak Lanjut Tugas';
$currentUser = Auth::user();
$db = Database::getInstance();

// Check if user is viewing their own tasks or all dispositions
$isPimpinanOrAdmin = Auth::hasRole(['superadmin', 'admin_persuratan', 'pimpinan', 'sekretaris']);

if ($isPimpinanOrAdmin) {
    // Show all dispositions
    $disposisiList = $db->query("
        SELECT d.*, sm.nomor_surat, sm.perihal, sm.pengirim, sm.asal_instansi,
               u.full_name as pemberi_nama,
               (SELECT COUNT(*) FROM disposisi_detail WHERE disposisi_id = d.id) as total_penerima,
               (SELECT COUNT(*) FROM disposisi_detail WHERE disposisi_id = d.id AND status = 'selesai') as total_selesai
        FROM disposisi d
        JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
        JOIN users u ON d.pemberi_user_id = u.id
        WHERE sm.deleted_at IS NULL
        ORDER BY d.created_at DESC
    ")->fetchAll();
} else {
    // Show dispositions specifically assigned to current user
    $disposisiList = Disposisi::getMyDisposisi($currentUser['id']);
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Disposisi & Tindak Lanjut Tugas</h4>
        <p class="text-muted small m-0">Pantau instruksi pimpinan, batas waktu penyelesaian, dan progres pengerjaan.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (Auth::can('disposisi.create')): ?>
            <a href="/surat_masuk/?status=diterima" class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm">
                <i data-lucide="plus-circle" style="width: 16px;"></i>
                <span>Beri Disposisi Surat</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0"><?= $isPimpinanOrAdmin ? 'Seluruh Lembar Disposisi' : 'Tugas Disposisi Saya' ?> (<?= count($disposisiList) ?>)</h6>
        <span class="badge bg-light text-secondary border">Real-time Tracker</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Surat Masuk Terkait</th>
                        <th>Instruksi / Catatan Pimpinan</th>
                        <th>Sifat & Tenggat</th>
                        <th>Pemberi / Penerima</th>
                        <th>Status</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($disposisiList)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i data-lucide="git-pull-request" style="width:42px; height:42px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Tidak ada data disposisi.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($disposisiList as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nomor_surat']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($row['perihal']) ?>">
                                        <?= htmlspecialchars($row['perihal']) ?>
                                    </div>
                                    <div class="text-secondary" style="font-size: 11px;"><?= htmlspecialchars($row['pengirim'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="p-2 bg-light rounded text-secondary small" style="max-width: 280px; line-height: 1.4;">
                                        <?= nl2br(htmlspecialchars($row['catatan_instruksi'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $sifatBadge = match($row['sifat']) {
                                        'penting' => 'bg-warning text-dark',
                                        'segera' => 'bg-danger text-white',
                                        'rahasia' => 'bg-dark text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                    ?>
                                    <span class="badge <?= $sifatBadge ?>"><?= ucfirst($row['sifat']) ?></span>
                                    <div class="text-muted small mt-1">
                                        Tenggat: <?= !empty($row['tenggat_waktu']) ? '<strong>' . date('d/m/Y', strtotime($row['tenggat_waktu'])) . '</strong>' : '-' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">Dari: <?= htmlspecialchars($row['pemberi_nama']) ?></div>
                                    <?php if ($isPimpinanOrAdmin): ?>
                                        <div class="text-muted small"><?= $row['total_selesai'] ?> dari <?= $row['total_penerima'] ?> staf selesai</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isPimpinanOrAdmin): ?>
                                        <?php if ($row['total_selesai'] == $row['total_penerima'] && $row['total_penerima'] > 0): ?>
                                            <span class="badge badge-soft-success">Selesai Semua</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-warning">Dalam Proses</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php
                                        $userSt = $row['detail_status'] ?? 'belum_dibaca';
                                        $badgeClass = match($userSt) {
                                            'selesai' => 'badge-soft-success',
                                            'dalam_proses' => 'badge-soft-primary',
                                            'dibaca' => 'badge-soft-info',
                                            default => 'badge-soft-warning'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $userSt)) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="/surat_masuk/detail.php?id=<?= $row['surat_masuk_id'] ?>" class="btn btn-sm btn-light border p-1" title="Lihat Surat & Disposisi">
                                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <a href="/disposisi/cetak.php?id=<?= $row['id'] ?? $row['disposisi_id'] ?>" target="_blank" class="btn btn-sm btn-light border p-1 text-primary" title="Cetak Lembar Disposisi">
                                            <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
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
