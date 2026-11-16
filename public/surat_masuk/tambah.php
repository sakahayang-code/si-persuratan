<?php
// persuratan/public/surat_masuk/tambah.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_masuk.create')) {
    Flash::set('danger', 'Akses ditolak.');
    header('Location: /surat_masuk/');
    exit;
}

$pageTitle = 'Registrasi Surat Masuk Baru';
$klasifikasiList = Klasifikasi::getAll();
$autoAgenda = Numbering::generateAgendaNumber('masuk');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!Auth::verifyCsrf($csrf)) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    }

    $nomorSurat = trim($_POST['nomor_surat'] ?? '');
    $pengirim = trim($_POST['pengirim'] ?? '');
    $asalInstansi = trim($_POST['asal_instansi'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $tglSurat = $_POST['tanggal_surat'] ?? '';
    $tglTerima = $_POST['tanggal_terima'] ?? date('Y-m-d');

    if (empty($nomorSurat) || empty($pengirim) || empty($perihal) || empty($tglSurat)) {
        $errors[] = 'Nomor surat, pengirim, perihal, dan tanggal surat wajib diisi.';
    }

    // Handle File Upload safely
    $uploadedFile = null;
    if (!empty($_FILES['file_dokumen']['name']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.';
        } else {
            $targetDir = DOCUMENTS_PATH . '/surat_masuk';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $safeName = 'sm_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath = $targetDir . '/' . $safeName;

            if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $targetPath)) {
                $uploadedFile = 'documents/surat_masuk/' . $safeName;
            } else {
                $errors[] = 'Gagal menyimpan file unggahan.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $id = SuratMasuk::create([
                'nomor_agenda' => $_POST['nomor_agenda'] ?? $autoAgenda,
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => $tglSurat,
                'tanggal_terima' => $tglTerima,
                'pengirim' => $pengirim,
                'asal_instansi' => $asalInstansi,
                'perihal' => $perihal,
                'isi_ringkas' => trim($_POST['isi_ringkas'] ?? ''),
                'kategori' => $_POST['kategori'] ?? 'Umum',
                'klasifikasi_id' => !empty($_POST['klasifikasi_id']) ? (int)$_POST['klasifikasi_id'] : null,
                'tingkat_urgensi' => $_POST['tingkat_urgensi'] ?? 'biasa',
                'file_dokumen' => $uploadedFile,
                'lokasi_fisik' => $_POST['lokasi_fisik'] ?? 'Bantex Surat Masuk ' . date('Y')
            ]);

            Flash::set('success', 'Surat Masuk baru berhasil diregistrasi dengan No. Agenda: ' . ($_POST['nomor_agenda'] ?? $autoAgenda));
            header('Location: /surat_masuk/detail.php?id=' . $id);
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage();
        }
    }
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="/surat_masuk/">Surat Masuk</a></li>
                <li class="breadcrumb-item active">Registrasi Baru</li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Formulir Registrasi Surat Masuk</h4>
    </div>
    <a href="/surat_masuk/" class="btn btn-outline-secondary btn-sm bg-white">Batal & Kembali</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="/surat_masuk/tambah.php" method="POST" enctype="multipart/form-data" id="formSuratMasuk">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Agenda</label>
                    <input type="text" name="nomor_agenda" class="form-control bg-light font-monospace" value="<?= htmlspecialchars($autoAgenda) ?>" readonly>
                    <div class="form-text small">Dibuat otomatis oleh sistem berurutan.</div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Surat Asli <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_surat" class="form-control" placeholder="Contoh: 005/123/DISDIK/XI/2026" required value="<?= htmlspecialchars($_POST['nomor_surat'] ?? '') ?>">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Klasifikasi Surat</label>
                    <select name="klasifikasi_id" class="form-select">
                        <option value="">-- Pilih Klasifikasi --</option>
                        <?php foreach ($klasifikasiList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= (isset($_POST['klasifikasi_id']) && $_POST['klasifikasi_id'] == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Tanggal Tertera pada Surat <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_surat" class="form-control" required value="<?= htmlspecialchars($_POST['tanggal_surat'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Tanggal Diterima Instansi <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terima" class="form-control" required value="<?= htmlspecialchars($_POST['tanggal_terima'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Nama Pengirim / Jabatan <span class="text-danger">*</span></label>
                    <input type="text" name="pengirim" class="form-control" placeholder="Contoh: Kepala Dinas Pendidikan DKI" required value="<?= htmlspecialchars($_POST['pengirim'] ?? '') ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-secondary">Asal Instansi / Organisasi <span class="text-danger">*</span></label>
                    <input type="text" name="asal_instansi" class="form-control" placeholder="Contoh: Dinas Pendidikan Provinsi DKI Jakarta" required value="<?= htmlspecialchars($_POST['asal_instansi'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Perihal Surat <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control" placeholder="Contoh: Undangan Rapat Koordinasi Kurikulum" required value="<?= htmlspecialchars($_POST['perihal'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Ringkasan / Isi Pokok Surat</label>
                    <textarea name="isi_ringkas" class="form-control" rows="3" placeholder="Tuliskan intisari atau pokok informasi dari surat ini..."><?= htmlspecialchars($_POST['isi_ringkas'] ?? '') ?></textarea>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Kategori Surat</label>
                    <select name="kategori" class="form-select">
                        <option value="Dinas">Dinas Resmi</option>
                        <option value="Pemberitahuan">Pemberitahuan</option>
                        <option value="Undangan">Undangan</option>
                        <option value="Permohonan">Permohonan</option>
                        <option value="Laporan">Laporan</option>
                        <option value="Umum">Umum</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Tingkat Urgensi</label>
                    <select name="tingkat_urgensi" class="form-select">
                        <option value="biasa">Biasa</option>
                        <option value="penting">Penting</option>
                        <option value="segera">Segera / Mendesak</option>
                        <option value="rahasia">Rahasia</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Lokasi Simpan Fisik</label>
                    <input type="text" name="lokasi_fisik" class="form-control" placeholder="Contoh: Bantex Rak A-1" value="Bantex Surat Masuk <?= date('Y') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Unggah Scan Dokumen / PDF Surat</label>
                    <input type="file" name="file_dokumen" class="form-control" accept=".pdf, .jpg, .jpeg, .png">
                    <div class="form-text small">Format didukung: PDF, JPG, PNG (Maksimal 10 MB). Disimpan di storage persisten terpisah.</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="/surat_masuk/" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2">
                    <i data-lucide="check" style="width: 18px;"></i>
                    <span>Simpan & Registrasi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
