<?php
// persuratan/public/surat_masuk/tanda_terima.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$surat = SuratMasuk::findById($id);

if (!$surat) {
    die("Surat tidak ditemukan");
}

$instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
$alamat = Pengaturan::get('alamat_instansi', 'Jl. Medan Merdeka Barat No. 12, Jakarta');
$telp = Pengaturan::get('telepon_instansi', '(021) 3845678');

// Verification QR code for receipt
$verifUrl = QrCodeHelper::getVerificationUrl('SM-' . $surat['id'] . '-' . date('Y'));
$qrData = QrCodeHelper::generateDataUri($verifUrl);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Surat - <?= htmlspecialchars($surat['nomor_agenda']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 30px;
            max-width: 750px;
            margin: 0 auto;
        }
        .receipt-card {
            border: 2px solid #000;
            padding: 25px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0 5px 0;
            text-align: center;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table-data td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 220px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #2563EB; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        🖨️ Cetak Tanda Terima
    </button>
</div>

<div class="receipt-card">
    <div class="header">
        <h3 style="margin: 0; text-transform: uppercase;"><?= htmlspecialchars($instansi) ?></h3>
        <p style="margin: 4px 0 0 0; font-size: 9pt; color: #444;"><?= htmlspecialchars($alamat) ?> | Telp: <?= htmlspecialchars($telp) ?></p>
    </div>

    <div class="title">LEMBAR TANDA TERIMA SURAT MASUK</div>
    <div style="text-align: center; font-size: 10pt; font-family: monospace; margin-bottom: 15px;">
        Nomor Agenda: <strong><?= htmlspecialchars($surat['nomor_agenda']) ?></strong>
    </div>

    <table class="table-data">
        <tr>
            <td width="160"><strong>Nomor Surat</strong></td>
            <td width="10">:</td>
            <td><strong><?= htmlspecialchars($surat['nomor_surat']) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Tanggal Surat</strong></td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></td>
        </tr>
        <tr>
            <td><strong>Tanggal Diterima</strong></td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($surat['tanggal_terima'])) ?></td>
        </tr>
        <tr>
            <td><strong>Pengirim</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($surat['pengirim']) ?> (<?= htmlspecialchars($surat['asal_instansi']) ?>)</td>
        </tr>
        <tr>
            <td><strong>Perihal</strong></td>
            <td>:</td>
            <td><strong><?= htmlspecialchars($surat['perihal']) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Tingkat Urgensi</strong></td>
            <td>:</td>
            <td><?= ucfirst($surat['tingkat_urgensi']) ?></td>
        </tr>
        <tr>
            <td><strong>Petugas Penerima</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($surat['penerima_nama'] ?? 'Petugas Front Office') ?></td>
        </tr>
    </table>

    <div class="footer">
        <div style="text-align: center;">
            <?php if (!empty($qrData)): ?>
                <img src="<?= $qrData ?>" width="80" height="80"><br>
                <span style="font-size: 8pt; color: #555;">Validasi Digital SI-Persuratan</span>
            <?php endif; ?>
        </div>

        <div class="sig-box">
            Jakarta, <?= date('d F Y') ?><br>
            Petugas Penerima Dokumen,<br><br><br><br>
            <strong><u><?= htmlspecialchars($surat['penerima_nama'] ?? Auth::user()['full_name']) ?></u></strong>
        </div>
    </div>
</div>

</body>
</html>
