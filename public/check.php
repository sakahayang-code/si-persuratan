<?php
// persuratan/public/check.php
// Pemeriksaan Diagnostik Kesiapan Server & cPanel

require_once __DIR__ . '/../config/app.php';

$results = [];

// 1. PHP Version
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.1.0', '>=');
$results['php_version'] = [
    'title' => 'Versi PHP Server',
    'value' => 'PHP ' . $phpVersion,
    'status' => $phpOk,
    'recommended' => 'PHP 8.2 atau lebih baru (minimal 8.1)',
    'help' => 'Ubah versi PHP melalui menu "Select PHP Version" atau "MultiPHP Manager" di cPanel.'
];

// 2. Required PHP Extensions
$requiredExtensions = [
    'pdo_sqlite' => 'Koneksi basis data SQLite melalui PDO',
    'sqlite3' => 'Mesin basis data SQLite3',
    'zip' => 'Pembuatan & pemulihan arsip backup (ZIP)',
    'gd' => 'Pembuatan tanda tangan, stempel resmi & ikon PWA',
    'mbstring' => 'Pemrosesan teks naskah dinas & UTF-8',
    'fileinfo' => 'Validasi tipe berkas scan PDF & lampiran',
    'dom' => 'Pembuatan dokumen PDF resmi via Dompdf',
    'xml' => 'Parser format naskah & dokumen'
];

$extResults = [];
$allExtOk = true;
foreach ($requiredExtensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    if (!$loaded) $allExtOk = false;
    $extResults[$ext] = [
        'loaded' => $loaded,
        'description' => $desc
    ];
}
$results['extensions'] = [
    'title' => 'Ekstensi PHP Wajib',
    'status' => $allExtOk,
    'items' => $extResults,
    'help' => 'Buka menu "Select PHP Version" di cPanel -> Tab "Extensions" -> Centang ekstensi yang bertanda merah.'
];

// 3. Storage Directories Write Permissions
$storageDirs = [
    'storage/database' => STORAGE_PATH . '/database',
    'storage/documents' => STORAGE_PATH . '/documents',
    'storage/backups' => STORAGE_PATH . '/backups',
    'storage/logs' => STORAGE_PATH . '/logs',
    'storage/sessions' => STORAGE_PATH . '/sessions'
];

$dirResults = [];
$allDirsOk = true;
foreach ($storageDirs as $label => $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
    @chmod($path, 0777);
    $isWritable = is_writable($path);
    if (!$isWritable) $allDirsOk = false;
    $perms = file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : '-';

    $dirResults[$label] = [
        'path' => $path,
        'writable' => $isWritable,
        'perms' => $perms
    ];
}
$results['directories'] = [
    'title' => 'Izin Menulis Folder Persisten (/storage)',
    'status' => $allDirsOk,
    'items' => $dirResults,
    'help' => 'Buka File Manager di cPanel, klik kanan pada folder terkait, pilih "Change Permissions" dan ubah ke 775 atau 777.'
];

// 4. Database Connection & Integrity Test
$dbOk = false;
$dbMsg = '';
try {
    $db = Database::getInstance();
    $integrity = Database::checkIntegrity();
    $dbOk = ($integrity['status'] === 'OK');
    $dbMsg = 'Koneksi SQLite berhasil. PRAGMA integrity_check: ' . $integrity['status'];
} catch (Throwable $e) {
    $dbOk = false;
    $dbMsg = $e->getMessage();
}
$results['database'] = [
    'title' => 'Koneksi & Integritas Basis Data SQLite',
    'status' => $dbOk,
    'message' => $dbMsg,
    'help' => 'Pastikan folder storage/database memiliki izin tulis dan file persuratan.sqlite tidak berstatus read-only.'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Sistem & Deployment cPanel - SI-PERSURATAN</title>
    <link rel="stylesheet" href="<?= url('assets/css/bootstrap.min.css') ?>">
    <style>
        body { background-color: #F1F5F9; font-family: system-ui, -apple-system, sans-serif; padding: 30px 15px; }
        .card-diag { background: #fff; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 20px; }
        .badge-ok { background: #ECFDF5; color: #059669; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
        .badge-fail { background: #FEF2F2; color: #DC2626; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    </style>
</head>
<body>

<div class="container" style="max-width: 860px;">
    <!-- Header -->
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark m-0">Diagnosa Kesiapan Server & cPanel</h3>
        <p class="text-muted small mt-1">SI-PERSURATAN • Alat Deteksi Otomatis & Pemecahan Masalah Deployment</p>
    </div>

    <!-- Overall Summary Alert -->
    <?php if ($phpOk && $allExtOk && $allDirsOk && $dbOk): ?>
        <div class="alert alert-success d-flex align-items-center gap-3 p-3 shadow-sm rounded-3 mb-4">
            <div style="font-size: 28px;">✅</div>
            <div>
                <h5 class="fw-bold mb-1">Semua Persyaratan Server Terpenuhi!</h5>
                <p class="mb-0 small">Server dan konfigurasi cPanel Anda siap menjalankan aplikasi SI-PERSURATAN dengan lancar.</p>
            </div>
            <a href="<?= url('login.php') ?>" class="btn btn-success fw-semibold ms-auto px-4">Buka Aplikasi</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger d-flex align-items-start gap-3 p-3 shadow-sm rounded-3 mb-4">
            <div style="font-size: 28px;">⚠️</div>
            <div>
                <h5 class="fw-bold mb-1">Ditemukan Kendala Konfigurasi Server</h5>
                <p class="mb-0 small">Silakan periksa item bertanda merah di bawah dan ikuti petunjuk perbaikan cPanel yang tertera.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. PHP Version -->
    <div class="card card-diag">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark m-0"><?= $results['php_version']['title'] ?></h6>
                <span class="<?= $results['php_version']['status'] ? 'badge-ok' : 'badge-fail' ?>">
                    <?= $results['php_version']['status'] ? 'SESUAI' : 'PERLU UPDATE' ?>
                </span>
            </div>
            <div class="fs-5 fw-bold text-primary mb-1"><?= $results['php_version']['value'] ?></div>
            <div class="text-muted small mb-2">Rekomendasi: <?= $results['php_version']['recommended'] ?></div>
            <?php if (!$results['php_version']['status']): ?>
                <div class="p-2 bg-light border border-danger text-danger rounded small">
                    <strong>Solusi:</strong> <?= $results['php_version']['help'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. Extensions -->
    <div class="card card-diag">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark m-0"><?= $results['extensions']['title'] ?></h6>
                <span class="<?= $results['extensions']['status'] ? 'badge-ok' : 'badge-fail' ?>">
                    <?= $results['extensions']['status'] ? 'LENGKAP' : 'BELUM LENGKAP' ?>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 small">
                    <thead class="bg-light">
                        <tr>
                            <th>Nama Ekstensi</th>
                            <th>Fungsi dalam Aplikasi</th>
                            <th width="100" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['extensions']['items'] as $extName => $info): ?>
                            <tr>
                                <td class="font-monospace fw-bold"><?= $extName ?></td>
                                <td class="text-secondary"><?= $info['description'] ?></td>
                                <td class="text-center">
                                    <span class="<?= $info['loaded'] ? 'badge-ok' : 'badge-fail' ?>">
                                        <?= $info['loaded'] ? '✓ Aktif' : '✗ Tidak Aktif' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$results['extensions']['status']): ?>
                <div class="mt-3 p-3 bg-light border border-danger text-danger rounded small">
                    <strong>Petunjuk cPanel:</strong> <?= $results['extensions']['help'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Storage Permissions -->
    <div class="card card-diag">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark m-0"><?= $results['directories']['title'] ?></h6>
                <span class="<?= $results['directories']['status'] ? 'badge-ok' : 'badge-fail' ?>">
                    <?= $results['directories']['status'] ? 'WRITABLE' : 'PERMISSION DENIED' ?>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 small">
                    <thead class="bg-light">
                        <tr>
                            <th>Direktori</th>
                            <th>Izin Saat Ini</th>
                            <th width="120" class="text-center">Status Tulis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['directories']['items'] as $lbl => $info): ?>
                            <tr>
                                <td class="font-monospace"><?= $lbl ?></td>
                                <td class="font-monospace"><?= $info['perms'] ?></td>
                                <td class="text-center">
                                    <span class="<?= $info['writable'] ? 'badge-ok' : 'badge-fail' ?>">
                                        <?= $info['writable'] ? 'Dapat Ditulis' : 'Terkunci (ReadOnly)' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$results['directories']['status']): ?>
                <div class="mt-3 p-3 bg-light border border-danger text-danger rounded small">
                    <strong>Petunjuk cPanel:</strong> <?= $results['directories']['help'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Database Connection -->
    <div class="card card-diag">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark m-0"><?= $results['database']['title'] ?></h6>
                <span class="<?= $results['database']['status'] ? 'badge-ok' : 'badge-fail' ?>">
                    <?= $results['database']['status'] ? 'TERHUBUNG' : 'GAGAL' ?>
                </span>
            </div>
            <div class="p-2 bg-light rounded text-secondary font-monospace small mb-2">
                <?= htmlspecialchars($results['database']['message']) ?>
            </div>
            <?php if (!$results['database']['status']): ?>
                <div class="p-3 bg-light border border-danger text-danger rounded small">
                    <strong>Petunjuk cPanel:</strong> <?= $results['database']['help'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- cPanel Deployment Guide Accordion -->
    <div class="card card-diag">
        <div class="card-body p-4 small">
            <h6 class="fw-bold text-dark mb-2">📋 2 Panduan Metode Deploy ke cPanel:</h6>
            <div class="mb-3">
                <strong>Metode A (Rekomendasi - Document Root ke public/):</strong>
                <ol class="mb-0 ps-3">
                    <li>Di cPanel -> <strong>Domains</strong> (atau Subdomains), atur <strong>Document Root</strong> domain mengarah ke folder: <code>public_html/public</code>.</li>
                    <li>Ekstrak seluruh file repo ke <code>public_html</code>.</li>
                    <li>Aplikasi otomatis terbuka langsung tanpa perlu konfigurasi tambahan.</li>
                </ol>
            </div>
            <div>
                <strong>Metode B (Upload Langsung ke public_html):</strong>
                <ol class="mb-0 ps-3">
                    <li>Ekstrak file ke <code>public_html</code>. File <code>.htaccess</code> bawaan akan otomatis mengarahkan pengunjung ke folder <code>public/</code> secara transparan.</li>
                    <li>Pastikan folder <code>storage/</code> memiliki permission <strong>775</strong> atau <strong>777</strong>.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

</body>
</html>
