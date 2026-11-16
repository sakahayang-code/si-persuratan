<?php
// persuratan/public/agenda/export.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('agenda.view')) {
    die("Akses ditolak");
}

$jenis = $_GET['jenis'] ?? 'masuk';
$format = $_GET['format'] ?? 'excel';

$filters = [
    'jenis' => $jenis,
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$agendaList = Agenda::getAll($filters);

$title = 'Buku Agenda ' . ($jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar');
$headers = ['No', 'No. Agenda', 'Tgl Agenda', 'Nomor Surat', 'Tanggal Surat', ($jenis === 'masuk' ? 'Pengirim / Asal' : 'Tujuan Surat'), 'Perihal', 'Keterangan'];

$rows = [];
$no = 1;
foreach ($agendaList as $a) {
    $rows[] = [
        $no++,
        $a['nomor_agenda'],
        date('d/m/Y', strtotime($a['tanggal_agenda'])),
        $a['nomor_surat'],
        date('d/m/Y', strtotime($a['tanggal_surat'])),
        $a['pengirim_penerima'],
        $a['perihal'],
        $a['keterangan'] ?? '-'
    ];
}

if ($format === 'excel') {
    ExcelHelper::exportTable($title, $headers, $rows, "Buku_Agenda_{$jenis}_" . date('Ymd') . ".xls");
} else {
    // PDF Export
    $instansi = Pengaturan::get('nama_instansi', 'BADAN PENGEMBANGAN TEKNOLOGI INFORMASI DAN ARSIP');
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title>';
    $html .= '<style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #111; }
        .hdr { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .hdr h3 { margin: 0; font-size: 13pt; text-transform: uppercase; }
        .hdr p { margin: 2px 0 0 0; font-size: 8pt; color: #555; }
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

    PdfHelper::streamHtml($html, "Buku_Agenda_{$jenis}_" . date('Ymd') . ".pdf", 'A4', 'landscape');
}
