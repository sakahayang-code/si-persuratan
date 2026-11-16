<?php
// persuratan/app/models/TemplateSurat.php

class TemplateSurat {
    public static function getAll(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM template_surat ORDER BY id ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM template_surat WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByKode(string $kode): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM template_surat WHERE kode = :kode");
        $stmt->execute([':kode' => $kode]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function renderLetterHtml(array $suratKeluar, array $vars = []): string {
        $instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
        $subInstansi = Pengaturan::get('sub_instansi', 'Pemerintah Provinsi DKI Jakarta');
        $alamat = Pengaturan::get('alamat_instansi', 'Jl. Medan Merdeka Barat No. 12, Jakarta');
        $telp = Pengaturan::get('telepon_instansi', '(021) 3845678');
        $email = Pengaturan::get('email_instansi', 'sekretariat@persuratan.go.id');
        $web = Pengaturan::get('website_instansi', 'https://persuratan.go.id');

        $template = self::findById($suratKeluar['template_id'] ?? 1);
        $content = $template['konten_template'] ?? ($suratKeluar['isi_ringkas'] ?? '');

        // Pejabat info
        $pejabatNama = $suratKeluar['pejabat_nama'] ?? 'Dr. H. Bambang Sudaryanto, M.Si';
        $pejabatJabatan = $suratKeluar['pejabat_jabatan'] ?? 'Kepala Badan';
        $pejabatNip = $suratKeluar['pejabat_nip'] ?? '197205121998031003';

        // QR Code Data URI
        $qrCodeDataUri = '';
        if (!empty($suratKeluar['token_verifikasi'])) {
            $verifUrl = QrCodeHelper::getVerificationUrl($suratKeluar['token_verifikasi']);
            $qrCodeDataUri = QrCodeHelper::generateDataUri($verifUrl);
        }

        // Replace placeholders in content
        $replacements = array_merge([
            '{nomor_surat}' => htmlspecialchars($suratKeluar['nomor_surat'] ?? ''),
            '{tanggal_surat}' => date('d F Y', strtotime($suratKeluar['tanggal_surat'] ?? date('Y-m-d'))),
            '{perihal}' => htmlspecialchars($suratKeluar['perihal'] ?? ''),
            '{tujuan}' => htmlspecialchars($suratKeluar['tujuan'] ?? ''),
            '{nama_pejabat}' => htmlspecialchars($pejabatNama),
            '{jabatan_pejabat}' => htmlspecialchars($pejabatJabatan),
            '{nip_pejabat}' => htmlspecialchars($pejabatNip),
        ], $vars);

        foreach ($replacements as $key => $val) {
            $content = str_replace($key, (string)$val, $content);
        }

        // Build Full Official HTML Letter
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($suratKeluar['perihal'] ?? 'Surat Resmi') . '</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
            position: relative;
        }
        .kop-instansi-sub {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .kop-instansi {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0;
        }
        .kop-kontak {
            font-size: 9.5pt;
            font-style: italic;
            margin: 0;
        }
        .meta-surat {
            margin-bottom: 20px;
        }
        .meta-surat table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-surat td {
            vertical-align: top;
            padding: 2px 0;
        }
        .tujuan-surat {
            margin-bottom: 25px;
        }
        .isi-surat {
            text-align: justify;
            margin-bottom: 35px;
        }
        .isi-surat p {
            margin-bottom: 12px;
            text-indent: 30px;
        }
        .table-content {
            width: 100%;
            margin: 10px 0 15px 30px;
            border-collapse: collapse;
        }
        .table-content td {
            padding: 3px 0;
            vertical-align: top;
        }
        .tanda-tangan-box {
            float: right;
            width: 280px;
            text-align: center;
            margin-top: 20px;
        }
        .qr-verifikasi-box {
            float: left;
            width: 220px;
            font-size: 8.5pt;
            margin-top: 20px;
            color: #333;
        }
        .clear {
            clear: both;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h4 class="kop-instansi-sub">' . htmlspecialchars($subInstansi) . '</h4>
        <h2 class="kop-instansi">' . htmlspecialchars($instansi) . '</h2>
        <p class="kop-kontak">' . htmlspecialchars($alamat) . '<br>Telp: ' . htmlspecialchars($telp) . ' | Email: ' . htmlspecialchars($email) . ' | Web: ' . htmlspecialchars($web) . '</p>
    </div>

    <div class="meta-surat">
        <table>
            <tr>
                <td width="90">Nomor</td>
                <td width="15">:</td>
                <td width="350"><strong>' . htmlspecialchars($suratKeluar['nomor_surat'] ?? '-') . '</strong></td>
                <td style="text-align: right;">Jakarta, ' . date('d F Y', strtotime($suratKeluar['tanggal_surat'] ?? date('Y-m-d'))) . '</td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>:</td>
                <td>' . ucfirst(htmlspecialchars($suratKeluar['tingkat_kerahasiaan'] ?? 'Biasa')) . '</td>
                <td></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>1 (satu) Berkas</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>' . htmlspecialchars($suratKeluar['perihal'] ?? '-') . '</strong></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="tujuan-surat">
        Kepada Yth.<br>
        <strong>' . nl2br(htmlspecialchars($suratKeluar['tujuan'] ?? '')) . '</strong><br>
        di - Tempat
    </div>

    <div class="isi-surat">
        ' . $content . '
    </div>

    <div class="penutup-section">
        <div class="qr-verifikasi-box">';
        if (!empty($qrCodeDataUri)) {
            $html .= '<img src="' . $qrCodeDataUri . '" width="85" height="85" style="border: 1px solid #ddd; padding: 2px;"><br>
            <span>Scan QR untuk validasi keaslian dokumen via sistem resmi SI-PERSURATAN</span><br>
            <span style="font-family: monospace; font-size: 7pt; color: #666;">Token: ' . htmlspecialchars($suratKeluar['token_verifikasi'] ?? '') . '</span>';
        }
        $html .= '</div>

        <div class="tanda-tangan-box">
            <span>' . htmlspecialchars($pejabatJabatan) . '</span><br><br>
            <div style="height: 65px; display: flex; align-items: center; justify-content: center;">
                <span style="font-family: \'Brush Script MT\', cursive, italic; font-size: 20pt; color: #1E3A8A;">' . htmlspecialchars($pejabatNama) . '</span>
            </div>
            <strong><u>' . htmlspecialchars($pejabatNama) . '</u></strong><br>
            <span>NIP. ' . htmlspecialchars($pejabatNip) . '</span>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>';

        return $html;
    }
}
