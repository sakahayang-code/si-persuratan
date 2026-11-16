<?php
// persuratan/public/profil.php

require_once __DIR__ . '/../config/app.php';

if (!Auth::check()) {
    redirect('login.php');
}

$pageTitle = 'Profil Pengguna';
$user = Auth::user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token tidak valid.';
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($fullName) || empty($email)) {
        $errors[] = 'Nama lengkap dan email tidak boleh kosong.';
    }

    $updateData = [
        'username' => $user['username'],
        'email' => $email,
        'full_name' => $fullName,
        'nip' => trim($_POST['nip'] ?? ''),
        'jabatan' => trim($_POST['jabatan'] ?? ''),
        'phone' => $phone,
        'role_id' => $user['role_id'],
        'is_active' => $user['is_active']
    ];

    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } else {
            $updateData['password'] = $_POST['new_password'];
        }
    }

    if (empty($errors)) {
        User::update($user['id'], $updateData);
        Flash::set('success', 'Profil Anda berhasil diperbarui.');
        redirect('profil.php');
    }
}

require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold m-0">Profil Pengguna</h4>
        <p class="text-muted small m-0">Kelola identitas akun dan kata sandi Anda.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 text-center p-4">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
            <div class="text-muted small mb-2"><?= htmlspecialchars($user['jabatan'] ?? 'Staf Instansi') ?></div>
            <span class="badge bg-primary-subtle text-primary mb-3"><?= htmlspecialchars($user['role_name']) ?></span>
            <div class="border-top pt-3 text-start small text-secondary">
                <div class="mb-1"><strong>Username:</strong> @<?= htmlspecialchars($user['username']) ?></div>
                <div class="mb-1"><strong>NIP:</strong> <?= htmlspecialchars($user['nip'] ?? '-') ?></div>
                <div class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= url('profil.php') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nama Lengkap & Gelar</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">NIP</label>
                            <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($user['nip'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($user['jabatan'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3">Ganti Kata Sandi (Kosongkan jika tidak diubah)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
