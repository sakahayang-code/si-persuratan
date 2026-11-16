<?php
// persuratan/app/models/Klasifikasi.php

class Klasifikasi {
    public static function getAll(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM klasifikasi_surat WHERE deleted_at IS NULL ORDER BY kode ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM klasifikasi_surat WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO klasifikasi_surat (kode, nama, uraian, retensi_aktif, retensi_inaktif, keterangan, is_active, created_at)
            VALUES (:kode, :nama, :uraian, :ret_aktif, :ret_inaktif, :ket, 1, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':kode' => trim($data['kode']),
            ':nama' => trim($data['nama']),
            ':uraian' => trim($data['uraian'] ?? ''),
            ':ret_aktif' => (int)($data['retensi_aktif'] ?? 2),
            ':ret_inaktif' => (int)($data['retensi_inaktif'] ?? 5),
            ':ket' => trim($data['keterangan'] ?? '')
        ]);
        $id = (int)$db->lastInsertId();
        AuditLog::record('CREATE', 'master', $id, "Klasifikasi surat baru ditambahkan: {$data['kode']} - {$data['nama']}");
        return $id;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE klasifikasi_surat SET
                kode = :kode,
                nama = :nama,
                uraian = :uraian,
                retensi_aktif = :ret_aktif,
                retensi_inaktif = :ret_inaktif,
                keterangan = :ket,
                updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            ':kode' => trim($data['kode']),
            ':nama' => trim($data['nama']),
            ':uraian' => trim($data['uraian'] ?? ''),
            ':ret_aktif' => (int)($data['retensi_aktif'] ?? 2),
            ':ret_inaktif' => (int)($data['retensi_inaktif'] ?? 5),
            ':ket' => trim($data['keterangan'] ?? ''),
            ':id' => $id
        ]);
        AuditLog::record('UPDATE', 'master', $id, "Klasifikasi surat ID {$id} diperbarui");
        return $ok;
    }

    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE klasifikasi_surat SET deleted_at = datetime('now', 'localtime') WHERE id = :id");
        $ok = $stmt->execute([':id' => $id]);
        AuditLog::record('DELETE', 'master', $id, "Klasifikasi surat ID {$id} dihapus (soft delete)");
        return $ok;
    }
}
