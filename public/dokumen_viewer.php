<?php
// persuratan/public/dokumen_viewer.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check()) {
    http_response_code(403);
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$path = $_GET['path'] ?? '';
// Prevent directory traversal
$path = str_replace(['..', "\0"], '', $path);
$fullPath = STORAGE_PATH . '/' . ltrim($path, '/');

if (empty($path) || !file_exists($fullPath) || is_dir($fullPath)) {
    http_response_code(404);
    die("Dokumen tidak ditemukan.");
}

$mime = mime_content_type($fullPath);
if (!$mime) {
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mime = match($ext) {
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'application/octet-stream'
    };
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
header('Cache-Control: private, max-age=3600');

readfile($fullPath);
exit;
