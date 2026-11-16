<?php
// persuratan/public/switch_role.php

require_once __DIR__ . '/../config/app.php';

$roleSlug = $_GET['role'] ?? 'superadmin';

$db = Database::getInstance();
$stmt = $db->prepare("
    SELECT u.*, r.slug as role_slug, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE r.slug = :slug AND u.is_active = 1 AND u.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([':slug' => $roleSlug]);
$user = $stmt->fetch();

if ($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role_slug'];
    $_SESSION['user_name'] = $user['full_name'];

    AuditLog::record('ROLE_SWITCH', 'auth', $user['id'], "Beralih peran demo ke: {$user['role_name']}");
    Flash::set('success', "Beralih ke akun peran: {$user['role_name']} ({$user['full_name']})");
} else {
    Flash::set('danger', "Peran {$roleSlug} tidak ditemukan.");
}

$redirect = $_SERVER['HTTP_REFERER'] ?? url('dashboard.php');
header("Location: {$redirect}");
exit;
