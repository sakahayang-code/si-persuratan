<?php
// persuratan/public/surat_keluar/tambah.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_keluar.create')) {
    Flash::set('danger', 'Akses ditolak.');
    header('Location: /surat_keluar/');
    exit;
}

$pageTitle = 'Buat Surat Keluar Baru';
$klasifikasiList = Klasifikasi::getAll();
$pejabatList = Pejabat::findActive();
$autoAgenda = Numbering::generateAgendaNumber('keluar');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    }

    $tujuan = trim($_POST['tujuan'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $tglSurat = $_POST['tanggal_surat'] ?? date('Y-m-d');
    $klasifikasiId = !empty($_POST['klasifikasi_id']) ? (int)$_POST['klasifikasi_id'] : null;
    $pejabatId = !empty($_POST['pejabat_id']) ? (int)$_POST['pejabat_id'] : null;
    $status = $_POST['submit_action'] === 'submit_approval' ? 'menunggu_persetujuan' : 'draft';

    if (empty($tujuan) || empty($perihal)) {
        $errors[] = 'Tujuan surat dan perihal wajib diisi.';
    }

    $kodeKlas = '010';
    if ($klasifikasiId) {
        $klasObj = Klasifikasi::findById($klasifikasiId);
        if ($klasObj) $kodeKlas = $klasObj['kode'];
    }

    $nomorSurat = trim($_POST['nomor_surat'] ?? '');
    if (empty($nomorSurat)) {
        $nomorSurat = Numbering::generateNextNumber($kodeKlas);
    }

    if (empty($errors)) {
        try {
            $id = SuratKeluar::create([
                'nomor_agenda' => $_POST['nomor_agenda'] ?? $autoAgenda,
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'perihal' => $perihal,
                'isi_ringkas' => trim($_POST['isi_surat'] ?? ''),
                'klasifikasi_id' => $klasifikasiId,
                'tingkat_kerahasiaan' => $_POST['tingkat_kerahasiaan'] ?? 'biasa',
                'pejabat_id' => $pejabatId,
                'status' => $status
            ]);

            Flash::set('success', "Surat Keluar berhasil dibuat dengan Nomor: {$nomorSurat}");
            header('Location: /surat_keluar/preview.php?id=' . $id);
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan surat: ' . $e->getMessage();
        }
    }
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="/surat_keluar/">Surat Keluar</a></li>
                <li class="breadcrumb-item active">Baru</li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0">Buat Surat Keluar Mandiri</h4>
    </div>
    <a href="/surat_keluar/" class="btn btn-outline-secondary btn-sm bg-white">Batal & Kembali</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="/surat_keluar/tambah.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Agenda Keluar</label>
                    <input type="text" name="nomor_agenda" class="form-control bg-light font-monospace" value="<?= htmlspecialchars($autoAgenda) ?>" readonly>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Klasifikasi Surat <span class="text-danger">*</span></label>
                    <select name="klasifikasi_id" class="form-select" required>
                        <option value="">-- Pilih Klasifikasi --</option>
                        <?php foreach ($klasifikasiList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= (isset($_POST['klasifikasi_id']) && $_POST['klasifikasi_id'] == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Nomor Surat (Otomatis)</label>
                    <input type="text" name="nomor_surat" class="form-control" placeholder="Kosongkan untuk penomoran otomatis instansi" value="<?= htmlspecialchars($_POST['nomor_surat'] ?? '') ?>">
                    <div class="form-text small">Jika dikosongkan, format penomoran instansi otomatis diterapkan.</div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Pejabat Penandatangan <span class="text-danger">*</span></label>
                    <select name="pejabat_id" class="form-select" required>
                        <?php foreach ($pejabatList as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nama'] . ' (' . $p['jabatan'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary">Tingkat Kerahasiaan</label>
                    <select name="tingkat_kerahasiaan" class="form-select">
                        <option value="biasa">Biasa / Terbuka</option>
                        <option value="terbatas">Terbatas</option>
                        <option value="rahasia">Rahasia</option>
                        <option value="sangat_rahasia">Sangat Rahasia</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Tujuan Surat / Penerima <span class="text-danger">*</span></label>
                    <textarea name="tujuan" class="form-control" rows="2" placeholder="Contoh: Kepala Badan Kepegawaian Daerah Provinsi DKI Jakarta&#10;di Jakarta" required><?= htmlspecialchars($_POST['tujuan'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Perihal Surat <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control" placeholder="Contoh: Penyampaian Usulan Penetapan Formasi Pegawai" required value="<?= htmlspecialchars($_POST['perihal'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-secondary">Isi Surat Resmi</label>
                    <textarea name="isi_surat" class="form-control" rows="6" placeholder="Tuliskan isi surat lengkap di sini..."><?= htmlspecialchars($_POST['isi_surat'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="/surat_keluar/" class="btn btn-light border px-4">Batal</a>
                <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary px-3">
                    Simpan sebagai Draf
                </button>
                <button type="submit" name="submit_action" value="submit_approval" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2">
                    <i data-lucide="send" style="width: 16px;"></i>
                    <span>Ajukan Persetujuan Pimpinan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
