<?php
// persuratan/public/surat_masuk/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

if (!Auth::can('surat_masuk.view')) {
    Flash::set('danger', 'Anda tidak memiliki hak akses untuk melihat surat masuk.');
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Manajemen Surat Masuk';

$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
    'urgensi' => $_GET['urgensi'] ?? '',
    'kategori' => $_GET['kategori'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$suratList = SuratMasuk::getAll($filters);
$klasifikasiList = Klasifikasi::getAll();

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Manajemen Surat Masuk</h4>
        <p class="text-muted small m-0">Catat, kelola disposisi, dan pantau penyelesaian surat masuk instansi.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/agenda/?jenis=masuk" class="btn btn-outline-secondary d-flex align-items-center gap-2 bg-white rounded-3">
            <i data-lucide="book-open" style="width: 16px;"></i>
            <span>Buku Agenda</span>
        </a>
        <?php if (Auth::can('surat_masuk.create')): ?>
            <a href="/surat_masuk/tambah.php" class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm">
                <i data-lucide="plus-circle" style="width: 16px;"></i>
                <span>Catat Surat Masuk</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="/surat_masuk/" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-secondary fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i data-lucide="search" style="width:14px;"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="No. surat, pengirim, perihal..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="diterima" <?= $filters['status'] === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                    <option value="disposisi" <?= $filters['status'] === 'disposisi' ? 'selected' : '' ?>>Disposisi</option>
                    <option value="tindak_lanjut" <?= $filters['status'] === 'tindak_lanjut' ? 'selected' : '' ?>>Tindak Lanjut</option>
                    <option value="selesai" <?= $filters['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Urgensi</label>
                <select name="urgensi" class="form-select">
                    <option value="">Semua Urgensi</option>
                    <option value="biasa" <?= $filters['urgensi'] === 'biasa' ? 'selected' : '' ?>>Biasa</option>
                    <option value="penting" <?= $filters['urgensi'] === 'penting' ? 'selected' : '' ?>>Penting</option>
                    <option value="segera" <?= $filters['urgensi'] === 'segera' ? 'selected' : '' ?>>Segera</option>
                    <option value="rahasia" <?= $filters['urgensi'] === 'rahasia' ? 'selected' : '' ?>>Rahasia</option>
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
                <a href="/surat_masuk/" class="btn btn-light border" title="Reset"><i data-lucide="rotate-ccw" style="width:16px;"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Daftar Surat Masuk (<?= count($suratList) ?> Surat)</h6>
        <div class="text-muted small">Diurutkan berdasarkan tanggal penerimaan terbaru</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="120">No. Agenda</th>
                        <th>Nomor & Tanggal Surat</th>
                        <th>Pengirim & Instansi</th>
                        <th>Perihal Surat</th>
                        <th>Urgensi</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suratList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i data-lucide="inbox" style="width:42px; height:42px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Tidak ada data surat masuk yang sesuai filter.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suratList as $sm): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($sm['nomor_agenda']) ?></span>
                                    <div class="text-muted" style="font-size:11px;">Terima: <?= date('d/m/Y', strtotime($sm['tanggal_terima'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($sm['nomor_surat']) ?></div>
                                    <div class="text-muted small">Tgl: <?= date('d/m/Y', strtotime($sm['tanggal_surat'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($sm['pengirim']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($sm['asal_instansi']) ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($sm['perihal']) ?>">
                                        <?= htmlspecialchars($sm['perihal']) ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border mt-1" style="font-size: 10px;">
                                        <?= htmlspecialchars($sm['klasifikasi_nama'] ?? 'Umum') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $urgBadge = match($sm['tingkat_urgensi']) {
                                        'penting' => 'bg-warning text-dark',
                                        'segera' => 'bg-danger text-white',
                                        'rahasia' => 'bg-dark text-white',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $urgBadge ?>"><?= ucfirst($sm['tingkat_urgensi']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $stBadge = match($sm['status']) {
                                        'diterima' => 'badge-soft-info',
                                        'disposisi' => 'badge-soft-warning',
                                        'tindak_lanjut' => 'badge-soft-purple',
                                        'selesai' => 'badge-soft-success',
                                        default => 'badge-soft-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $stBadge ?>"><?= ucfirst(str_replace('_', ' ', $sm['status'])) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="/surat_masuk/detail.php?id=<?= $sm['id'] ?>" class="btn btn-sm btn-light border p-1" title="Detail & Disposisi">
                                            <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <a href="/surat_masuk/tanda_terima.php?id=<?= $sm['id'] ?>" target="_blank" class="btn btn-sm btn-light border p-1 text-primary" title="Cetak Tanda Terima">
                                            <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                                        </a>
                                        <?php if (Auth::can('disposisi.create') && $sm['status'] === 'diterima'): ?>
                                            <a href="/disposisi/buat.php?surat_id=<?= $sm['id'] ?>" class="btn btn-sm btn-warning text-dark p-1" title="Lembar Disposisi">
                                                <i data-lucide="git-pull-request" style="width: 15px; height: 15px;"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (Auth::can('surat_masuk.edit')): ?>
                                            <a href="/surat_masuk/edit.php?id=<?= $sm['id'] ?>" class="btn btn-sm btn-light border p-1" title="Ubah Data">
                                                <i data-lucide="pencil" style="width: 15px; height: 15px;"></i>
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
