<?php
// persuratan/public/laporan/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('laporan.view')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Laporan & Rekapitulasi Persuratan';
$db = Database::getInstance();

$tipeLaporan = $_GET['tipe'] ?? 'surat_masuk'; // 'surat_masuk', 'surat_keluar', 'disposisi'
$dateFrom = $_GET['date_from'] ?? date('Y-01-01');
$dateTo = $_GET['date_to'] ?? date('Y-12-31');
$statusFilter = $_GET['status'] ?? '';

// Build query based on report type
$rows = [];
if ($tipeLaporan === 'surat_masuk') {
    $rows = SuratMasuk::getAll([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'status' => $statusFilter
    ]);
} elseif ($tipeLaporan === 'surat_keluar') {
    $rows = SuratKeluar::getAll([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'status' => $statusFilter
    ]);
} else {
    // Disposisi report
    $sql = "
        SELECT d.*, sm.nomor_surat, sm.perihal, sm.pengirim, sm.tanggal_surat,
               u.full_name as pemberi_nama
        FROM disposisi d
        JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
        JOIN users u ON d.pemberi_user_id = u.id
        WHERE d.created_at >= :from AND d.created_at <= :to
    ";
    $params = [':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59'];
    if (!empty($statusFilter)) {
        $sql .= " AND d.sifat = :sifat";
        $params[':sifat'] = $statusFilter;
    }
    $sql .= " ORDER BY d.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Laporan & Rekapitulasi Persuratan</h4>
        <p class="text-muted small m-0">Hasilkan rekapitulasi data surat masuk, surat keluar, dan disposisi untuk evaluasi berkala.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('laporan/export.php') ?>?tipe=<?= $tipeLaporan ?>&format=excel&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&status=<?= $statusFilter ?>" class="btn btn-outline-success d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="sheet" style="width: 16px;"></i>
            <span>Ekspor Excel</span>
        </a>
        <a href="<?= url('laporan/export.php') ?>?tipe=<?= $tipeLaporan ?>&format=pdf&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&status=<?= $statusFilter ?>" target="_blank" class="btn btn-outline-danger d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="file-down" style="width: 16px;"></i>
            <span>Cetak / PDF</span>
        </a>
    </div>
</div>

<!-- Filter Selection Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="<?= url('laporan/') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary">Jenis Laporan</label>
                <select name="tipe" class="form-select">
                    <option value="surat_masuk" <?= $tipeLaporan === 'surat_masuk' ? 'selected' : '' ?>>Rekapitulasi Surat Masuk</option>
                    <option value="surat_keluar" <?= $tipeLaporan === 'surat_keluar' ? 'selected' : '' ?>>Rekapitulasi Surat Keluar</option>
                    <option value="disposisi" <?= $tipeLaporan === 'disposisi' ? 'selected' : '' ?>>Rekapitulasi Disposisi</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-secondary">Status / Filter</label>
                <input type="text" name="status" class="form-control" placeholder="Semua..." value="<?= htmlspecialchars($statusFilter) ?>">
            </div>

            <div class="col-6 col-md-1">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <div class="text-muted small">Total Dokumen / Baris</div>
            <div class="fs-4 fw-bold text-primary"><?= count($rows) ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <div class="text-muted small">Periode Laporan</div>
            <div class="fs-6 fw-bold text-dark"><?= date('d/m/Y', strtotime($dateFrom)) ?> s.d <?= date('d/m/Y', strtotime($dateTo)) ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <div class="text-muted small">Status Ekspor</div>
            <div class="fs-6 fw-bold text-success">Excel & PDF Tersedia</div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold m-0">Pratinjau Data Laporan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <?php if ($tipeLaporan === 'surat_masuk'): ?>
                        <tr>
                            <th>No. Agenda</th>
                            <th>Nomor & Tanggal Surat</th>
                            <th>Pengirim & Instansi</th>
                            <th>Perihal</th>
                            <th>Urgensi</th>
                            <th>Status</th>
                        </tr>
                    <?php elseif ($tipeLaporan === 'surat_keluar'): ?>
                        <tr>
                            <th>No. Agenda</th>
                            <th>Nomor & Tanggal Surat</th>
                            <th>Tujuan Surat</th>
                            <th>Perihal</th>
                            <th>Penandatangan</th>
                            <th>Status</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Surat Terkait</th>
                            <th>Pemberi Disposisi</th>
                            <th>Instruksi Disposisi</th>
                            <th>Sifat</th>
                            <th>Tenggat Waktu</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Tidak ada data untuk rentang waktu dan filter yang dipilih.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <?php if ($tipeLaporan === 'surat_masuk'): ?>
                                    <td><span class="badge bg-light text-primary border"><?= htmlspecialchars($r['nomor_agenda']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($r['nomor_surat']) ?></strong><br><small class="text-muted"><?= date('d/m/Y', strtotime($r['tanggal_surat'])) ?></small></td>
                                    <td><?= htmlspecialchars($r['pengirim']) ?><br><small class="text-muted"><?= htmlspecialchars($r['asal_instansi']) ?></small></td>
                                    <td><?= htmlspecialchars($r['perihal']) ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($r['tingkat_urgensi']) ?></span></td>
                                    <td><span class="badge badge-soft-primary"><?= ucfirst($r['status']) ?></span></td>
                                <?php elseif ($tipeLaporan === 'surat_keluar'): ?>
                                    <td><span class="badge bg-light text-primary border"><?= htmlspecialchars($r['nomor_agenda']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($r['nomor_surat']) ?></strong><br><small class="text-muted"><?= date('d/m/Y', strtotime($r['tanggal_surat'])) ?></small></td>
                                    <td><?= htmlspecialchars($r['tujuan']) ?></td>
                                    <td><?= htmlspecialchars($r['perihal']) ?></td>
                                    <td><?= htmlspecialchars($r['pejabat_nama'] ?? '-') ?></td>
                                    <td><span class="badge badge-soft-success"><?= ucfirst($r['status']) ?></span></td>
                                <?php else: ?>
                                    <td><strong><?= htmlspecialchars($r['nomor_surat']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['perihal']) ?></small></td>
                                    <td><?= htmlspecialchars($r['pemberi_nama']) ?></td>
                                    <td><?= htmlspecialchars($r['catatan_instruksi']) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= ucfirst($r['sifat']) ?></span></td>
                                    <td><?= !empty($r['tenggat_waktu']) ? date('d/m/Y', strtotime($r['tenggat_waktu'])) : '-' ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
