<?php
// persuratan/public/arsip/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('arsip.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Manajemen Arsip Digital';

$filters = [
    'search' => $_GET['search'] ?? '',
    'jenis' => $_GET['jenis'] ?? '',
    'tahun' => $_GET['tahun'] ?? '',
    'status_retensi' => $_GET['status_retensi'] ?? '',
    'is_important' => $_GET['is_important'] ?? ''
];

$arsipList = Arsip::getAll($filters);
$klasifikasiList = Klasifikasi::getAll();

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Arsip Digital Terpadu</h4>
        <p class="text-muted small m-0">Pusat penyimpanan, retensi, dan penelusuran berkas persuratan instansi.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahArsip">
            <i data-lucide="upload-cloud" style="width: 16px;"></i>
            <span>Arsipkan Dokumen Baru</span>
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="<?= url('arsip/') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small text-secondary fw-semibold">Pencarian Metadata</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i data-lucide="search" style="width:14px;"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Kode arsip, judul, no dokumen..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Jenis Dokumen</label>
                <select name="jenis" class="form-select">
                    <option value="">Semua Jenis</option>
                    <option value="surat_masuk" <?= $filters['jenis'] === 'surat_masuk' ? 'selected' : '' ?>>Surat Masuk</option>
                    <option value="surat_keluar" <?= $filters['jenis'] === 'surat_keluar' ? 'selected' : '' ?>>Surat Keluar</option>
                    <option value="sk" <?= $filters['jenis'] === 'sk' ? 'selected' : '' ?>>Keputusan (SK)</option>
                    <option value="dokumen_lain" <?= $filters['jenis'] === 'dokumen_lain' ? 'selected' : '' ?>>Dokumen Lain</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Status Retensi</label>
                <select name="status_retensi" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= $filters['status_retensi'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inaktif" <?= $filters['status_retensi'] === 'inaktif' ? 'selected' : '' ?>>Inaktif</option>
                    <option value="dimusnahkan" <?= $filters['status_retensi'] === 'dimusnahkan' ? 'selected' : '' ?>>Dimusnahkan</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary fw-semibold">Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $filters['tahun'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-6 col-md-1">
                <label class="form-label small text-secondary fw-semibold">Penting</label>
                <select name="is_important" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" <?= $filters['is_important'] === '1' ? 'selected' : '' ?>>★ Bintang</option>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i data-lucide="filter" style="width:16px;"></i></button>
                <a href="<?= url('arsip/') ?>" class="btn btn-light border"><i data-lucide="rotate-ccw" style="width:16px;"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Repositori Arsip Digital (<?= count($arsipList) ?> Berkas)</h6>
        <span class="badge bg-light text-secondary border">Penyimpanan Terpisah dari Kode</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="40" class="text-center">★</th>
                        <th width="140">Kode Arsip</th>
                        <th>Judul & Nomor Dokumen</th>
                        <th>Jenis Dokumen</th>
                        <th>Lokasi Fisik</th>
                        <th>Retensi</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($arsipList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i data-lucide="archive" style="width:40px; height:40px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Tidak ada berkas arsip yang ditemukan.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($arsipList as $ar): ?>
                            <tr>
                                <td class="text-center">
                                    <a href="<?= url('arsip/action.php') ?>?act=toggle_star&id=<?= $ar['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="text-decoration-none <?= $ar['is_important'] ? 'text-warning' : 'text-muted' ?>" title="Tandai Penting">
                                        <?= $ar['is_important'] ? '★' : '☆' ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($ar['kode_arsip']) ?></span>
                                    <div class="text-muted" style="font-size:11px;">Tahun: <?= $ar['tahun'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($ar['judul']) ?></div>
                                    <div class="text-muted small">No: <?= htmlspecialchars($ar['nomor_dokumen']) ?> • Tgl: <?= date('d/m/Y', strtotime($ar['tanggal_dokumen'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= ucfirst(str_replace('_', ' ', $ar['jenis_dokumen'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark small"><i data-lucide="folder" style="width:12px;" class="me-1 text-warning"></i><?= htmlspecialchars($ar['lokasi_fisik'] ?? 'Rak Utama') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $retClass = match($ar['status_retensi']) {
                                        'aktif' => 'badge-soft-success',
                                        'inaktif' => 'badge-soft-warning',
                                        'dimusnahkan' => 'badge-soft-danger',
                                        default => 'badge-soft-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $retClass ?>"><?= ucfirst($ar['status_retensi']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if (!empty($ar['file_path'])): ?>
                                            <a href="<?= url('dokumen_viewer.php') ?>?path=<?= urlencode($ar['file_path']) ?>" target="_blank" class="btn btn-sm btn-light border p-1 text-primary" title="Buka Berkas Digital">
                                                <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                                            </a>
                                        <?php endif; ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border p-1" data-bs-toggle="dropdown" title="Ubah Retensi">
                                                <i data-lucide="more-vertical" style="width: 15px; height: 15px;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small p-1">
                                                <li><a class="dropdown-item py-1" href="<?= url('arsip/action.php') ?>?act=retention&status=aktif&id=<?= $ar['id'] ?>&csrf=<?= Auth::csrfToken() ?>">Jadikan Aktif</a></li>
                                                <li><a class="dropdown-item py-1 text-warning" href="<?= url('arsip/action.php') ?>?act=retention&status=inaktif&id=<?= $ar['id'] ?>&csrf=<?= Auth::csrfToken() ?>">Jadikan Inaktif</a></li>
                                                <li><a class="dropdown-item py-1 text-danger" href="<?= url('arsip/action.php') ?>?act=retention&status=dimusnahkan&id=<?= $ar['id'] ?>&csrf=<?= Auth::csrfToken() ?>" onclick="return confirm('Peringatan: Pemusnahan arsip memerlukan otorisasi. Lanjutkan?')">Musnahkan Arsip</a></li>
                                            </ul>
                                        </div>
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

<!-- Modal Tambah Arsip Baru -->
<div class="modal fade" id="modalTambahArsip" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('arsip/action.php?act=create') ?>" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Arsipkan Dokumen / Surat Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Judul Berkas Arsip <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: SK Pengangkatan Pegawai Kontrak 2026" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary">Nomor Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_dokumen" class="form-control" placeholder="001/SK/2026" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary">Tanggal Dokumen</label>
                        <input type="date" name="tanggal_dokumen" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary">Jenis Dokumen</label>
                        <select name="jenis_dokumen" class="form-select">
                            <option value="sk">Keputusan (SK)</option>
                            <option value="surat_masuk">Surat Masuk</option>
                            <option value="surat_keluar">Surat Keluar</option>
                            <option value="dokumen_lain">Dokumen Pendukung Lain</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary">Klasifikasi</label>
                        <select name="klasifikasi_id" class="form-select">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($klasifikasiList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Lokasi Simpan Fisik</label>
                    <input type="text" name="lokasi_fisik" class="form-control" placeholder="Contoh: Bantex SK 2026 / Lemari 2 Rak B">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Unggah Berkas PDF / Foto</label>
                    <input type="file" name="file_dokumen" class="form-control" accept=".pdf, .jpg, .jpeg, .png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan ke Arsip</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
