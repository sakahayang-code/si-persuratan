<?php
// persuratan/public/users/index.php

require_once __DIR__ . '/../../config/app.php';

if (!Auth::check() || !Auth::can('users.manage')) {
    Flash::set('danger', 'Akses ditolak.');
    redirect('dashboard.php');
}

$pageTitle = 'Manajemen Pengguna & Peran (RBAC)';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('danger', 'Token CSRF tidak valid.');
    } else {
        $act = $_POST['action'] ?? 'create';
        if ($act === 'create') {
            User::create($_POST);
            Flash::set('success', 'Akun pengguna baru berhasil ditambahkan.');
        } elseif ($act === 'delete') {
            User::delete((int)$_POST['id']);
            Flash::set('info', 'Pengguna telah dinonaktifkan (soft delete).');
        }
    }
    redirect('users/');
}

$users = User::getAll();
$roles = User::getRoles();
require_once APP_PATH . '/views/layouts/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0">Manajemen Pengguna & Hak Akses (RBAC)</h4>
        <p class="text-muted small m-0">Atur pengguna multi-level: Super Admin, Admin Surat, Pimpinan, Sekretaris, Petugas, dan Pegawai.</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
        <i data-lucide="user-plus" style="width: 16px;"></i>
        <span>Tambah Pengguna</span>
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Nama Pengguna & Akun</th>
                        <th>Peran (Role)</th>
                        <th>NIP & Jabatan</th>
                        <th>Kontak Email / Telp</th>
                        <th>Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div class="text-muted font-monospace small">@<?= htmlspecialchars($u['username']) ?></div>
                            </td>
                            <td>
                                <?php
                                $roleBadge = match($u['role_slug']) {
                                    'superadmin' => 'bg-danger text-white',
                                    'pimpinan' => 'bg-primary text-white',
                                    'admin_persuratan' => 'bg-purple-subtle text-purple',
                                    'sekretaris' => 'bg-info-subtle text-info',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $roleBadge ?>"><?= htmlspecialchars($u['role_name']) ?></span>
                            </td>
                            <td>
                                <div class="text-dark small fw-medium"><?= htmlspecialchars($u['jabatan'] ?? '-') ?></div>
                                <div class="text-muted font-monospace" style="font-size:11px;">NIP: <?= htmlspecialchars($u['nip'] ?? '-') ?></div>
                            </td>
                            <td>
                                <div class="small text-secondary"><?= htmlspecialchars($u['email']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($u['phone'] ?? '-') ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $u['is_active'] ? 'badge-soft-success' : 'badge-soft-danger' ?>">
                                    <?= $u['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($u['id'] !== Auth::id()): ?>
                                    <form action="<?= url('users/') ?>" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan akun pengguna ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light text-danger border p-1" title="Nonaktifkan">
                                            <i data-lucide="user-x" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('users/') ?>" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Akun Pengguna Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Password Awal <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Peran (Role) <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">NIP</label>
                        <input type="text" name="nip" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Buat Pengguna</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
