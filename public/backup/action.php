<?php
// persuratan/public/backup/action.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('backup.manage')) {
    header('Location: /login.php');
    exit;
}

$act = $_GET['act'] ?? '';
$csrf = $_GET['csrf'] ?? ($_POST['csrf_token'] ?? '');

if (!Auth::verifyCsrf($csrf)) {
    Flash::set('danger', 'Token CSRF tidak valid.');
    header('Location: /backup/');
    exit;
}

$db = Database::getInstance();

try {
    if ($act === 'create') {
        $result = BackupService::createBackup('manual', 'Backup manual oleh ' . Auth::user()['full_name']);
        Flash::set('success', "Backup berhasil dibuat: {$result['filename']} (" . round($result['filesize'] / 1024, 2) . " KB)");
    } elseif ($act === 'restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_FILES['backup_file']['name']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['backup_file']['tmp_name'];
            $res = BackupService::restoreBackup($tmpPath);
            Flash::set('success', "Pemulihan berhasil! Status integritas: {$res['integrity']['status']}. Safety backup kondisi awal telah dibuat otomatis.");
        } else {
            Flash::set('danger', 'Gagal mengunggah file arsip backup.');
        }
    } elseif ($act === 'restore_existing') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT filepath FROM backup_log WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $filepath = $stmt->fetchColumn();

        if ($filepath && file_exists($filepath)) {
            $res = BackupService::restoreBackup($filepath);
            Flash::set('success', "Pemulihan dari arsip lokal berhasil! Status integritas: {$res['integrity']['status']}.");
        } else {
            Flash::set('danger', 'File arsip tidak ditemukan di disk.');
        }
    } elseif ($act === 'download') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT filename, filepath FROM backup_log WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row && file_exists($row['filepath'])) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $row['filename'] . '"');
            header('Content-Length: ' . filesize($row['filepath']));
            readfile($row['filepath']);
            exit;
        } else {
            Flash::set('danger', 'Berkas arsip tidak ditemukan.');
        }
    }
} catch (Throwable $e) {
    Flash::set('danger', 'Kesalahan proses backup/restore: ' . $e->getMessage());
}

header('Location: /backup/');
exit;
