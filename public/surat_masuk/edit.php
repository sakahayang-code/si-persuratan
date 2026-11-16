<?php
// persuratan/public/surat_masuk/edit.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_masuk.edit')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('surat_masuk/');
}

$id = (int)($_GET['id'] ?? 0);
$surat = SuratMasuk::findById($id);

if (!$surat) {
    Flash::set('danger', 'Surat tidak ditemukan.');
    redirect('surat_masuk/');
}

$pageTitle = 'Edit Surat Masuk - ' . $surat['nomor_agenda'];
$klasifikasiList = Klasifikasi::getAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token tidak valid.';
    }

    $nomorSurat = trim($_POST['nomor_surat'] ?? '');
    $pengirim = trim($_POST['pengirim'] ?? '');
    $asalInstansi = trim($_POST['asal_instansi'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');

    if (empty($nomorSurat) || empty($pengirim) || empty($perihal)) {
        $errors[] = 'Nomor surat, pengirim, dan perihal wajib diisi.';
    }

    if (empty($errors)) {
        $updateData = [
            'nomor_surat' => $nomorSurat,
            'tanggal_surat' => $_POST['tanggal_surat'],
            'tanggal_terima' => $_POST['tanggal_terima'],
            'pengirim' => $pengirim,
            'asal_instansi' => $asalInstansi,
            'perihal' => $perihal,
            'isi_ringkas' => trim($_POST['isi_ringkas'] ?? ''),
            'kategori' => $_POST['kategori'] ?? 'Umum',
            'klasifikasi_id' => !empty($_POST['klasifikasi_id']) ? (int)$_POST['klasifikasi_id'] : null,
            'tingkat_urgensi' => $_POST['tingkat_urgensi'] ?? 'biasa',
            'status' => $_POST['status'] ?? $surat['status']
        ];

        // If new file uploaded
        if (!empty($_FILES['file_dokumen']['name']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $safeName = 'sm_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = DOCUMENTS_PATH . '/surat_masuk/' . $safeName;
                if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $targetPath)) {
                    $updateData['file_dokumen'] = 'documents/surat_masuk/' . $safeName;
                }
            }
        }

        SuratMasuk::update($id, $updateData);
        Flash::set('success', 'Data surat masuk berhasil diperbarui.');
        redirect('surat_masuk/detail.php?id=' . $id);
    }
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('surat_masuk/') ?>">Surat Masuk</a></li>
                <li class="breadcrumb-item"><a href="<?= url('surat_masuk/detail.php') ?>?id=<?= $id ?>"><?= htmlspecialchars($surat['nomor_agenda']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0">Edit Data Surat Masuk</h4>
    </div>
    <a href="<?= url('surat_masuk/detail.php') ?>?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm bg-white">Batal</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="<?= url('surat_masuk/edit.php?id=' . $id) ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Agenda</label>
                    <input type="text" class="form-control bg-light font-monospace" value="<?= htmlspecialchars($surat['nomor_agenda']) ?>" readonly>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Surat Asli <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_surat" class="form-control" required value="<?= htmlspecialchars($surat['nomor_surat']) ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Klasifikasi</label>
                    <select name="klasifikasi_id" class="form-select">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($klasifikasiList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $surat['klasifikasi_id'] == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control" required value="<?= $surat['tanggal_surat'] ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Tanggal Diterima</label>
                    <input type="date" name="tanggal_terima" class="form-control" required value="<?= $surat['tanggal_terima'] ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Pengirim</label>
                    <input type="text" name="pengirim" class="form-control" required value="<?= htmlspecialchars($surat['pengirim']) ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Asal Instansi</label>
                    <input type="text" name="asal_instansi" class="form-control" required value="<?= htmlspecialchars($surat['asal_instansi']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Perihal</label>
                    <input type="text" name="perihal" class="form-control" required value="<?= htmlspecialchars($surat['perihal']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Ringkasan Isi</label>
                    <textarea name="isi_ringkas" class="form-control" rows="3"><?= htmlspecialchars($surat['isi_ringkas'] ?? '') ?></textarea>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Status</label>
                    <select name="status" class="form-select">
                        <option value="diterima" <?= $surat['status'] === 'diterima' ? 'selected' : '' ?>>Diterima</option>
                        <option value="disposisi" <?= $surat['status'] === 'disposisi' ? 'selected' : '' ?>>Disposisi</option>
                        <option value="tindak_lanjut" <?= $surat['status'] === 'tindak_lanjut' ? 'selected' : '' ?>>Tindak Lanjut</option>
                        <option value="selesai" <?= $surat['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Urgensi</label>
                    <select name="tingkat_urgensi" class="form-select">
                        <option value="biasa" <?= $surat['tingkat_urgensi'] === 'biasa' ? 'selected' : '' ?>>Biasa</option>
                        <option value="penting" <?= $surat['tingkat_urgensi'] === 'penting' ? 'selected' : '' ?>>Penting</option>
                        <option value="segera" <?= $surat['tingkat_urgensi'] === 'segera' ? 'selected' : '' ?>>Segera</option>
                        <option value="rahasia" <?= $surat['tingkat_urgensi'] === 'rahasia' ? 'selected' : '' ?>>Rahasia</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Ganti Dokumen File</label>
                    <input type="file" name="file_dokumen" class="form-control" accept=".pdf, .jpg, .jpeg, .png">
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="<?= url('surat_masuk/detail.php') ?>?id=<?= $id ?>" class="btn btn-light border">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
