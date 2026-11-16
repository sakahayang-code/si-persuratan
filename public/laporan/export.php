<?php
// persuratan/public/laporan/export.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('laporan.view')) {
    die("Akses ditolak");
}

$tipeLaporan = $_GET['tipe'] ?? 'surat_masuk';
$format = $_GET['format'] ?? 'excel';
$dateFrom = $_GET['date_from'] ?? '2026-01-01';
$dateTo = $_GET['date_to'] ?? '2026-12-31';
$statusFilter = $_GET['status'] ?? '';

$db = Database::getInstance();

if ($tipeLaporan === 'surat_masuk') {
    $title = "Rekapitulasi Surat Masuk ({$dateFrom} s.d {$dateTo})";
    $headers = ['No', 'No. Agenda', 'No. Surat', 'Tgl Surat', 'Tgl Terima', 'Pengirim', 'Asal Instansi', 'Perihal', 'Urgensi', 'Status'];
    $data = SuratMasuk::getAll(['date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => $statusFilter]);
    $rows = [];
    $no = 1;
    foreach ($data as $r) {
        $rows[] = [
            $no++, $r['nomor_agenda'], $r['nomor_surat'], $r['tanggal_surat'], $r['tanggal_terima'],
            $r['pengirim'], $r['asal_instansi'], $r['perihal'], $r['tingkat_urgensi'], $r['status']
        ];
    }
} elseif ($tipeLaporan === 'surat_keluar') {
    $title = "Rekapitulasi Surat Keluar ({$dateFrom} s.d {$dateTo})";
    $headers = ['No', 'No. Agenda', 'No. Surat', 'Tgl Surat', 'Tujuan', 'Perihal', 'Penandatangan', 'Kerahasiaan', 'Status'];
    $data = SuratKeluar::getAll(['date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => $statusFilter]);
    $rows = [];
    $no = 1;
    foreach ($data as $r) {
        $rows[] = [
            $no++, $r['nomor_agenda'], $r['nomor_surat'], $r['tanggal_surat'],
            $r['tujuan'], $r['perihal'], $r['pejabat_nama'] ?? '-', $r['tingkat_kerahasiaan'], $r['status']
        ];
    }
} else {
    $title = "Rekapitulasi Disposisi ({$dateFrom} s.d {$dateTo})";
    $headers = ['No', 'Surat Masuk', 'Pemberi Disposisi', 'Instruksi', 'Sifat', 'Tenggat Waktu', 'Waktu Disposisi'];
    $stmt = $db->prepare("
        SELECT d.*, sm.nomor_surat, sm.perihal, u.full_name as pemberi_nama
        FROM disposisi d
        JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
        JOIN users u ON d.pemberi_user_id = u.id
        WHERE d.created_at >= :from AND d.created_at <= :to
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([':from' => $dateFrom . ' 00:00:00', ':to' => $dateTo . ' 23:59:59']);
    $data = $stmt->fetchAll();
    $rows = [];
    $no = 1;
    foreach ($data as $r) {
        $rows[] = [
            $no++, $r['nomor_surat'] . ' - ' . $r['perihal'], $r['pemberi_nama'],
            $r['catatan_instruksi'], $r['sifat'], $r['tenggat_waktu'] ?? '-', $r['created_at']
        ];
    }
}

if ($format === 'excel') {
    ExcelHelper::exportTable($title, $headers, $rows, "Laporan_{$tipeLaporan}_" . date('Ymd') . ".xls");
} else {
    $instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title>';
    $html .= '<style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #111; }
        .hdr { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .hdr h3 { margin: 0; font-size: 13pt; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2563EB; color: #fff; padding: 6px; border: 1px solid #ccc; font-size: 8pt; text-align: left; }
        td { padding: 5px; border: 1px solid #ccc; font-size: 8.5pt; vertical-align: top; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style></head><body>';
    $html .= '<div class="hdr"><h3>' . htmlspecialchars($instansi) . '</h3>';
    $html .= '<p>' . htmlspecialchars(Pengaturan::get('alamat_instansi')) . ' | Dicetak pada: ' . date('d F Y H:i') . ' WIB</p></div>';
    $html .= '<h4 style="text-align: center; margin-bottom: 12px; text-transform: uppercase;">' . htmlspecialchars($title) . '</h4>';
    $html .= '<table><thead><tr>';
    foreach ($headers as $h) {
        $html .= '<th>' . htmlspecialchars($h) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $html .= '<tr>';
        foreach ($r as $c) {
            $html .= '<td>' . htmlspecialchars((string)$c) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    PdfHelper::streamHtml($html, "Laporan_{$tipeLaporan}_" . date('Ymd') . ".pdf", 'A4', 'landscape');
}
