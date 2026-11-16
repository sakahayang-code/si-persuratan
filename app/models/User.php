<?php
// persuratan/app/models/User.php

class User {
    public static function getAll(bool $includeDeleted = false): array {
        $db = Database::getInstance();
        $sql = "
            SELECT u.*, r.name as role_name, r.slug as role_slug 
            FROM users u
            JOIN roles r ON u.role_id = r.id
        ";
        if (!$includeDeleted) {
            $sql .= " WHERE u.deleted_at IS NULL ";
        }
        $sql .= " ORDER BY u.role_id ASC, u.full_name ASC";
        return $db->query($sql)->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, nip, jabatan, phone, role_id, is_active, created_at)
            VALUES (:username, :email, :password_hash, :full_name, :nip, :jabatan, :phone, :role_id, :is_active, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':username' => trim($data['username']),
            ':email' => trim($data['email']),
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':full_name' => trim($data['full_name']),
            ':nip' => trim($data['nip'] ?? ''),
            ':jabatan' => trim($data['jabatan'] ?? ''),
            ':phone' => trim($data['phone'] ?? ''),
            ':role_id' => (int)$data['role_id'],
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        $id = (int)$db->lastInsertId();
        AuditLog::record('CREATE', 'users', $id, "Pengguna baru dibuat: {$data['username']}");
        return $id;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $params = [
            ':id' => $id,
            ':username' => trim($data['username']),
            ':email' => trim($data['email']),
            ':full_name' => trim($data['full_name']),
            ':nip' => trim($data['nip'] ?? ''),
            ':jabatan' => trim($data['jabatan'] ?? ''),
            ':phone' => trim($data['phone'] ?? ''),
            ':role_id' => (int)$data['role_id'],
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ];

        $sql = "
            UPDATE users SET 
                username = :username,
                email = :email,
                full_name = :full_name,
                nip = :nip,
                jabatan = :jabatan,
                phone = :phone,
                role_id = :role_id,
                is_active = :is_active,
                updated_at = datetime('now', 'localtime')
        ";

        if (!empty($data['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);

        AuditLog::record('UPDATE', 'users', $id, "Data pengguna ID {$id} diperbarui");
        return $ok;
    }

    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE users SET deleted_at = datetime('now', 'localtime'), is_active = 0 WHERE id = :id
        ");
        $ok = $stmt->execute([':id' => $id]);
        AuditLog::record('DELETE', 'users', $id, "Pengguna ID {$id} dinonaktifkan (soft delete)");
        return $ok;
    }

    public static function getRoles(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    }
}
