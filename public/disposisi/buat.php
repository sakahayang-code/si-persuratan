<?php
// persuratan/public/disposisi/buat.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('disposisi.create')) {
    Flash::set('danger', 'Hanya Pimpinan atau pejabat yang berwenang memberikan lembar disposisi.');
    redirect('disposisi/');
}

$pageTitle = 'Buat Lembar Disposisi';
$suratId = (int)($_GET['surat_id'] ?? 0);
$surat = $suratId ? SuratMasuk::findById($suratId) : null;

$users = User::getAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    }

    $targetSuratId = (int)($_POST['surat_masuk_id'] ?? $suratId);
    $instruksi = trim($_POST['catatan_instruksi'] ?? '');
    $penerimaList = $_POST['penerima_user_ids'] ?? [];

    if ($targetSuratId <= 0) {
        $errors[] = 'Silakan pilih surat masuk yang akan didisposisikan.';
    }
    if (empty($instruksi)) {
        $errors[] = 'Catatan instruksi pimpinan wajib diisi.';
    }
    if (empty($penerimaList)) {
        $errors[] = 'Pilih minimal satu pejabat/pegawai penerima disposisi.';
    }

    if (empty($errors)) {
        try {
            $dispId = Disposisi::create([
                'surat_masuk_id' => $targetSuratId,
                'sifat' => $_POST['sifat'] ?? 'biasa',
                'tenggat_waktu' => !empty($_POST['tenggat_waktu']) ? $_POST['tenggat_waktu'] : null,
                'catatan_instruksi' => $instruksi
            ], $penerimaList);

            Flash::set('success', 'Lembar disposisi berhasil diterbitkan dan notifikasi telah dikirim.');
            redirect('surat_masuk/detail.php?id=' . $targetSuratId);
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan disposisi: ' . $e->getMessage();
        }
    }
}

// Available pending surat masuk if no suratId passed
$pendingSuratMasuk = SuratMasuk::getAll(['status' => 'diterima']);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('disposisi/') ?>">Disposisi</a></li>
                <li class="breadcrumb-item active">Lembar Disposisi Baru</li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0">Terbitkan Lembar Disposisi Pimpinan</h4>
    </div>
    <a href="<?= url('disposisi/') ?>" class="btn btn-outline-secondary btn-sm bg-white">Kembali</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= url('disposisi/buat.php' . ($suratId ? '?surat_id=' . $suratId : '')) ?>" method="POST" id="formDisposisi">
                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

                    <!-- Surat Reference -->
                    <?php if ($surat): ?>
                        <input type="hidden" name="surat_masuk_id" value="<?= $surat['id'] ?>">
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-primary font-monospace"><?= htmlspecialchars($surat['nomor_agenda']) ?></span>
                                <span class="text-muted small">Tgl: <?= date('d/m/Y', strtotime($surat['tanggal_surat'])) ?></span>
                            </div>
                            <h5 class="fw-bold text-dark mt-2 mb-1"><?= htmlspecialchars($surat['perihal']) ?></h5>
                            <div class="text-muted small">No: <?= htmlspecialchars($surat['nomor_surat']) ?> • Pengirim: <?= htmlspecialchars($surat['pengirim']) ?></div>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Pilih Surat Masuk yang Didisposisikan <span class="text-danger">*</span></label>
                            <select name="surat_masuk_id" class="form-select" required>
                                <option value="">-- Pilih Surat Masuk --</option>
                                <?php foreach ($pendingSuratMasuk as $sm): ?>
                                    <option value="<?= $sm['id'] ?>">
                                        <?= htmlspecialchars($sm['nomor_agenda'] . ' - ' . $sm['nomor_surat'] . ' (' . $sm['perihal'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Sifat Disposisi</label>
                            <select name="sifat" class="form-select">
                                <option value="biasa">Biasa</option>
                                <option value="penting" selected>Penting</option>
                                <option value="segera">Segera / Mendesak</option>
                                <option value="rahasia">Rahasia</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Batas Waktu / Tenggat (Deadline)</label>
                            <input type="date" name="tenggat_waktu" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                        </div>
                    </div>

                    <!-- Multi-Recipient Selection -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary mb-2">Diteruskan Kepada (Pilih Pejabat / Staf Pelaksana) <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <?php foreach ($users as $u): ?>
                                <?php if ($u['id'] !== Auth::id()): ?>
                                    <div class="col-12 col-sm-6">
                                        <div class="p-2 border rounded bg-white d-flex align-items-center gap-2">
                                            <input type="checkbox" name="penerima_user_ids[]" value="<?= $u['id'] ?>" id="user_<?= $u['id'] ?>" class="form-check-input">
                                            <label for="user_<?= $u['id'] ?>" class="form-check-label small" style="cursor: pointer;">
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($u['full_name']) ?></div>
                                                <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($u['role_name'] . ($u['jabatan'] ? ' • ' . $u['jabatan'] : '')) ?></div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Instructions and Preset Chips -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-secondary m-0">Instruksi / Catatan Pimpinan <span class="text-danger">*</span></label>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" onclick="addChip('Mohon ditindaklanjuti segera.')">+ Tindak Lanjut</button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" onclick="addChip('Pelajari dan siapkan telaah staf.')">+ Telaah Staf</button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" onclick="addChip('Hadir mewakili pimpinan dan laporkan hasilnya.')">+ Hadiri & Wakilkan</button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" onclick="addChip('Siapkan draft surat balasan.')">+ Draft Balasan</button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" onclick="addChip('Simpan untuk diketahui dan arsipkan.')">+ Arsipkan</button>
                        </div>
                        <textarea name="catatan_instruksi" id="instruksiInput" class="form-control" rows="4" placeholder="Tuliskan petunjuk atau instruksi khusus dari pimpinan..." required></textarea>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('disposisi/') ?>" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                            <i data-lucide="check" style="width: 18px;"></i>
                            <span>Terbitkan Disposisi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold m-0">Ketentuan Disposisi Instansi</h6>
            </div>
            <div class="card-body small text-secondary">
                <p>1. Lembar disposisi memiliki kekuatan dinas untuk menginstruksikan tindak lanjut kepada staf terkait.</p>
                <p>2. Seluruh penerima yang dipilih akan menerima notifikasi sistem secara instan.</p>
                <p>3. Status penyelesaian dipantau otomatis dan tercatat dalam buku agenda persuratan.</p>
            </div>
        </div>
    </div>
</div>

<script>
function addChip(text) {
    const el = document.getElementById('instruksiInput');
    if (el.value.trim() === '') {
        el.value = text;
    } else {
        el.value += ' ' + text;
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
