<?php
// persuratan/public/generator/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || (!Auth::can('template.manage') && !Auth::can('surat_keluar.create'))) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Generator Surat Otomatis';
$templates = TemplateSurat::getAll();

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Generator Surat Otomatis</h4>
        <p class="text-muted small m-0">Pilih salah satu dari 16 template surat dinas terstandarisasi untuk membuat dokumen dalam hitungan menit.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('surat_keluar/') ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2 bg-white rounded-3">
            <i data-lucide="send" style="width: 16px;"></i>
            <span>Daftar Surat Keluar</span>
        </a>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($templates as $t): ?>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 hover-shadow transition">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="p-2 bg-primary-subtle text-primary rounded-3">
                            <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($t['kode']) ?></span>
                    </div>

                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($t['nama_template']) ?></h6>
                    <p class="text-muted small mb-3 flex-grow-1">
                        Format pola penomoran: <br>
                        <code class="text-secondary"><?= htmlspecialchars($t['format_nomor'] ?? '{nomor}/{kode}/BPTI/{tahun}') ?></code>
                    </p>

                    <a href="<?= url('generator/buat.php') ?>?id=<?= $t['id'] ?>" class="btn btn-outline-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-2 rounded-2 mt-auto">
                        <i data-lucide="edit" style="width: 16px;"></i>
                        <span>Gunakan Template</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
