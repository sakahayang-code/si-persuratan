<?php
// persuratan/public/surat_keluar/preview.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('surat_keluar.view')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('surat_keluar/');
}

$id = (int)($_GET['id'] ?? 0);
$surat = SuratKeluar::findById($id);

if (!$surat) {
    Flash::set('danger', 'Surat keluar tidak ditemukan.');
    redirect('surat_keluar/');
}

$pageTitle = 'Preview Surat - ' . $surat['nomor_surat'];

// Render document HTML
$letterHtml = TemplateSurat::renderLetterHtml($surat);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('surat_keluar/') ?>">Surat Keluar</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($surat['nomor_agenda']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold m-0"><?= htmlspecialchars($surat['nomor_surat']) ?></h4>
            <?php
            $stBadge = match($surat['status']) {
                'draft' => 'bg-secondary text-white',
                'menunggu_persetujuan' => 'badge-soft-warning',
                'disetujui' => 'badge-soft-primary',
                'diterbitkan' => 'badge-soft-success',
                'dikirim' => 'badge-soft-purple',
                default => 'bg-light text-dark'
            };
            ?>
            <span class="badge <?= $stBadge ?> fs-6"><?= ucfirst(str_replace('_', ' ', $surat['status'])) ?></span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('surat_keluar/pdf.php') ?>?id=<?= $surat['id'] ?>" target="_blank" class="btn btn-outline-danger d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="file-down" style="width: 16px;"></i>
            <span>Unduh PDF Resmi</span>
        </a>
        <button onclick="printLetterFrame()" class="btn btn-outline-secondary d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="printer" style="width: 16px;"></i>
            <span>Cetak Langsung</span>
        </button>

        <?php if (Auth::can('surat_keluar.approve') && in_array($surat['status'], ['draft', 'menunggu_persetujuan'])): ?>
            <a href="<?= url('surat_keluar/action.php') ?>?act=approve&id=<?= $surat['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="btn btn-success d-flex align-items-center gap-2 shadow-sm rounded-3">
                <i data-lucide="check-circle" style="width: 16px;"></i>
                <span>Setujui Dokumen (Pimpinan)</span>
            </a>
        <?php endif; ?>

        <?php if (in_array($surat['status'], ['disetujui'])): ?>
            <a href="<?= url('surat_keluar/action.php') ?>?act=publish&id=<?= $surat['id'] ?>&csrf=<?= Auth::csrfToken() ?>" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-3">
                <i data-lucide="send" style="width: 16px;"></i>
                <span>Terbitkan & Arsipkan Surat</span>
            </a>
        <?php endif; ?>

        <?php if (!empty($surat['token_verifikasi'])): ?>
            <a href="<?= url('verifikasi.php') ?>?doc=<?= urlencode($surat['token_verifikasi']) ?>" target="_blank" class="btn btn-outline-primary bg-white d-flex align-items-center gap-1 rounded-3">
                <i data-lucide="qr-code" style="width: 16px;"></i>
                <span>Cek QR Publik</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Main Letter Preview Frame (Styled Paper Simulation) -->
    <div class="col-12 col-xl-9">
        <div class="card border shadow-sm rounded-3 bg-white p-3 p-md-5" style="min-height: 850px; max-width: 900px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <iframe id="letterPreviewFrame" srcdoc="<?= htmlspecialchars($letterHtml) ?>" style="width: 100%; height: 800px; border: none;"></iframe>
        </div>
    </div>

    <!-- Metadata & Verification Sidebar -->
    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold m-0">Metadata Persuratan</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Pembuat Draf:</span>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($surat['pembuat_nama'] ?? 'Petugas') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Penandatangan:</span>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($surat['pejabat_nama'] ?? 'Kepala Badan') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Disetujui Oleh:</span>
                        <span class="text-dark"><?= !empty($surat['penyetuju_nama']) ? htmlspecialchars($surat['penyetuju_nama']) : '<em class="text-muted">Belum disetujui</em>' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Waktu Verifikasi:</span>
                        <span class="text-dark"><?= !empty($surat['verified_at']) ? date('d/m/Y H:i', strtotime($surat['verified_at'])) : '-' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tingkat Kerahasiaan:</span>
                        <span class="badge bg-light text-dark border"><?= ucfirst($surat['tingkat_kerahasiaan']) ?></span>
                    </li>
                </ul>

                <?php if (!empty($surat['token_verifikasi'])): ?>
                    <div class="mt-3 p-3 bg-light rounded text-center border">
                        <div class="text-muted small mb-1 fw-bold">TOKEN VERIFIKASI DIGITAL:</div>
                        <div class="font-monospace text-primary fw-bold small text-break"><?= htmlspecialchars($surat['token_verifikasi']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function printLetterFrame() {
    const frame = document.getElementById('letterPreviewFrame');
    if (frame) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
