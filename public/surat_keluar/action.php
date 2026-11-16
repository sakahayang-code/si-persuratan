<?php
// persuratan/public/surat_keluar/action.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$act = $_GET['act'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!Auth::verifyCsrf($csrf)) {
    Flash::set('danger', 'Token CSRF tidak valid.');
    header('Location: /surat_keluar/');
    exit;
}

if ($act === 'approve') {
    if (!Auth::can('surat_keluar.approve')) {
        Flash::set('danger', 'Hanya Pimpinan yang berwenang menyetujui surat keluar.');
    } else {
        SuratKeluar::approve($id);
        Flash::set('success', 'Surat keluar berhasil disetujui secara resmi oleh Pimpinan.');
    }
} elseif ($act === 'publish') {
    SuratKeluar::publish($id);
    Flash::set('success', 'Surat keluar berhasil diterbitkan dan otomatis diarsipkan ke sistem.');
} elseif ($act === 'delete') {
    if (!Auth::can('surat_keluar.delete')) {
        Flash::set('danger', 'Akses hapus ditolak.');
    } else {
        SuratKeluar::delete($id);
        Flash::set('info', 'Surat keluar telah dihapus.');
    }
}

header('Location: /surat_keluar/preview.php?id=' . $id);
exit;
