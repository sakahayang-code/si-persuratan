<?php
// persuratan/public/disposisi/cetak.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);
$db = Database::getInstance();

$stmt = $db->prepare("
    SELECT d.*, sm.nomor_agenda, sm.nomor_surat, sm.tanggal_surat, sm.tanggal_terima,
           sm.pengirim, sm.asal_instansi, sm.perihal, sm.tingkat_urgensi,
           u.full_name as pemberi_nama, u.jabatan as pemberi_jabatan
    FROM disposisi d
    JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
    JOIN users u ON d.pemberi_user_id = u.id
    WHERE d.id = :id
");
$stmt->execute([':id' => $id]);
$disp = $stmt->fetch();

if (!$disp) {
    die("Lembar disposisi tidak ditemukan.");
}

$stmtDet = $db->prepare("
    SELECT dd.*, u.full_name, u.jabatan 
    FROM disposisi_detail dd 
    JOIN users u ON dd.penerima_user_id = u.id 
    WHERE dd.disposisi_id = :id
");
$stmtDet->execute([':id' => $id]);
$details = $stmtDet->fetchAll();

$instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
$alamat = Pengaturan::get('alamat_instansi', 'Jl. Medan Merdeka Barat No. 12, Jakarta');
$telp = Pengaturan::get('telepon_instansi', '(021) 3845678');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi - <?= htmlspecialchars($disp['nomor_agenda']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5pt;
            color: #000;
            padding: 25px;
            max-width: 800px;
            margin: 0 auto;
        }
        .disp-box {
            border: 2px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h3 { margin: 0; text-transform: uppercase; font-size: 13pt; }
        .header p { margin: 3px 0 0 0; font-size: 8.5pt; color: #444; }
        .title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .grid-table td, .grid-table th {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .checkbox-item {
            display: inline-block;
            margin-right: 15px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; text-align: right;">
    <button onclick="window.print()" style="padding: 8px 16px; background: #2563EB; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        🖨️ Cetak Lembar Disposisi
    </button>
</div>

<div class="disp-box">
    <div class="header">
        <h3><?= htmlspecialchars($instansi) ?></h3>
        <p><?= htmlspecialchars($alamat) ?> | Telp: <?= htmlspecialchars($telp) ?></p>
    </div>

    <div class="title">LEMBAR DISPOSISI RESMI</div>

    <table class="grid-table">
        <tr>
            <td width="50%">
                <strong>Surat Dari:</strong><br>
                <?= htmlspecialchars($disp['pengirim']) ?><br>
                <small>(<?= htmlspecialchars($disp['asal_instansi']) ?>)</small>
            </td>
            <td width="50%">
                <strong>Diterima Tanggal:</strong> <?= date('d/m/Y', strtotime($disp['tanggal_terima'])) ?><br>
                <strong>No. Agenda:</strong> <?= htmlspecialchars($disp['nomor_agenda']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal Surat:</strong> <?= date('d/m/Y', strtotime($disp['tanggal_surat'])) ?><br>
                <strong>Nomor Surat:</strong> <?= htmlspecialchars($disp['nomor_surat']) ?>
            </td>
            <td>
                <strong>Sifat Disposisi:</strong><br>
                <span class="checkbox-item">[<?= $disp['sifat'] === 'biasa' ? 'X' : ' ' ?>] Biasa</span>
                <span class="checkbox-item">[<?= $disp['sifat'] === 'penting' ? 'X' : ' ' ?>] Penting</span>
                <span class="checkbox-item">[<?= $disp['sifat'] === 'segera' ? 'X' : ' ' ?>] Segera</span>
                <span class="checkbox-item">[<?= $disp['sifat'] === 'rahasia' ? 'X' : ' ' ?>] Rahasia</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Perihal:</strong><br>
                <div style="font-weight: bold; margin-top: 3px;"><?= htmlspecialchars($disp['perihal']) ?></div>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <strong>Diteruskan Kepada Yth:</strong>
                <ol style="margin: 6px 0 0 15px; padding: 0;">
                    <?php foreach ($details as $d): ?>
                        <li><?= htmlspecialchars($d['full_name']) ?> <small>(<?= htmlspecialchars($d['jabatan']) ?>)</small></li>
                    <?php endforeach; ?>
                </ol>
            </td>
            <td width="50%">
                <strong>Batas Waktu (Tenggat):</strong><br>
                <div style="font-size: 11pt; font-weight: bold; margin-top: 5px;">
                    <?= !empty($disp['tenggat_waktu']) ? date('d F Y', strtotime($disp['tenggat_waktu'])) : 'Sesuai Ketentuan' ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="min-height: 120px;">
                <strong>Instruksi / Catatan Pimpinan:</strong>
                <div style="margin-top: 8px; font-style: italic; line-height: 1.5; min-height: 80px;">
                    "<?= nl2br(htmlspecialchars($disp['catatan_instruksi'])) ?>"
                </div>

                <div style="float: right; text-align: center; width: 220px; margin-top: 15px;">
                    Jakarta, <?= date('d F Y', strtotime($disp['created_at'])) ?><br>
                    <strong><?= htmlspecialchars($disp['pemberi_jabatan']) ?></strong><br><br><br>
                    <strong><u><?= htmlspecialchars($disp['pemberi_nama']) ?></u></strong>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
