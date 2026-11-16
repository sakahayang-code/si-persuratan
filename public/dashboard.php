<?php
// persuratan/public/dashboard.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check()) {
    redirect('login.php');
}

$pageTitle = 'Dashboard Persuratan';
$db = Database::getInstance();

// 1. Real-time Database Counts (As per Section 2 criteria)
$totalSuratMasuk = (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE deleted_at IS NULL")->fetchColumn();
$totalSuratKeluar = (int)$db->query("SELECT COUNT(*) FROM surat_keluar WHERE deleted_at IS NULL")->fetchColumn();
$totalMenungguDisposisi = (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE status = 'diterima' AND deleted_at IS NULL")->fetchColumn();
$totalMenungguPersetujuan = (int)$db->query("SELECT COUNT(*) FROM surat_keluar WHERE status = 'menunggu_persetujuan' AND deleted_at IS NULL")->fetchColumn();
$totalArsip = (int)$db->query("SELECT COUNT(*) FROM arsip WHERE deleted_at IS NULL")->fetchColumn();

// 2. Monthly Trend Data for Chart.js
$currentYear = (int)date('Y');
$agendaRecap = Agenda::getRecapByMonth($currentYear);
$chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$chartDataMasuk = [];
$chartDataKeluar = [];
for ($m = 1; $m <= 12; $m++) {
    $k = str_pad((string)$m, 2, '0', STR_PAD_LEFT);
    $chartDataMasuk[] = $agendaRecap[$k]['masuk'] ?? 0;
    $chartDataKeluar[] = $agendaRecap[$k]['keluar'] ?? 0;
}

// 3. Classification Distribution Data for Doughnut Chart
$stmtKlas = $db->query("
    SELECT COALESCE(k.nama, 'Lainnya') as nama, COUNT(sm.id) as total
    FROM surat_masuk sm
    LEFT JOIN klasifikasi_surat k ON sm.klasifikasi_id = k.id
    WHERE sm.deleted_at IS NULL
    GROUP BY k.id
    LIMIT 5
");
$klasDist = $stmtKlas->fetchAll();
$klasLabels = array_column($klasDist, 'nama');
$klasTotals = array_column($klasDist, 'total');
if (empty($klasLabels)) {
    $klasLabels = ['Umum', 'Kepegawaian', 'Keuangan'];
    $klasTotals = [3, 2, 1];
}

// 4. Recent Letters & Pending Dispositions
$recentSuratMasuk = SuratMasuk::getAll(['limit' => 5]);
$pendingDisposisi = $db->query("
    SELECT sm.*, k.nama as klasifikasi_nama
    FROM surat_masuk sm
    LEFT JOIN klasifikasi_surat k ON sm.klasifikasi_id = k.id
    WHERE sm.status = 'diterima' AND sm.deleted_at IS NULL
    ORDER BY sm.tanggal_terima DESC LIMIT 5
")->fetchAll();

// 5. Recent Surat Keluar Needing Approval
$pendingApproval = $db->query("
    SELECT sk.*, p.nama as pejabat_nama, u.full_name as pembuat_nama
    FROM surat_keluar sk
    LEFT JOIN pejabat p ON sk.pejabat_id = p.id
    LEFT JOIN users u ON sk.created_by = u.id
    WHERE sk.status IN ('draft', 'menunggu_persetujuan') AND sk.deleted_at IS NULL
    ORDER BY sk.created_at DESC LIMIT 5
")->fetchAll();

// 6. Recent Audit Logs
$recentLogs = AuditLog::getRecent(6);

require_once APP_PATH . '/views/layouts/header.php';
?>

<!-- Quick Action Buttons -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold m-0" style="letter-spacing: -0.02em;">Selamat Datang, <?= htmlspecialchars(Auth::user()['full_name']) ?></h3>
        <p class="text-muted small m-0">Ringkasan aktivitas persuratan instansi hari ini, <?= date('d F Y') ?>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Auth::can('surat_masuk.create')): ?>
            <a href="<?= url('surat_masuk/tambah.php') ?>" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-3">
                <i data-lucide="plus-circle" style="width: 18px;"></i>
                <span>Catat Surat Masuk</span>
            </a>
        <?php endif; ?>
        <?php if (Auth::can('surat_keluar.create')): ?>
            <a href="<?= url('generator/') ?>" class="btn btn-outline-primary d-flex align-items-center gap-2 rounded-3 bg-white">
                <i data-lucide="file-text" style="width: 18px;"></i>
                <span>Buat Surat Keluar</span>
            </a>
        <?php endif; ?>
        <a href="<?= url('arsip/') ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2 rounded-3 bg-white">
            <i data-lucide="folder-search" style="width: 18px;"></i>
            <span>Buka Arsip</span>
        </a>
    </div>
</div>

<!-- 4 Statistics Summary Cards (Real-time DB counts with direct click navigation) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= url('surat_masuk/') ?>" class="stat-card">
            <div>
                <div class="stat-val text-primary"><?= $totalSuratMasuk ?></div>
                <p class="stat-label">Total Surat Masuk</p>
                <span class="badge badge-soft-primary mt-2">Arsip & Disposisi</span>
            </div>
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i data-lucide="inbox" style="width: 24px; height: 24px;"></i>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= url('surat_keluar/') ?>" class="stat-card">
            <div>
                <div class="stat-val" style="color: #7C3AED;"><?= $totalSuratKeluar ?></div>
                <p class="stat-label">Total Surat Keluar</p>
                <span class="badge badge-soft-purple mt-2">Diterbitkan & Draf</span>
            </div>
            <div class="stat-icon-wrapper bg-purple-subtle" style="background:#FAF5FF; color:#7C3AED;">
                <i data-lucide="send" style="width: 24px; height: 24px;"></i>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= url('surat_masuk/?status=diterima') ?>" class="stat-card">
            <div>
                <div class="stat-val text-warning"><?= $totalMenungguDisposisi ?></div>
                <p class="stat-label">Menunggu Disposisi</p>
                <span class="badge badge-soft-warning mt-2">Perlu Ditindaklanjuti</span>
            </div>
            <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= url('arsip/') ?>" class="stat-card">
            <div>
                <div class="stat-val text-success"><?= $totalArsip ?></div>
                <p class="stat-label">Total Arsip Digital</p>
                <span class="badge badge-soft-success mt-2">Tersimpan Permanen</span>
            </div>
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i data-lucide="archive" style="width: 24px; height: 24px;"></i>
            </div>
        </a>
    </div>
</div>

<!-- Charts Section: Monthly Trends & Category Breakdown -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold m-0 fs-6">Statistik Tren Persuratan Bulanan (Tahun <?= $currentYear ?>)</h5>
                <span class="badge bg-light text-secondary border">Real-time DB</span>
            </div>
            <div class="card-body">
                <div style="height: 280px; position: relative;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold m-0 fs-6">Distribusi Klasifikasi Surat</h5>
                <i data-lucide="pie-chart" class="text-muted" style="width: 18px;"></i>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div style="height: 220px; position: relative;">
                    <canvas id="klasifikasiChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables: Pending Dispositions & Pending Approvals -->
<div class="row g-4 mb-4">
    <!-- Surat Menunggu Disposisi Pimpinan -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1 rounded bg-warning-subtle text-warning"><i data-lucide="alert-circle" style="width: 18px;"></i></span>
                    <h5 class="card-title fw-bold m-0 fs-6">Surat Masuk Menunggu Disposisi</h5>
                </div>
                <a href="<?= url('surat_masuk/?status=diterima') ?>" class="btn btn-sm btn-light text-primary small">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>No. Surat / Pengirim</th>
                                <th>Perihal</th>
                                <th>Urgensi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingDisposisi)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">Semua surat masuk telah didisposisikan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingDisposisi as $sm): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($sm['nomor_surat']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($sm['pengirim']) ?></div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($sm['perihal']) ?>">
                                                <?= htmlspecialchars($sm['perihal']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                             $urgClass = match($sm['tingkat_urgensi']) {
                                                'penting' => 'bg-warning text-dark',
                                                'segera' => 'bg-danger text-white',
                                                'rahasia' => 'bg-dark text-white',
                                                default => 'bg-secondary-subtle text-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $urgClass ?>"><?= ucfirst($sm['tingkat_urgensi']) ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= url('disposisi/buat.php?surat_id=' . $sm['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
                                                Disposisi
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Surat Keluar Menunggu Persetujuan / Verifikasi -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1 rounded bg-info-subtle text-info"><i data-lucide="check-circle" style="width: 18px;"></i></span>
                    <h5 class="card-title fw-bold m-0 fs-6">Surat Keluar Menunggu Persetujuan</h5>
                </div>
                <a href="<?= url('surat_keluar/') ?>" class="btn btn-sm btn-light text-primary small">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>No. Surat / Tujuan</th>
                                <th>Perihal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingApproval)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">Tidak ada surat keluar yang menunggu persetujuan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingApproval as $sk): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($sk['nomor_surat']) ?></div>
                                            <div class="text-muted small">Tujuan: <?= htmlspecialchars($sk['tujuan']) ?></div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($sk['perihal']) ?>">
                                                <?= htmlspecialchars($sk['perihal']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-white"><?= ucfirst(str_replace('_', ' ', $sk['status'])) ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= url('surat_keluar/preview.php?id=' . $sk['id']) ?>" class="btn btn-sm btn-outline-info py-1 px-2">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent System Audit Trail -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold m-0 fs-6">Aktivitas Persuratan Terkini (Audit Trail)</h5>
        <?php if (Auth::hasRole('superadmin')): ?>
            <a href="<?= url('audit_log.php') ?>" class="btn btn-sm btn-light small">Lihat Log Lengkap</a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Keterangan Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td class="text-muted small"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="fw-semibold text-dark small"><?= htmlspecialchars($log['full_name'] ?? 'System') ?></td>
                            <td>
                                <?php
                                $actBadge = match($log['action']) {
                                    'CREATE' => 'badge-soft-success',
                                    'UPDATE' => 'badge-soft-warning',
                                    'DELETE' => 'badge-soft-danger',
                                    'APPROVE' => 'badge-soft-purple',
                                    'LOGIN' => 'badge-soft-primary',
                                    'BACKUP', 'RESTORE' => 'badge-soft-info',
                                    default => 'badge-soft-primary'
                                };
                                ?>
                                <span class="badge <?= $actBadge ?>"><?= htmlspecialchars($log['action']) ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($log['module']) ?></span></td>
                            <td class="small text-secondary"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Monthly Trend Bar Chart
    const ctxMonthly = document.getElementById('monthlyTrendChart');
    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Surat Masuk',
                        data: <?= json_encode($chartDataMasuk) ?>,
                        backgroundColor: '#2563EB',
                        borderRadius: 6
                    },
                    {
                        label: 'Surat Keluar',
                        data: <?= json_encode($chartDataKeluar) ?>,
                        backgroundColor: '#7C3AED',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // 2. Classification Doughnut Chart
    const ctxKlas = document.getElementById('klasifikasiChart');
    if (ctxKlas) {
        new Chart(ctxKlas, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($klasLabels) ?>,
                datasets: [{
                    data: <?= json_encode($klasTotals) ?>,
                    backgroundColor: ['#2563EB', '#7C3AED', '#0D9488', '#F59E0B', '#EF4444'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
