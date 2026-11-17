<?php
// persuratan/public/generator/buat.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || (!Auth::can('template.manage') && !Auth::can('surat_keluar.create'))) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$templateId = (int)($_GET['id'] ?? 1);
$template = TemplateSurat::findById($templateId);

if (!$template) {
    Flash::set('danger', 'Template tidak ditemukan.');
    redirect('generator/');
}

$pageTitle = 'Buat ' . $template['nama_template'];
$pejabatList = Pejabat::findActive();
$klasifikasiList = Klasifikasi::getAll();
$autoAgenda = Numbering::generateAgendaNumber('keluar');

// Auto number generation based on template pattern
$sampleNomor = Numbering::generateNextNumber('005', $template['format_nomor']);

$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    }

    $tujuan = trim($_POST['tujuan'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $pejabatId = (int)($_POST['pejabat_id'] ?? 1);
    $tglSurat = $_POST['tanggal_surat'] ?? date('Y-m-d');
    $nomorSurat = trim($_POST['nomor_surat'] ?? $sampleNomor);

    if (empty($tujuan) || empty($perihal)) {
        $errors[] = 'Tujuan dan perihal wajib diisi.';
    }

    if (empty($errors)) {
        try {
            // Collect dynamic template fields
            $templateData = $_POST['fields'] ?? [];

            // Replace fields into template content
            $contentHtml = $template['konten_template'];
            foreach ($templateData as $k => $v) {
                $contentHtml = str_replace('{' . $k . '}', htmlspecialchars($v), $contentHtml);
            }

            $suratId = SuratKeluar::create([
                'nomor_agenda' => !empty($_POST['nomor_agenda']) ? $_POST['nomor_agenda'] : $autoAgenda,
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'perihal' => $perihal,
                'isi_ringkas' => $contentHtml,
                'template_id' => $templateId,
                'template_data' => $templateData,
                'klasifikasi_id' => !empty($_POST['klasifikasi_id']) ? (int)$_POST['klasifikasi_id'] : 1,
                'pejabat_id' => $pejabatId,
                'status' => 'draft'
            ]);

            Flash::set('success', "Surat berhasil digenerate berdasarkan template: {$template['nama_template']}");
            redirect('surat_keluar/preview.php?id=' . $suratId);
        } catch (Throwable $e) {
            $errors[] = 'Gagal membuat dokumen: ' . $e->getMessage();
        }
    }
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('generator/') ?>">Generator</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($template['nama_template']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold m-0" style="letter-spacing: -0.02em;">Generator: <?= htmlspecialchars($template['nama_template']) ?></h4>
    </div>
    <a href="<?= url('generator/') ?>" class="btn btn-outline-secondary btn-sm bg-white">Kembali ke Template</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="" method="POST" id="formGenerator">
    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

    <div class="row g-4">
        <!-- Input Form Parameters -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold m-0">1. Data Pokok Surat Resmi</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nomor Surat Otomatis</label>
                            <input type="text" name="nomor_surat" class="form-control font-monospace" value="<?= htmlspecialchars($sampleNomor) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Tujuan Surat / Kepada Yth. <span class="text-danger">*</span></label>
                            <textarea name="tujuan" class="form-control" rows="2" placeholder="Contoh: Kepala Dinas Perpustakaan dan Kearsipan Daerah&#10;di - Jakarta" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Perihal Surat <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" class="form-control" placeholder="Perihal surat..." value="<?= htmlspecialchars($template['nama_template']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Pejabat Penandatangan</label>
                            <select name="pejabat_id" class="form-select">
                                <?php foreach ($pejabatList as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama'] . ' - ' . $p['jabatan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Klasifikasi Surat</label>
                            <select name="klasifikasi_id" class="form-select">
                                <?php foreach ($klasifikasiList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Template Fields Box -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold m-0">2. Isian Variabel Konten Template</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php if ($template['jenis_surat'] === 'undangan'): ?>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Hari / Tanggal Acara</label>
                                <input type="text" name="fields[hari_tanggal]" class="form-control" placeholder="Contoh: Kamis, 26 November 2026">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Waktu / Jam</label>
                                <input type="text" name="fields[waktu]" class="form-control" placeholder="Contoh: 09.00 - 12.00">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Tempat / Ruang</label>
                                <input type="text" name="fields[tempat]" class="form-control" placeholder="Contoh: Ruang Rapat Lantai 3 Gedung BPTI">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Agenda / Acara</label>
                                <input type="text" name="fields[agenda_rapat]" class="form-control" placeholder="Contoh: Pembahasan SPBE dan Digitalisasi Persuratan">
                            </div>
                        <?php elseif ($template['jenis_surat'] === 'surat_tugas'): ?>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Nama Petugas yang Ditugaskan</label>
                                <input type="text" name="fields[nama_petugas]" class="form-control" placeholder="Contoh: Rian Pratama, S.T">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">NIP / Jabatan Petugas</label>
                                <input type="text" name="fields[nip_petugas]" class="form-control" placeholder="Contoh: 199504182019031006 / Pranata Komputer">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Uraian Tugas / Perintah</label>
                                <textarea name="fields[uraian_tugas]" class="form-control" rows="2" placeholder="Uraikan tugas yang diperintahkan..."></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Waktu Pelaksanaan</label>
                                <input type="text" name="fields[waktu_pelaksanaan]" class="form-control" placeholder="Contoh: 20 s.d 22 November 2026">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Lokasi Tujuan</label>
                                <input type="text" name="fields[lokasi_tugas]" class="form-control" placeholder="Contoh: Kantor Balai Kota Provinsi DKI Jakarta">
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Konten / Keterangan Surat</label>
                                <textarea name="fields[isi_surat_bebas]" class="form-control" rows="6" placeholder="Tuliskan uraian isi surat di sini..."></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="<?= url('generator/') ?>" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                    <i data-lucide="sparkles" style="width: 18px;"></i>
                    <span>Generate & Buka Surat Keluar</span>
                </button>
            </div>
        </div>

        <!-- Template Structure Information Card -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold m-0">Format Kop & Standar Instansi</h6>
                </div>
                <div class="card-body p-4 small text-secondary">
                    <p class="mb-2">Aplikasi secara otomatis menyisipkan komponen resmi berikut pada dokumen:</p>
                    <ul class="mb-3 ps-3">
                        <li><strong>Kop Surat Resmi</strong>: Logo instansi, nama badan induk, alamat, kontak, dan garis tebal pemisah ganda.</li>
                        <li><strong>Metadata Surat</strong>: Nomor resmi otomatis, tanggal, sifat kerahasiaan, lampiran, dan perihal.</li>
                        <li><strong>Blok Penandatangan</strong>: Jabatan pejabat, nama lengkap dengan gelar, NIP, serta gambar tanda tangan digital.</li>
                        <li><strong>QR Code Keaslian</strong>: Terintegrasi langsung dengan endpoint publik <code>/verifikasi.php</code>.</li>
                    </ul>
                    <div class="p-3 bg-light rounded border text-muted">
                        Template ini memenuhi standar tata naskah dinas kearsipan dan dapat dicetak langsung atau diunduh dalam format PDF siap distribusi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
