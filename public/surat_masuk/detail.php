<?php
// persuratan/public/surat_masuk/detail.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_masuk.view')) {
    Flash::set('danger', 'Akses ditolak.');
    header('Location: /surat_masuk/');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$surat = SuratMasuk::findById($id);

if (!$surat) {
    Flash::set('danger', 'Data surat masuk tidak ditemukan.');
    header('Location: /surat_masuk/');
    exit;
}

$pageTitle = 'Detail Surat Masuk - ' . $surat['nomor_surat'];
$disposisiList = Disposisi::getBySuratMasukId($id);
$tindakLanjutList = TindakLanjut::getBySuratMasukId($id);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="/surat_masuk/">Surat Masuk</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($surat['nomor_agenda']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Detail Surat Masuk</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/surat_masuk/tanda_terima.php?id=<?= $surat['id'] ?>" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-2 bg-white rounded-3">
            <i data-lucide="printer" style="width: 16px;"></i>
            <span>Cetak Tanda Terima</span>
        </a>
        <?php if (Auth::can('disposisi.create')): ?>
            <a href="/disposisi/buat.php?surat_id=<?= $surat['id'] ?>" class="btn btn-warning text-dark d-flex align-items-center gap-2 rounded-3 fw-semibold">
                <i data-lucide="git-pull-request" style="width: 16px;"></i>
                <span>Buat Disposisi</span>
            </a>
        <?php endif; ?>
        <?php if (Auth::can('surat_masuk.edit')): ?>
            <a href="/surat_masuk/edit.php?id=<?= $surat['id'] ?>" class="btn btn-light border d-flex align-items-center gap-2 rounded-3">
                <i data-lucide="pencil" style="width: 16px;"></i>
                <span>Edit</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Letter Information -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary font-monospace"><?= htmlspecialchars($surat['nomor_agenda']) ?></span>
                    <span class="text-muted small">Diterima: <?= date('d F Y', strtotime($surat['tanggal_terima'])) ?></span>
                </div>
                <div>
                    <?php
                    $stBadge = match($surat['status']) {
                        'diterima' => 'badge-soft-info',
                        'disposisi' => 'badge-soft-warning',
                        'tindak_lanjut' => 'badge-soft-purple',
                        'selesai' => 'badge-soft-success',
                        default => 'badge-soft-secondary'
                    };
                    ?>
                    <span class="badge <?= $stBadge ?> fs-6"><?= ucfirst(str_replace('_', ' ', $surat['status'])) ?></span>
                </div>
            </div>
            <div class="card-body p-4">
                <h4 class="fw-bold text-dark mb-3"><?= htmlspecialchars($surat['perihal']) ?></h4>

                <div class="row g-3 py-3 border-top border-bottom mb-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Nomor Surat Asli</div>
                        <div class="fw-bold fs-6 text-primary"><?= htmlspecialchars($surat['nomor_surat']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Tanggal Surat</div>
                        <div class="fw-semibold text-dark"><?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Pengirim</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($surat['pengirim']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($surat['asal_instansi']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Klasifikasi & Kategori</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($surat['klasifikasi_kode'] . ' - ' . ($surat['klasifikasi_nama'] ?? 'Umum')) ?></div>
                        <span class="badge bg-light text-secondary border mt-1">Kategori: <?= htmlspecialchars($surat['kategori']) ?></span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary small text-uppercase mb-2">Ringkasan / Isi Pokok Surat:</h6>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="line-height: 1.6;">
                        <?= !empty($surat['isi_ringkas']) ? nl2br(htmlspecialchars($surat['isi_ringkas'])) : '<em class="text-muted">Tidak ada ringkasan teks.</em>' ?>
                    </div>
                </div>

                <?php if (!empty($surat['file_dokumen'])): ?>
                    <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-light">
                        <div class="d-flex align-items-center gap-3">
                            <i data-lucide="file-text" class="text-danger" style="width: 32px; height: 32px;"></i>
                            <div>
                                <div class="fw-semibold text-dark">Berkas Scan Surat Masuk</div>
                                <div class="text-muted small"><?= htmlspecialchars(basename($surat['file_dokumen'])) ?></div>
                            </div>
                        </div>
                        <a href="/dokumen_viewer.php?path=<?= urlencode($surat['file_dokumen']) ?>" target="_blank" class="btn btn-sm btn-primary">
                            <i data-lucide="external-link" style="width: 14px;"></i> Buka Dokumen
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Disposisi History Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="git-pull-request" class="text-warning" style="width: 20px;"></i>
                    <h6 class="fw-bold m-0">Instruksi Lembar Disposisi (<?= count($disposisiList) ?>)</h6>
                </div>
                <?php if (Auth::can('disposisi.create')): ?>
                    <a href="/disposisi/buat.php?surat_id=<?= $surat['id'] ?>" class="btn btn-sm btn-warning text-dark">+ Disposisi Baru</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-4">
                <?php if (empty($disposisiList)): ?>
                    <div class="text-center text-muted py-4">
                        <p class="mb-0">Surat ini belum memiliki lembar disposisi dari pimpinan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($disposisiList as $disp): ?>
                        <div class="p-3 border rounded-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning text-dark">Sifat: <?= ucfirst($disp['sifat']) ?></span>
                                    <span class="text-muted small ms-2">Dari: <strong><?= htmlspecialchars($disp['pemberi_nama']) ?></strong></span>
                                </div>
                                <div class="text-muted small">
                                    Tenggat: <?= !empty($disp['tenggat_waktu']) ? '<strong>' . date('d/m/Y', strtotime($disp['tenggat_waktu'])) . '</strong>' : '-' ?>
                                </div>
                            </div>
                            <div class="fw-bold text-dark mb-2">Instruksi:</div>
                            <div class="p-2 bg-white border rounded mb-3 text-secondary">
                                <?= nl2br(htmlspecialchars($disp['catatan_instruksi'])) ?>
                            </div>

                            <div class="fw-semibold small text-secondary mb-1">Penerima Disposisi & Status:</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($disp['details'] as $det): ?>
                                    <div class="badge bg-white text-dark border p-2 d-flex align-items-center gap-2">
                                        <i data-lucide="user" style="width:12px;"></i>
                                        <span><?= htmlspecialchars($det['penerima_nama']) ?></span>
                                        <span class="badge <?= $det['status'] === 'selesai' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:9px;">
                                            <?= ucfirst(str_replace('_', ' ', $det['status'])) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Follow-up Log (Tindak Lanjut) Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="check-square" class="text-success" style="width: 20px;"></i>
                    <h6 class="fw-bold m-0">Riwayat Tindak Lanjut & Penyelesaian</h6>
                </div>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalTindakLanjut">+ Catat Tindak Lanjut</button>
            </div>
            <div class="card-body p-4">
                <?php if (empty($tindakLanjutList)): ?>
                    <div class="text-center text-muted py-3">
                        <p class="mb-0">Belum ada catatan tindak lanjut untuk surat ini.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tindakLanjutList as $tl): ?>
                        <div class="border-start border-success border-3 ps-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($tl['user_nama']) ?></span>
                                <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($tl['created_at'])) ?></span>
                            </div>
                            <div class="text-primary fw-medium small mb-1"><?= htmlspecialchars($tl['tindakan']) ?></div>
                            <p class="text-secondary small mb-0"><?= nl2br(htmlspecialchars($tl['catatan'] ?? '')) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Sidebar Metadata -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold m-0">Informasi Registrasi</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Petugas Penerima:</span>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($surat['penerima_nama'] ?? 'Admin') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tingkat Urgensi:</span>
                        <span class="fw-semibold"><?= ucfirst($surat['tingkat_urgensi']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Waktu Catat:</span>
                        <span class="text-dark"><?= date('d/m/Y H:i', strtotime($surat['created_at'])) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status Dokumen:</span>
                        <span class="badge badge-soft-success">Tersimpan di SQLite</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Tindak Lanjut -->
<div class="modal fade" id="modalTindakLanjut" tabindex="-1">
    <div class="modal-dialog">
        <form action="/disposisi/tindak_lanjut.php" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="surat_masuk_id" value="<?= $surat['id'] ?>">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Catat Tindak Lanjut Surat</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Tindakan yang Dilakukan <span class="text-danger">*</span></label>
                    <input type="text" name="tindakan" class="form-control" placeholder="Contoh: Menghadiri rapat koordinasi / Menyusun laporan balasan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Catatan Hasil / Keterangan</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Jelaskan hasil pelaksanaan tugas..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Status Penyelesaian</label>
                    <select name="status" class="form-select">
                        <option value="selesai">Selesai (Tugas Selesai Penuh)</option>
                        <option value="proses">Dalam Proses (Perlu Tindak Lanjut Lanjutan)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-success fw-semibold">Simpan Tindak Lanjut</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
