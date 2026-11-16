<?php
// persuratan/public/agenda/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('agenda.view')) {
    Flash::set('danger', 'Akses ditolak.');
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Buku Agenda Surat';
$jenis = $_GET['jenis'] ?? 'masuk'; // 'masuk' or 'keluar'

$filters = [
    'jenis' => $jenis,
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$agendaList = Agenda::getAll($filters);

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Buku Agenda Persuratan</h4>
        <p class="text-muted small m-0">Catatan kronologis resmi penomoran agenda surat masuk dan keluar.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/agenda/export.php?jenis=<?= $jenis ?>&format=excel&search=<?= urlencode($filters['search']) ?>&date_from=<?= $filters['date_from'] ?>&date_to=<?= $filters['date_to'] ?>" class="btn btn-outline-success d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="sheet" style="width: 16px;"></i>
            <span>Ekspor Excel</span>
        </a>
        <a href="/agenda/export.php?jenis=<?= $jenis ?>&format=pdf&search=<?= urlencode($filters['search']) ?>&date_from=<?= $filters['date_from'] ?>&date_to=<?= $filters['date_to'] ?>" target="_blank" class="btn btn-outline-danger d-flex align-items-center gap-2 bg-white rounded-3 shadow-sm">
            <i data-lucide="file-down" style="width: 16px;"></i>
            <span>Cetak / PDF</span>
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm border" style="width: fit-content;">
    <li class="nav-item">
        <a class="nav-link fw-semibold px-4 <?= $jenis === 'masuk' ? 'active' : '' ?>" href="/agenda/?jenis=masuk">
            📥 Agenda Surat Masuk
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-semibold px-4 <?= $jenis === 'keluar' ? 'active' : '' ?>" href="/agenda/?jenis=keluar">
            📤 Agenda Surat Keluar
        </a>
    </li>
</ul>

<!-- Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="/agenda/" method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis) ?>">

            <div class="col-12 col-md-5">
                <label class="form-label small text-secondary fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i data-lucide="search" style="width:14px;"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="No. agenda, nomor surat, perihal, pihak..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary fw-semibold">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary fw-semibold">Tanggal Sampai</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i data-lucide="filter" style="width:16px;"></i></button>
                <a href="/agenda/?jenis=<?= $jenis ?>" class="btn btn-light border"><i data-lucide="rotate-ccw" style="width:16px;"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Catatan Agenda <?= $jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar' ?> (<?= count($agendaList) ?>)</h6>
        <span class="badge bg-light text-secondary border font-monospace">STANDAR KEARSIPAN</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="120">No. Agenda</th>
                        <th width="120">Tgl Agenda</th>
                        <th>Nomor & Tanggal Surat</th>
                        <th><?= $jenis === 'masuk' ? 'Pengirim & Instansi' : 'Tujuan Surat' ?></th>
                        <th>Perihal Dokumen</th>
                        <th>Keterangan</th>
                        <th width="80" class="text-center">Tautan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agendaList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i data-lucide="book-open" style="width:40px; height:40px;" class="mb-2 text-secondary opacity-50"></i>
                                <div>Tidak ada catatan agenda yang ditemukan.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agendaList as $ag): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= htmlspecialchars($ag['nomor_agenda']) ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?= date('d/m/Y', strtotime($ag['tanggal_agenda'])) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($ag['nomor_surat']) ?></div>
                                    <div class="text-muted small">Tgl: <?= date('d/m/Y', strtotime($ag['tanggal_surat'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($ag['pengirim_penerima']) ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($ag['perihal']) ?>">
                                        <?= htmlspecialchars($ag['perihal']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($ag['keterangan'] ?? '-') ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($jenis === 'masuk'): ?>
                                        <a href="/surat_masuk/detail.php?id=<?= $ag['referensi_id'] ?>" class="btn btn-sm btn-light border p-1" title="Lihat Surat Masuk">
                                            <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/surat_keluar/preview.php?id=<?= $ag['referensi_id'] ?>" class="btn btn-sm btn-light border p-1" title="Lihat Surat Keluar">
                                            <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
