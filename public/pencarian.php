<?php
// persuratan/public/pencarian.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$query = trim($_GET['q'] ?? '');
$pageTitle = 'Pencarian Global: ' . ($query ?: 'Semua');

$suratMasukResults = [];
$suratKeluarResults = [];
$arsipResults = [];

if (!empty($query)) {
    $suratMasukResults = SuratMasuk::getAll(['search' => $query]);
    $suratKeluarResults = SuratKeluar::getAll(['search' => $query]);
    $arsipResults = Arsip::getAll(['search' => $query]);
}

$totalMatches = count($suratMasukResults) + count($suratKeluarResults) + count($arsipResults);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Hasil Pencarian Global</h4>
    <p class="text-muted small m-0">Menampilkan <?= $totalMatches ?> hasil untuk kata kunci: <strong class="text-primary">"<?= htmlspecialchars($query) ?>"</strong></p>
</div>

<!-- Search Input -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="/pencarian.php" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i data-lucide="search" style="width: 16px;"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari nomor surat, perihal, pengirim, arsip..." value="<?= htmlspecialchars($query) ?>" autofocus>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">Cari</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($query)): ?>
    <div class="card border-0 shadow-sm rounded-3 text-center py-5">
        <i data-lucide="search" style="width:48px; height:48px;" class="mx-auto text-secondary opacity-50 mb-2"></i>
        <p class="text-muted mb-0">Silakan masukkan nomor surat, nama pengirim, atau perihal pada kotak pencarian di atas.</p>
    </div>
<?php else: ?>
    <!-- Surat Masuk Matches -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0">Surat Masuk Terkait (<?= count($suratMasukResults) ?>)</h6>
            <a href="/surat_masuk/?search=<?= urlencode($query) ?>" class="btn btn-sm btn-light text-primary">Lihat di Modul</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($suratMasukResults)): ?>
                <div class="p-3 text-muted small text-center">Tidak ada surat masuk yang cocok.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($suratMasukResults as $sm): ?>
                        <a href="/surat_masuk/detail.php?id=<?= $sm['id'] ?>" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($sm['perihal']) ?></span>
                                <span class="badge bg-light text-secondary border"><?= htmlspecialchars($sm['nomor_agenda']) ?></span>
                            </div>
                            <div class="text-muted small mt-1">
                                No: <strong><?= htmlspecialchars($sm['nomor_surat']) ?></strong> • Dari: <?= htmlspecialchars($sm['pengirim']) ?> • Tgl: <?= date('d/m/Y', strtotime($sm['tanggal_surat'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Surat Keluar Matches -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0">Surat Keluar Terkait (<?= count($suratKeluarResults) ?>)</h6>
            <a href="/surat_keluar/?search=<?= urlencode($query) ?>" class="btn btn-sm btn-light text-primary">Lihat di Modul</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($suratKeluarResults)): ?>
                <div class="p-3 text-muted small text-center">Tidak ada surat keluar yang cocok.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($suratKeluarResults as $sk): ?>
                        <a href="/surat_keluar/preview.php?id=<?= $sk['id'] ?>" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($sk['perihal']) ?></span>
                                <span class="badge bg-light text-secondary border"><?= htmlspecialchars($sk['nomor_agenda']) ?></span>
                            </div>
                            <div class="text-muted small mt-1">
                                No: <strong><?= htmlspecialchars($sk['nomor_surat']) ?></strong> • Tujuan: <?= htmlspecialchars($sk['tujuan']) ?> • Status: <?= ucfirst($sk['status']) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Arsip Matches -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0">Arsip Digital Terkait (<?= count($arsipResults) ?>)</h6>
            <a href="/arsip/?search=<?= urlencode($query) ?>" class="btn btn-sm btn-light text-primary">Lihat di Modul</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($arsipResults)): ?>
                <div class="p-3 text-muted small text-center">Tidak ada arsip digital yang cocok.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($arsipResults as $ar): ?>
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($ar['judul']) ?></div>
                                <div class="text-muted small">No Dok: <?= htmlspecialchars($ar['nomor_dokumen']) ?> • Lokasi: <?= htmlspecialchars($ar['lokasi_fisik']) ?></div>
                            </div>
                            <?php if (!empty($ar['file_path'])): ?>
                                <a href="/dokumen_viewer.php?path=<?= urlencode($ar['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Arsip</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
