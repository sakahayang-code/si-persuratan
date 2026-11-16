<?php
// persuratan/public/pengaturan.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check() || !Auth::hasRole('superadmin')) {
    Flash::set('danger', 'Hanya Super Administrator yang berwenang mengubah pengaturan sistem.');
    redirect('dashboard.php');
}

$pageTitle = 'Pengaturan Identitas & Sistem';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('danger', 'Token CSRF tidak valid.');
    } else {
        $keys = [
            'nama_instansi', 'sub_instansi', 'alamat_instansi',
            'telepon_instansi', 'email_instansi', 'website_instansi',
            'kop_garis_tebal', 'auto_backup_interval', 'retensi_default_tahun'
        ];

        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                Pengaturan::set($k, trim($_POST[$k]));
            }
        }

        Flash::set('success', 'Konfigurasi identitas instansi dan sistem berhasil diperbarui.');
    }
    redirect('pengaturan.php');
}

$pengaturan = Pengaturan::getAll();
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0">Pengaturan Identitas & Sistem</h4>
        <p class="text-muted small m-0">Sesuaikan profil lembaga, alamat resmi, kop surat, dan kebijakan cadangan data.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= url('pengaturan.php') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

                    <h6 class="fw-bold text-primary mb-3">1. Identitas Resmi Instansi (Kop Surat)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Nama Resmi Instansi / Lembaga <span class="text-danger">*</span></label>
                            <input type="text" name="nama_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['nama_instansi'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Organisasi Induk / Sub-Instansi</label>
                            <input type="text" name="sub_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['sub_instansi'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Alamat Lengkap</label>
                            <textarea name="alamat_instansi" class="form-control" rows="2"><?= htmlspecialchars($pengaturan['alamat_instansi'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Nomor Telepon</label>
                            <input type="text" name="telepon_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['telepon_instansi'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Email Resmi</label>
                            <input type="email" name="email_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['email_instansi'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Website</label>
                            <input type="text" name="website_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['website_instansi'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3">2. Preferensi Kearsipan & Backup</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Masa Retensi Arsip Default (Tahun)</label>
                            <input type="number" name="retensi_default_tahun" class="form-control" value="<?= htmlspecialchars($pengaturan['retensi_default_tahun'] ?? '5') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Interval Backup Otomatis</label>
                            <select name="auto_backup_interval" class="form-select">
                                <option value="daily" <?= ($pengaturan['auto_backup_interval'] ?? '') === 'daily' ? 'selected' : '' ?>>Harian (Daily)</option>
                                <option value="weekly" <?= ($pengaturan['auto_backup_interval'] ?? '') === 'weekly' ? 'selected' : '' ?>>Mingguan (Weekly)</option>
                                <option value="monthly" <?= ($pengaturan['auto_backup_interval'] ?? '') === 'monthly' ? 'selected' : '' ?>>Bulanan (Monthly)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold m-0">Pratinjau Kop Surat Resmi</h6>
            </div>
            <div class="card-body p-4 text-center">
                <img src="<?= app_logo_data_uri() ?>" width="56" height="56" class="mb-2">
                <div class="fw-bold text-dark" style="font-size:12px;"><?= htmlspecialchars($pengaturan['sub_instansi'] ?? '') ?></div>
                <div class="fw-bold text-primary" style="font-size:14px; text-transform:uppercase;"><?= htmlspecialchars($pengaturan['nama_instansi'] ?? '') ?></div>
                <div class="text-muted" style="font-size:10px; margin-top:4px;"><?= htmlspecialchars($pengaturan['alamat_instansi'] ?? '') ?></div>
                <div class="border-top border-dark border-2 my-2"></div>
                <div class="border-top border-dark border-1 mb-3"></div>
                <span class="badge badge-soft-success">Format Kop Baku Tersimpan</span>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
