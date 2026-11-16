<?php
// persuratan/public/disposisi/tindak_lanjut.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('disposisi/');
}

if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
    Flash::set('danger', 'Token CSRF tidak valid.');
    redirect('disposisi/');
}

$suratId = (int)($_POST['surat_masuk_id'] ?? 0);
$tindakan = trim($_POST['tindakan'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$status = $_POST['status'] ?? 'selesai';

if ($suratId <= 0 || empty($tindakan)) {
    Flash::set('danger', 'Tindakan yang dilakukan wajib diisi.');
    redirect('surat_masuk/detail.php?id=' . $suratId);
}

try {
    TindakLanjut::create([
        'surat_masuk_id' => $suratId,
        'user_id' => Auth::id(),
        'tindakan' => $tindakan,
        'catatan' => $catatan,
        'status' => $status
    ]);

    Flash::set('success', 'Catatan tindak lanjut berhasil disimpan.');
} catch (Throwable $e) {
    Flash::set('danger', 'Gagal menyimpan tindak lanjut: ' . $e->getMessage());
}

redirect('surat_masuk/detail.php?id=' . $suratId);
