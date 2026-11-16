<?php
// persuratan/public/master/penomoran.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('master.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Aturan Penomoran Surat';
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('danger', 'Token CSRF tidak valid.');
    } else {
        $id = (int)$_POST['id'];
        $format = trim($_POST['format_pola']);
        $stmt = $db->prepare("UPDATE nomor_surat SET format_pola = :fmt, updated_at = datetime('now', 'localtime') WHERE id = :id");
        $stmt->execute([':fmt' => $format, ':id' => $id]);
        Flash::set('success', 'Aturan pola penomoran berhasil diperbarui.');
    }
    redirect('master/penomoran.php');
}

$rules = $db->query("SELECT * FROM nomor_surat ORDER BY id ASC")->fetchAll();
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0">Aturan & Counter Penomoran Surat</h4>
        <p class="text-muted small m-0">Sesuaikan pola penomoran dinas, kode romawi bulan, dan urutan counter otomatis.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Kode Klasifikasi</th>
                        <th>Format Pola Penomoran</th>
                        <th>Counter Terakhir</th>
                        <th>Nomor Terakhir Diterbitkan</th>
                        <th>Periode Reset</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $r): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary font-monospace"><?= htmlspecialchars($r['kode_klasifikasi'] ?? 'Semua (Default)') ?></span>
                            </td>
                            <td>
                                <code class="fw-bold text-dark fs-6"><?= htmlspecialchars($r['format_pola']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= $r['counter'] ?></span>
                            </td>
                            <td>
                                <span class="text-secondary small font-monospace"><?= htmlspecialchars($r['last_number'] ?? '-') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= ucfirst($r['reset_period']) ?></span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light border p-1" onclick="editFormat(<?= $r['id'] ?>, '<?= htmlspecialchars($r['format_pola']) ?>')">
                                    <i data-lucide="pencil" style="width: 14px;"></i> Edit Pola
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold m-0">Panduan Tag Placeholder Penomoran:</h6>
    </div>
    <div class="card-body small text-secondary">
        <ul class="mb-0 ps-3">
            <li><code>{nomor}</code> : Nomor urut 3 digit (contoh: 001, 002, 012)</li>
            <li><code>{kode}</code> : Kode klasifikasi surat (contoh: 005, 820)</li>
            <li><code>{bulan_romawi}</code> : Bulan terbit dalam angka Romawi (contoh: I, II, ..., XI, XII)</li>
            <li><code>{bulan}</code> : Bulan terbit 2 digit angka (contoh: 01, 11)</li>
            <li><code>{tahun}</code> : Tahun terbit 4 digit (contoh: 2026)</li>
        </ul>
    </div>
</div>

<!-- Modal Edit Pola -->
<div class="modal fade" id="modalEditFormat" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('master/penomoran.php') ?>" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="id" id="editRuleId">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Ubah Format Pola Penomoran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-bold">Format Pola Baru</label>
                <input type="text" name="format_pola" id="editFormatInput" class="form-control font-monospace" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Pola</button>
            </div>
        </form>
    </div>
</div>

<script>
function editFormat(id, format) {
    document.getElementById('editRuleId').value = id;
    document.getElementById('editFormatInput').value = format;
    const modal = new bootstrap.Modal(document.getElementById('modalEditFormat'));
    modal.show();
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
