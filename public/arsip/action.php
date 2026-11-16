<?php
// persuratan/public/arsip/action.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('arsip.manage')) {
    header('Location: /arsip/');
    exit;
}

$act = $_GET['act'] ?? ($_POST['act'] ?? '');
$id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
$csrf = $_GET['csrf'] ?? ($_POST['csrf_token'] ?? '');

if (!Auth::verifyCsrf($csrf)) {
    Flash::set('danger', 'Token CSRF tidak valid.');
    header('Location: /arsip/');
    exit;
}

if ($act === 'toggle_star') {
    Arsip::toggleImportant($id);
} elseif ($act === 'retention') {
    $status = $_GET['status'] ?? 'aktif';
    Arsip::updateRetentionStatus($id, $status);
    Flash::set('success', "Status retensi berkas diubah menjadi: {$status}");
} elseif ($act === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadedPath = '';
    if (!empty($_FILES['file_dokumen']['name']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            $safeName = 'arsip_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $target = DOCUMENTS_PATH . '/lampiran/' . $safeName;
            if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $target)) {
                $uploadedPath = 'documents/lampiran/' . $safeName;
            }
        }
    }

    Arsip::create([
        'judul' => $_POST['judul'],
        'nomor_dokumen' => $_POST['nomor_dokumen'],
        'tanggal_dokumen' => $_POST['tanggal_dokumen'],
        'jenis_dokumen' => $_POST['jenis_dokumen'],
        'klasifikasi_id' => !empty($_POST['klasifikasi_id']) ? (int)$_POST['klasifikasi_id'] : null,
        'tahun' => (int)date('Y', strtotime($_POST['tanggal_dokumen'])),
        'lokasi_fisik' => $_POST['lokasi_fisik'] ?? '',
        'file_path' => $uploadedPath
    ]);
    Flash::set('success', 'Dokumen berhasil disimpan ke arsip digital.');
}

header('Location: /arsip/');
exit;
