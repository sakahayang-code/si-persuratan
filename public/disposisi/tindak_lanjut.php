<?php
// persuratan/public/disposisi/tindak_lanjut.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /disposisi/');
    exit;
}

if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
    Flash::set('danger', 'Token CSRF tidak valid.');
    header('Location: /disposisi/');
    exit;
}

$suratId = (int)($_POST['surat_masuk_id'] ?? 0);
$tindakan = trim($_POST['tindakan'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$status = $_POST['status'] ?? 'selesai';

if ($suratId <= 0 || empty($tindakan)) {
    Flash::set('danger', 'Tindakan yang dilakukan wajib diisi.');
    header('Location: /surat_masuk/detail.php?id=' . $suratId);
    exit;
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

header('Location: /surat_masuk/detail.php?id=' . $suratId);
exit;
