<?php
// persuratan/app/models/Arsip.php

class Arsip {
    public static function getAll(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "
            SELECT a.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama
            FROM arsip a
            LEFT JOIN klasifikasi_surat k ON a.klasifikasi_id = k.id
            WHERE a.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.kode_arsip LIKE :search OR a.judul LIKE :search OR a.nomor_dokumen LIKE :search OR a.lokasi_fisik LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['jenis'])) {
            $sql .= " AND a.jenis_dokumen = :jenis";
            $params[':jenis'] = $filters['jenis'];
        }

        if (!empty($filters['tahun'])) {
            $sql .= " AND a.tahun = :tahun";
            $params[':tahun'] = (int)$filters['tahun'];
        }

        if (!empty($filters['status_retensi'])) {
            $sql .= " AND a.status_retensi = :status_retensi";
            $params[':status_retensi'] = $filters['status_retensi'];
        }

        if (isset($filters['is_important']) && $filters['is_important'] !== '') {
            $sql .= " AND a.is_important = :is_important";
            $params[':is_important'] = (int)$filters['is_important'];
        }

        $sql .= " ORDER BY a.is_important DESC, a.tanggal_dokumen DESC, a.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT a.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama
            FROM arsip a
            LEFT JOIN klasifikasi_surat k ON a.klasifikasi_id = k.id
            WHERE a.id = :id AND a.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function toggleImportant(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE arsip SET is_important = CASE WHEN is_important = 1 THEN 0 ELSE 1 END WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function updateRetentionStatus(int $id, string $status): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE arsip SET status_retensi = :status, updated_at = datetime('now', 'localtime') WHERE id = :id");
        $ok = $stmt->execute([':status' => $status, ':id' => $id]);
        AuditLog::record('UPDATE', 'arsip', $id, "Status retensi arsip ID {$id} diubah menjadi {$status}");
        return $ok;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $kodeArsip = 'ARS-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

        $stmt = $db->prepare("
            INSERT INTO arsip (
                kode_arsip, jenis_dokumen, referensi_id, judul, nomor_dokumen, tanggal_dokumen,
                klasifikasi_id, tahun, lokasi_fisik, file_path, status_retensi, is_important, created_at
            ) VALUES (
                :kode, :jenis, :ref_id, :judul, :nomor, :tgl,
                :klasifikasi, :tahun, :lokasi, :filepath, 'aktif', :important, datetime('now', 'localtime')
            )
        ");

        $stmt->execute([
            ':kode' => $kodeArsip,
            ':jenis' => $data['jenis_dokumen'] ?? 'dokumen_lain',
            ':ref_id' => !empty($data['referensi_id']) ? (int)$data['referensi_id'] : null,
            ':judul' => trim($data['judul']),
            ':nomor' => trim($data['nomor_dokumen']),
            ':tgl' => $data['tanggal_dokumen'] ?? date('Y-m-d'),
            ':klasifikasi' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            ':tahun' => !empty($data['tahun']) ? (int)$data['tahun'] : (int)date('Y'),
            ':lokasi' => trim($data['lokasi_fisik'] ?? 'Rak Arsip Utama'),
            ':filepath' => $data['file_path'] ?? '',
            ':important' => !empty($data['is_important']) ? 1 : 0
        ]);

        $id = (int)$db->lastInsertId();
        AuditLog::record('CREATE', 'arsip', $id, "Arsip digital baru dibuat: {$data['judul']} ({$data['nomor_dokumen']})");
        return $id;
    }
}
