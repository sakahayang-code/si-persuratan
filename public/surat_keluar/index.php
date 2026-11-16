<?php
// persuratan/public/surat_keluar/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_keluar.view')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Manajemen Surat Keluar';

$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
    'kerahasiaan' => $_GET['kerahasiaan'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$suratList = SuratKeluar::getAll($filters);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Manajemen Surat Keluar</h4>
        <p class="text-muted small m-0">Kelola draft, penomoran otomatis, alur persetujuan, dan penerbitan surat dinas.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('generator/') ?>" class="btn btn-outline-primary d-flex align-items-center gap-2 bg-white rounded-3">
            <i data-lucide="file-edit" style="width: 16px;"></i>
            <span>Generator Surat (Template)</span>
        </a>
        <?php if (Auth::can('surat_keluar.create')): ?>
            <a href="<?= url('surat_keluar/tambah.php') ?>" class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm">
                <i data-lucide="plus-circle" style="width: 16px;"></i>
                <span>Input Surat Keluar</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="<?= url('surat_keluar/') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-secondary fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i data-lucide="search" style="width:14px;"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="No. surat, tujuan, perihal..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Status Alur</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draf</option>
                    <option value="menunggu_persetujuan" <?= $filters['status'] === 'menunggu_persetujuan' ? 'selected' : '' ?>>Menunggu Persetujuan</option>
                    <option value="disetujui" <?= $filters['status'] === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="diterbitkan" <?= $filters['status'] === 'diterbitkan' ? 'selected' : '' ?>>Diterbitkan</option>
                    <option value="dikirim" <?= $filters['status'] === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Kerahasiaan</label>
                <select name="kerahasiaan" class="form-select">
                    <option value="">Semua</option>
                    <option value="biasa" <?= $filters['kerahasiaan'] === 'biasa' ? 'selected' : '' ?>>Biasa</option>
                    <option value="terbatas" <?= $filters['kerahasiaan'] === 'terbatas' ? 'selected' : '' ?>>Terbatas</option>
                    <option value="rahasia" <?= $filters['kerahasiaan'] === 'rahasia' ? 'selected' : '' ?>>Rahasia</option>
                    <option value="sangat_rahasia" <?= $filters['kerahasiaan'] === 'sangat_rahasia' ? 'selected' : '' ?>>Sangat Rahasia</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Tanggal Sampai</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Filter"><i data-lucide="filter" style="width:16px;"></i></button>
                <a href="<?= url('surat_keluar/') ?>" class="btn btn-light border" title="Reset"><i data-lucide="rotate-ccw" style="width:16px;"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Daftar Surat Keluar (<?= count($suratList) ?> Surat)</h6>
        <div class="text-muted small">Penomoran terintegrasi dengan kode klasifikasi</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="120">No. Agenda</th>
                        <th>Nomor & Tanggal Surat</th>
                        <th>Tujuan Surat</th>
                        <th>Perihal</th>
                        <th>Penandatangan</th>
                        <th>Status</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suratList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i data-lucide="send" style="width:42px; height:42px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Tidak ada data surat keluar yang ditemukan.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suratList as $sk): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($sk['nomor_agenda']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($sk['nomor_surat']) ?></div>
                                    <div class="text-muted small">Tgl: <?= date('d/m/Y', strtotime($sk['tanggal_surat'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($sk['tujuan']) ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 240px;" title="<?= htmlspecialchars($sk['perihal']) ?>">
                                        <?= htmlspecialchars($sk['perihal']) ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border mt-1" style="font-size:10px;">
                                        <?= htmlspecialchars($sk['klasifikasi_nama'] ?? 'Umum') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium"><?= htmlspecialchars($sk['pejabat_nama'] ?? 'Kepala Instansi') ?></div>
                                    <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($sk['pejabat_jabatan'] ?? '') ?></div>
                                </td>
                                <td>
                                    <?php
                                    $stBadge = match($sk['status']) {
                                        'draft' => 'bg-secondary text-white',
                                        'menunggu_persetujuan' => 'badge-soft-warning',
                                        'disetujui' => 'badge-soft-primary',
                                        'diterbitkan' => 'badge-soft-success',
                                        'dikirim' => 'badge-soft-purple',
                                        default => 'bg-light text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $stBadge ?>"><?= ucfirst(str_replace('_', ' ', $sk['status'])) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= url('surat_keluar/preview.php') ?>?id=<?= $sk['id'] ?>" class="btn btn-sm btn-light border p-1" title="Lihat Dokumen / Persetujuan">
                                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <a href="<?= url('surat_keluar/pdf.php') ?>?id=<?= $sk['id'] ?>" target="_blank" class="btn btn-sm btn-light border p-1 text-danger" title="Unduh PDF Resmi">
                                            <i data-lucide="file-down" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <?php if (Auth::can('surat_keluar.approve') && in_array($sk['status'], ['draft', 'menunggu_persetujuan'])): ?>
                                            <a href="<?= url('surat_keluar/action.php') ?>?act=approve&id=<?= $sk['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="btn btn-sm btn-success p-1" title="Setujui Surat" onclick="return confirm('Setujui surat keluar ini?')">
                                                <i data-lucide="check" style="width: 15px; height: 15px;"></i>
                                            </a>
                                        <?php endif; ?>
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
