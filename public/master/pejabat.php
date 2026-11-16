<?php
// persuratan/public/master/pejabat.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('master.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Master Pejabat & Tanda Tangan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('danger', 'Token CSRF tidak valid.');
    } else {
        $act = $_POST['action'] ?? 'create';
        if ($act === 'create') {
            Pejabat::create($_POST);
            Flash::set('success', 'Pejabat penandatangan berhasil didaftarkan.');
        } elseif ($act === 'toggle_status') {
            $id = (int)$_POST['id'];
            $pejabat = Pejabat::findById($id);
            if ($pejabat) {
                Pejabat::update($id, [
                    'nip' => $pejabat['nip'],
                    'nama' => $pejabat['nama'],
                    'jabatan' => $pejabat['jabatan'],
                    'unit_kerja' => $pejabat['unit_kerja'],
                    'is_active' => $pejabat['is_active'] ? 0 : 1
                ]);
                Flash::set('success', 'Status keaktifan pejabat diperbarui.');
            }
        }
    }
    redirect('master/pejabat.php');
}

$pejabatList = Pejabat::getAll();
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0">Master Pejabat & Penandatangan</h4>
        <p class="text-muted small m-0">Kelola daftar pejabat berwenang penandatangan surat dinas serta aset tanda tangan digital.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPejabat">
        <i data-lucide="user-plus" style="width: 16px;"></i>
        <span>Tambah Pejabat</span>
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Nama Pejabat & NIP</th>
                        <th>Jabatan Dinas</th>
                        <th>Unit Kerja</th>
                        <th>Aset Tanda Tangan & Stempel</th>
                        <th>Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pejabatList as $p): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama']) ?></div>
                                <div class="text-muted font-monospace small">NIP. <?= htmlspecialchars($p['nip']) ?></div>
                            </td>
                            <td>
                                <span class="fw-semibold text-primary"><?= htmlspecialchars($p['jabatan']) ?></span>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= htmlspecialchars($p['unit_kerja'] ?? '-') ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-success border">✓ Signature Active</span>
                                    <span class="badge bg-light text-primary border">✓ Stempel Resmi</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $p['is_active'] ? 'badge-soft-success' : 'badge-soft-danger' ?>">
                                    <?= $p['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="<?= url('master/pejabat.php') ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-light border p-1" title="Aktif/Nonaktifkan">
                                        <i data-lucide="power" style="width: 14px;"></i>
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

<!-- Modal Tambah Pejabat -->
<div class="modal fade" id="modalTambahPejabat" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('master/pejabat.php') ?>" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Pejabat Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Dr. H. Bambang Sudaryanto, M.Si" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">NIP Pejabat <span class="text-danger">*</span></label>
                    <input type="text" name="nip" class="form-control" placeholder="Contoh: 197205121998031003" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Jabatan Resmi <span class="text-danger">*</span></label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Kepala Badan / Sekretaris" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Unit Kerja / Bagian</label>
                    <input type="text" name="unit_kerja" class="form-control" placeholder="Contoh: BPTI & Kearsipan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Pejabat</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
