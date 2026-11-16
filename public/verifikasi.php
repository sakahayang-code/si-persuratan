<?php
// persuratan/public/verifikasi.php

require_once __DIR__ . '/../config/app.php';

$token = trim($_GET['doc'] ?? ($_GET['token'] ?? ''));
$surat = null;

if (!empty($token)) {
    $surat = SuratKeluar::findByToken($token);
}

$instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
$alamat = Pengaturan::get('alamat_instansi', 'Jl. Medan Merdeka Barat No. 12, Jakarta');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Dokumen - SI-PERSURATAN</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        body {
            background-color: #F1F5F9;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .verif-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid #E2E8F0;
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }
        .verif-header {
            background: #0F172A;
            color: #FFFFFF;
            padding: 1.75rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="verif-card">
    <div class="verif-header">
        <img src="/assets/img/logo-instansi.svg" alt="Logo" width="48" height="48" class="mb-2">
        <h5 class="fw-bold m-0" style="letter-spacing: -0.02em;">SI-PERSURATAN</h5>
        <div class="text-secondary small">Layanan Verifikasi Keabsahan Dokumen Dinas</div>
    </div>

    <div class="p-4 p-md-5">
        <?php if ($surat): ?>
            <!-- Verified Document Badge -->
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 bg-success-subtle text-success rounded-circle mb-3">
                    <i data-lucide="shield-check" style="width: 48px; height: 48px;"></i>
                </div>
                <h5 class="fw-bold text-success mb-1">DOKUMEN RESMI TERVERIFIKASI</h5>
                <p class="text-muted small m-0">Dokumen ini sah terdaftar pada basis data persuratan resmi instansi.</p>
            </div>

            <div class="border rounded-3 p-3 bg-light mb-4">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td width="130" class="text-muted">Instansi Penerbit:</td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($instansi) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Surat:</td>
                        <td class="fw-bold text-primary font-monospace fs-6"><?= htmlspecialchars($surat['nomor_surat']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Surat:</td>
                        <td class="text-dark"><?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Perihal:</td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($surat['perihal']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tujuan Surat:</td>
                        <td class="text-dark"><?= nl2br(htmlspecialchars($surat['tujuan'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Penandatangan:</td>
                        <td class="text-dark">
                            <strong><?= htmlspecialchars($surat['pejabat_nama']) ?></strong><br>
                            <span class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($surat['pejabat_jabatan']) ?> (NIP. <?= htmlspecialchars($surat['pejabat_nip']) ?>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Dokumen:</td>
                        <td>
                            <span class="badge bg-success">Resmi Diterbitkan</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Token Digital:</td>
                        <td class="font-monospace text-secondary" style="font-size: 11px;"><?= htmlspecialchars($surat['token_verifikasi']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="text-center text-muted small">
                Diperiksa pada: <?= date('d F Y H:i:s') ?> WIB
            </div>

        <?php else: ?>
            <!-- Invalid / Not Found Badge -->
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 bg-danger-subtle text-danger rounded-circle mb-3">
                    <i data-lucide="shield-alert" style="width: 48px; height: 48px;"></i>
                </div>
                <h5 class="fw-bold text-danger mb-1">DOKUMEN TIDAK TERDAFTAR</h5>
                <p class="text-muted small">Token verifikasi tidak ditemukan atau dokumen telah dicabut dari sistem resmi.</p>
            </div>

            <div class="p-3 bg-light rounded text-center small text-secondary mb-4">
                Pastikan Anda memindai kode QR asli yang tercetak pada dokumen resmi <?= htmlspecialchars($instansi) ?>.
            </div>

            <div class="text-center">
                <a href="/login.php" class="btn btn-outline-primary btn-sm px-4">Buka Portal SI-PERSURATAN</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>
