<?php
// persuratan/public/master/klasifikasi.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('master.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Master Klasifikasi Surat';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('danger', 'Token CSRF tidak valid.');
    } else {
        $act = $_POST['action'] ?? 'create';
        if ($act === 'create') {
            Klasifikasi::create($_POST);
            Flash::set('success', 'Klasifikasi surat baru berhasil ditambahkan.');
        } elseif ($act === 'edit') {
            Klasifikasi::update((int)$_POST['id'], $_POST);
            Flash::set('success', 'Klasifikasi surat berhasil diperbarui.');
        } elseif ($act === 'delete') {
            Klasifikasi::delete((int)$_POST['id']);
            Flash::set('info', 'Klasifikasi surat telah dihapus.');
        }
    }
    redirect('master/klasifikasi.php');
}

$klasList = Klasifikasi::getAll();
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0">Master Klasifikasi Surat</h4>
        <p class="text-muted small m-0">Pengaturan kode klasifikasi kearsipan nasional dan masa retensi berkas.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKlasifikasi">
        <i data-lucide="plus-circle" style="width: 16px;"></i>
        <span>Tambah Klasifikasi</span>
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="100">Kode</th>
                        <th>Kategori / Nama Klasifikasi</th>
                        <th>Uraian Lingkup</th>
                        <th>Retensi Aktif / Inaktif</th>
                        <th>Keterangan</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($klasList as $k): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary font-monospace fs-6"><?= htmlspecialchars($k['kode']) ?></span>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($k['nama']) ?></td>
                            <td class="small text-secondary"><?= htmlspecialchars($k['uraian'] ?? '-') ?></td>
                            <td class="small">
                                <span class="badge bg-light text-success border"><?= $k['retensi_aktif'] ?> Thn Aktif</span>
                                <span class="badge bg-light text-warning border"><?= $k['retensi_inaktif'] ?> Thn Inaktif</span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($k['keterangan'] ?? 'Fasilitatif') ?></span></td>
                            <td class="text-center">
                                <form action="<?= url('master/klasifikasi.php') ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus klasifikasi ini?')">
                                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-light text-danger border p-1" title="Hapus">
                                        <i data-lucide="trash-2" style="width: 14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Klasifikasi -->
<div class="modal fade" id="modalTambahKlasifikasi" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('master/klasifikasi.php') ?>" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Klasifikasi Surat Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label small fw-bold">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: 005" required>
                    </div>
                    <div class="col-8">
                        <label class="form-label small fw-bold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Undangan Resmi" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Uraian / Deskripsi</label>
                    <textarea name="uraian" class="form-control" rows="2" placeholder="Uraian isi cakupan klasifikasi..."></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Retensi Aktif (Tahun)</label>
                        <input type="number" name="retensi_aktif" class="form-control" value="2">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Retensi Inaktif (Tahun)</label>
                        <input type="number" name="retensi_inaktif" class="form-control" value="5">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Keterangan / Kategori Berkas</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Arsip Fasilitatif">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Klasifikasi</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
