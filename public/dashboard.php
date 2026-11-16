<?php
// persuratan/public/dashboard.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check()) {
    redirect('login.php');
}

$pageTitle = 'Dashboard Persuratan';
$db = Database::getInstance();
$currentUser = Auth::user();

// 1. Real-time Database Counts
$totalSuratMasuk = (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE deleted_at IS NULL")->fetchColumn();
$totalSuratKeluar = (int)$db->query("SELECT COUNT(*) FROM surat_keluar WHERE deleted_at IS NULL")->fetchColumn();
$totalMenungguDisposisi = (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE status = 'diterima' AND deleted_at IS NULL")->fetchColumn();
$totalArsip = (int)$db->query("SELECT COUNT(*) FROM arsip WHERE deleted_at IS NULL")->fetchColumn();

// 2. Monthly Trend Data for Chart.js
$currentYear = (int)date('Y');
$agendaRecap = Agenda::getRecapByMonth($currentYear);
$chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$chartDataMasuk = [];
$chartDataKeluar = [];
for ($m = 1; $m <= 12; $m++) {
    $k = str_pad((string)$m, 2, '0', STR_PAD_LEFT);
    $chartDataMasuk[] = (int)($agendaRecap[$k]['masuk'] ?? 0);
    $chartDataKeluar[] = (int)($agendaRecap[$k]['keluar'] ?? 0);
}

// 3. Classification Distribution Data
$stmtKlas = $db->query("
    SELECT COALESCE(k.nama, 'Umum') as nama, COUNT(sm.id) as total
    FROM surat_masuk sm
    LEFT JOIN klasifikasi_surat k ON sm.klasifikasi_id = k.id
    WHERE sm.deleted_at IS NULL
    GROUP BY k.id
    LIMIT 5
");
$klasDist = $stmtKlas->fetchAll();
$klasLabels = !empty($klasDist) ? array_column($klasDist, 'nama') : ['Umum', 'Kepegawaian', 'Keuangan'];
$klasTotals = !empty($klasDist) ? array_column($klasDist, 'total') : [3, 2, 1];

// 4. Pending Tasks for Actionable List
$pendingList = $db->query("
    SELECT sm.id, sm.nomor_surat, sm.perihal, sm.pengirim, sm.tanggal_surat, sm.status, 'masuk' as tipe
    FROM surat_masuk sm
    WHERE sm.status = 'diterima' AND sm.deleted_at IS NULL
    UNION ALL
    SELECT sk.id, sk.nomor_surat, sk.perihal, sk.tujuan as pengirim, sk.tanggal_surat, sk.status, 'keluar' as tipe
    FROM surat_keluar sk
    WHERE sk.status IN ('draft', 'menunggu_persetujuan') AND sk.deleted_at IS NULL
    ORDER BY tanggal_surat DESC LIMIT 5
")->fetchAll();

// 5. Recent Audit Logs
$recentLogs = AuditLog::getRecent(6);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="space-y-6">
    <!-- Welcome & Quick Actions Card -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold mb-2 border border-blue-200/50">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Ringkasan Aktivitas Real-Time</span>
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-bold text-slate-900">
                Selamat Datang, <?= htmlspecialchars($currentUser['full_name']) ?>!
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pantau alur surat masuk, disposisi berjenjang, penerbitan surat keluar, dan arsip digital instansi.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <?php if (Auth::can('surat_masuk.create')): ?>
                <a href="<?= url('surat_masuk/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-600/20 transition text-decoration-none">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Surat Masuk</span>
                </a>
            <?php endif; ?>
            <?php if (Auth::can('surat_keluar.create')): ?>
                <a href="<?= url('generator/') ?>" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-teal-600/20 transition text-decoration-none">
                    <i data-lucide="file-plus-2" class="w-4 h-4"></i>
                    <span>Buat Surat Keluar</span>
                </a>
            <?php endif; ?>
            <a href="<?= url('agenda/') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl flex items-center gap-2 transition text-decoration-none">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Buku Agenda</span>
            </a>
        </div>
    </div>

    <!-- 4 Interactive KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1: Surat Masuk -->
        <a href="<?= url('surat_masuk/') ?>" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-blue-400 shadow-xs hover:shadow-md transition-all text-decoration-none block">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider m-0">Surat Masuk</p>
                    <h3 class="font-display text-3xl font-extrabold text-slate-900 mt-1.5 m-0"><?= $totalSuratMasuk ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="inbox" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Terdata di Database
                </span>
                <span class="text-blue-600 font-medium group-hover:translate-x-0.5 transition-transform">Lihat daftar →</span>
            </div>
        </a>

        <!-- Card 2: Surat Keluar -->
        <a href="<?= url('surat_keluar/') ?>" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-purple-400 shadow-xs hover:shadow-md transition-all text-decoration-none block">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider m-0">Surat Keluar</p>
                    <h3 class="font-display text-3xl font-extrabold text-slate-900 mt-1.5 m-0"><?= $totalSuratKeluar ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="text-purple-600 font-semibold">Penomoran Otomatis</span>
                <span class="text-purple-600 font-medium group-hover:translate-x-0.5 transition-transform">Lihat daftar →</span>
            </div>
        </a>

        <!-- Card 3: Menunggu Proses / Disposisi -->
        <a href="<?= url('disposisi/') ?>" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-amber-400 shadow-xs hover:shadow-md transition-all text-decoration-none block">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider m-0">Menunggu Proses</p>
                    <h3 class="font-display text-3xl font-extrabold text-slate-900 mt-1.5 m-0"><?= $totalMenungguDisposisi ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="clock-alert" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="text-amber-700 font-semibold">Disposisi & Review</span>
                <span class="text-amber-700 font-medium group-hover:translate-x-0.5 transition-transform">Tindak lanjuti →</span>
            </div>
        </a>

        <!-- Card 4: Arsip Digital -->
        <a href="<?= url('arsip/') ?>" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-teal-400 shadow-xs hover:shadow-md transition-all text-decoration-none block">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider m-0">Arsip Digital</p>
                    <h3 class="font-display text-3xl font-extrabold text-slate-900 mt-1.5 m-0"><?= $totalArsip ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white flex items-center justify-center transition-colors">
                    <i data-lucide="archive" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="text-teal-700 font-semibold">Terindeks & Terproteksi</span>
                <span class="text-teal-700 font-medium group-hover:translate-x-0.5 transition-transform">Telusuri arsip →</span>
            </div>
        </a>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Trend Line Chart -->
        <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-display font-bold text-slate-900 text-base m-0">Grafik Volume Surat Masuk & Keluar</h3>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Statistik per bulan tahun berjalan (<?= $currentYear ?>)</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">Tahun <?= $currentYear ?></span>
            </div>
            <div class="h-64 relative">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Category Doughnut Chart -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col">
            <div class="mb-4">
                <h3 class="font-display font-bold text-slate-900 text-base m-0">Klasifikasi & Jenis Surat</h3>
                <p class="text-xs text-slate-500 m-0 mt-0.5">Distribusi kategori dokumen</p>
            </div>
            <div class="flex-1 flex items-center justify-center min-h-[220px] relative">
                <canvas id="categoryDistChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Actionable Queues & Audit Log Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Surat Menunggu Disposisi & Tindak Lanjut -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="list-todo" class="w-4 h-4 text-orange-500"></i>
                    <h3 class="font-display font-bold text-slate-900 text-sm sm:text-base m-0">Surat Menunggu Disposisi & Persetujuan</h3>
                </div>
                <a href="<?= url('disposisi/') ?>" class="text-xs font-semibold text-blue-600 hover:underline text-decoration-none">Semua Antrean</a>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($pendingList)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-500 mx-auto mb-2"></i>
                        Semua surat dan disposisi telah diproses dengan lengkap!
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingList as $item): ?>
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/75 transition">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-800"><?= htmlspecialchars($item['nomor_surat'] ?: 'DRAFT') ?></span>
                                    <?php if ($item['tipe'] === 'masuk'): ?>
                                        <span class="text-[10px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">Surat Masuk</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-semibold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200">Surat Keluar</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-600 font-medium m-0"><?= htmlspecialchars($item['perihal']) ?></p>
                                <p class="text-[11px] text-slate-400 m-0">Dari / Kepada: <?= htmlspecialchars($item['pengirim']) ?> • <?= date('d M Y', strtotime($item['tanggal_surat'])) ?></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <?php if ($item['tipe'] === 'masuk'): ?>
                                    <a href="<?= url('surat_masuk/detail.php?id=' . $item['id']) ?>" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition text-decoration-none">
                                        Disposisi
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('surat_keluar/detail.php?id=' . $item['id']) ?>" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg transition text-decoration-none">
                                        Periksa
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Audit Trail Aktivitas -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-purple-600"></i>
                    <h3 class="font-display font-bold text-slate-900 text-sm sm:text-base m-0">Audit Trail Aktivitas</h3>
                </div>
                <a href="<?= url('audit_log.php') ?>" class="text-[11px] text-blue-600 hover:underline text-decoration-none">Lihat Semua</a>
            </div>
            <div class="p-4 space-y-3.5 max-h-80 overflow-y-auto">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada aktivitas tercatat.</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="flex items-start gap-2.5 text-xs">
                            <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                            <div class="flex-1 space-y-0.5">
                                <p class="text-slate-700 font-medium m-0"><?= htmlspecialchars($log['action'] ?? $log['aktivitas'] ?? 'Aktivitas sistem') ?></p>
                                <p class="text-[10px] text-slate-400 m-0">
                                    Oleh: <strong class="text-slate-600"><?= htmlspecialchars($log['user_name'] ?? 'Sistem') ?></strong> • <?= date('d M, H:i', strtotime($log['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Monthly Line Chart
    const ctxMonthly = document.getElementById('monthlyTrendChart');
    if (ctxMonthly && typeof Chart !== 'undefined') {
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Surat Masuk',
                        data: <?= json_encode($chartDataMasuk) ?>,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3
                    },
                    {
                        label: 'Surat Keluar',
                        data: <?= json_encode($chartDataKeluar) ?>,
                        borderColor: '#7C3AED',
                        backgroundColor: 'rgba(124, 58, 237, 0.08)',
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { family: 'Inter', size: 11 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });
    }

    // 2. Category Doughnut Chart
    const ctxCat = document.getElementById('categoryDistChart');
    if (ctxCat && typeof Chart !== 'undefined') {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($klasLabels) ?>,
                datasets: [{
                    data: <?= json_encode($klasTotals) ?>,
                    backgroundColor: ['#2563EB', '#7C3AED', '#0D9488', '#F59E0B', '#64748B'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { family: 'Inter', size: 10 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
