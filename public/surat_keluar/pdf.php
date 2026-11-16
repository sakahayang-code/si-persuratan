<?php
// persuratan/public/surat_keluar/pdf.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);
$surat = SuratKeluar::findById($id);

if (!$surat) {
    die("Surat tidak ditemukan");
}

$html = TemplateSurat::renderLetterHtml($surat);
$safeFilename = 'Surat_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $surat['nomor_surat']) . '.pdf';

PdfHelper::streamHtml($html, $safeFilename);
