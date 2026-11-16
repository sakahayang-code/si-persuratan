<?php
// persuratan/app/models/Disposisi.php

class Disposisi {
    public static function getBySuratMasukId(int $suratMasukId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT d.*, u.full_name as pemberi_nama, u.jabatan as pemberi_jabatan
            FROM disposisi d
            JOIN users u ON d.pemberi_user_id = u.id
            WHERE d.surat_masuk_id = :sm_id
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([':sm_id' => $suratMasukId]);
        $disposisiList = $stmt->fetchAll();

        foreach ($disposisiList as &$disp) {
            $stmtDet = $db->prepare("
                SELECT dd.*, u.full_name as penerima_nama, u.jabatan as penerima_jabatan
                FROM disposisi_detail dd
                JOIN users u ON dd.penerima_user_id = u.id
                WHERE dd.disposisi_id = :disp_id
            ");
            $stmtDet->execute([':disp_id' => $disp['id']]);
            $disp['details'] = $stmtDet->fetchAll();
        }

        return $disposisiList;
    }

    public static function create(array $data, array $penerimaUserIds): int {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO disposisi (surat_masuk_id, pemberi_user_id, sifat, tenggat_waktu, catatan_instruksi, created_at)
                VALUES (:surat_masuk_id, :pemberi_user_id, :sifat, :tenggat_waktu, :catatan_instruksi, datetime('now', 'localtime'))
            ");
            $stmt->execute([
                ':surat_masuk_id' => (int)$data['surat_masuk_id'],
                ':pemberi_user_id' => Auth::id() ?: 3, // Pimpinan default
                ':sifat' => $data['sifat'] ?? 'biasa',
                ':tenggat_waktu' => !empty($data['tenggat_waktu']) ? $data['tenggat_waktu'] : null,
                ':catatan_instruksi' => trim($data['catatan_instruksi'])
            ]);

            $disposisiId = (int)$db->lastInsertId();

            // Insert details for each recipient
            $stmtDet = $db->prepare("
                INSERT INTO disposisi_detail (disposisi_id, penerima_user_id, status, created_at)
                VALUES (:disposisi_id, :penerima_user_id, 'belum_dibaca', datetime('now', 'localtime'))
            ");

            foreach ($penerimaUserIds as $userId) {
                $stmtDet->execute([
                    ':disposisi_id' => $disposisiId,
                    ':penerima_user_id' => (int)$userId
                ]);

                // Send notification to recipient
                Notifikasi::createNotification(
                    (int)$userId,
                    'Disposisi Baru Diterima',
                    "Anda menerima lembar disposisi baru: " . substr($data['catatan_instruksi'], 0, 80),
                    '/disposisi/detail.php?id=' . $disposisiId,
                    'disposisi'
                );
            }

            // Update status surat masuk menjadi 'disposisi'
            SuratMasuk::updateStatus((int)$data['surat_masuk_id'], 'disposisi');

            $db->commit();
            AuditLog::record('CREATE', 'disposisi', $disposisiId, "Disposisi diterbitkan untuk Surat Masuk ID {$data['surat_masuk_id']}");
            return $disposisiId;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getMyDisposisi(int $userId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT dd.id as detail_id, dd.status as detail_status, dd.tanggal_dibaca, dd.tanggal_selesai,
                   d.id as disposisi_id, d.sifat, d.tenggat_waktu, d.catatan_instruksi, d.created_at,
                   sm.id as surat_masuk_id, sm.nomor_surat, sm.perihal, sm.pengirim, sm.asal_instansi,
                   u.full_name as pemberi_nama
            FROM disposisi_detail dd
            JOIN disposisi d ON dd.disposisi_id = d.id
            JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
            JOIN users u ON d.pemberi_user_id = u.id
            WHERE dd.penerima_user_id = :user_id AND sm.deleted_at IS NULL
            ORDER BY dd.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $detailId, string $status): bool {
        $db = Database::getInstance();
        $now = date('Y-m-d H:i:s');
        $extra = "";
        $params = [':status' => $status, ':id' => $detailId];

        if ($status === 'dibaca' || $status === 'dalam_proses') {
            $extra = ", tanggal_dibaca = COALESCE(tanggal_dibaca, :now)";
            $params[':now'] = $now;
        } elseif ($status === 'selesai') {
            $extra = ", tanggal_selesai = :now";
            $params[':now'] = $now;
        }

        $sql = "UPDATE disposisi_detail SET status = :status, updated_at = datetime('now', 'localtime') {$extra} WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}
