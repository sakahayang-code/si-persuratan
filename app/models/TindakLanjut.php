<?php
// persuratan/app/models/TindakLanjut.php

class TindakLanjut {
    public static function getBySuratMasukId(int $suratMasukId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT tl.*, u.full_name as user_nama, u.jabatan as user_jabatan
            FROM tindak_lanjut tl
            JOIN users u ON tl.user_id = u.id
            WHERE tl.surat_masuk_id = :sm_id
            ORDER BY tl.created_at ASC
        ");
        $stmt->execute([':sm_id' => $suratMasukId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO tindak_lanjut (
                disposisi_detail_id, surat_masuk_id, user_id, tindakan, catatan, file_lampiran, status, created_at
            ) VALUES (
                :disp_id, :sm_id, :user_id, :tindakan, :catatan, :file_lampiran, :status, datetime('now', 'localtime')
            )
        ");

        $stmt->execute([
            ':disp_id' => !empty($data['disposisi_detail_id']) ? (int)$data['disposisi_detail_id'] : null,
            ':sm_id' => (int)$data['surat_masuk_id'],
            ':user_id' => Auth::id() ?: (int)$data['user_id'],
            ':tindakan' => trim($data['tindakan']),
            ':catatan' => trim($data['catatan'] ?? ''),
            ':file_lampiran' => $data['file_lampiran'] ?? null,
            ':status' => $data['status'] ?? 'selesai'
        ]);

        $id = (int)$db->lastInsertId();

        // Update status detail disposisi jika ada
        if (!empty($data['disposisi_detail_id'])) {
            Disposisi::updateStatus((int)$data['disposisi_detail_id'], $data['status'] ?? 'selesai');
        }

        // Update status surat masuk menjadi 'tindak_lanjut' atau 'selesai'
        $newStatus = ($data['status'] === 'selesai') ? 'selesai' : 'tindak_lanjut';
        SuratMasuk::updateStatus((int)$data['surat_masuk_id'], $newStatus);

        AuditLog::record('ACTION', 'tindak_lanjut', $id, "Tindak lanjut dicatat untuk Surat Masuk ID {$data['surat_masuk_id']}");
        return $id;
    }
}
