<?php
// persuratan/app/models/SuratMasuk.php

class SuratMasuk {
    public static function getAll(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "
            SELECT sm.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama, 
                   u.full_name as penerima_nama,
                   (SELECT COUNT(*) FROM disposisi WHERE surat_masuk_id = sm.id) as total_disposisi
            FROM surat_masuk sm
            LEFT JOIN klasifikasi_surat k ON sm.klasifikasi_id = k.id
            LEFT JOIN users u ON sm.penerima_user_id = u.id
            WHERE sm.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (sm.nomor_surat LIKE :search 
                      OR sm.nomor_agenda LIKE :search 
                      OR sm.pengirim LIKE :search 
                      OR sm.asal_instansi LIKE :search 
                      OR sm.perihal LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND sm.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['urgensi'])) {
            $sql .= " AND sm.tingkat_urgensi = :urgensi";
            $params[':urgensi'] = $filters['urgensi'];
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND sm.kategori = :kategori";
            $params[':kategori'] = $filters['kategori'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sm.tanggal_surat >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sm.tanggal_surat <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY sm.tanggal_terima DESC, sm.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT sm.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama, 
                   u.full_name as penerima_nama
            FROM surat_masuk sm
            LEFT JOIN klasifikasi_surat k ON sm.klasifikasi_id = k.id
            LEFT JOIN users u ON sm.penerima_user_id = u.id
            WHERE sm.id = :id AND sm.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $nomorAgenda = !empty($data['nomor_agenda']) ? $data['nomor_agenda'] : Numbering::generateAgendaNumber('masuk');

        $stmt = $db->prepare("
            INSERT INTO surat_masuk (
                nomor_agenda, nomor_surat, tanggal_surat, tanggal_terima, pengirim, asal_instansi,
                perihal, isi_ringkas, kategori, klasifikasi_id, tingkat_urgensi, file_dokumen,
                lampiran_files, status, penerima_user_id, created_at
            ) VALUES (
                :nomor_agenda, :nomor_surat, :tanggal_surat, :tanggal_terima, :pengirim, :asal_instansi,
                :perihal, :isi_ringkas, :kategori, :klasifikasi_id, :tingkat_urgensi, :file_dokumen,
                :lampiran_files, :status, :penerima_user_id, datetime('now', 'localtime')
            )
        ");

        $stmt->execute([
            ':nomor_agenda' => $nomorAgenda,
            ':nomor_surat' => trim($data['nomor_surat']),
            ':tanggal_surat' => $data['tanggal_surat'],
            ':tanggal_terima' => $data['tanggal_terima'] ?? date('Y-m-d'),
            ':pengirim' => trim($data['pengirim']),
            ':asal_instansi' => trim($data['asal_instansi']),
            ':perihal' => trim($data['perihal']),
            ':isi_ringkas' => trim($data['isi_ringkas'] ?? ''),
            ':kategori' => $data['kategori'] ?? 'Umum',
            ':klasifikasi_id' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            ':tingkat_urgensi' => $data['tingkat_urgensi'] ?? 'biasa',
            ':file_dokumen' => $data['file_dokumen'] ?? null,
            ':lampiran_files' => isset($data['lampiran_files']) ? json_encode($data['lampiran_files']) : '[]',
            ':status' => 'diterima',
            ':penerima_user_id' => Auth::id()
        ]);

        $id = (int)$db->lastInsertId();

        // Otomatis catat ke buku agenda
        $stmtAgenda = $db->prepare("
            INSERT INTO agenda_surat (jenis, nomor_agenda, tanggal_agenda, referensi_id, nomor_surat, tanggal_surat, pengirim_penerima, perihal, keterangan, created_at)
            VALUES ('masuk', :nomor_agenda, :tanggal_agenda, :ref_id, :nomor_surat, :tanggal_surat, :pengirim, :perihal, 'Otomatis dari pencatatan surat masuk', datetime('now', 'localtime'))
        ");
        $stmtAgenda->execute([
            ':nomor_agenda' => $nomorAgenda,
            ':tanggal_agenda' => $data['tanggal_terima'] ?? date('Y-m-d'),
            ':ref_id' => $id,
            ':nomor_surat' => $data['nomor_surat'],
            ':tanggal_surat' => $data['tanggal_surat'],
            ':pengirim' => $data['pengirim'] . ' (' . $data['asal_instansi'] . ')',
            ':perihal' => $data['perihal']
        ]);

        // Otomatis catat ke arsip digital
        $kodeArsip = 'ARS-SM-' . date('Y') . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        $stmtArsip = $db->prepare("
            INSERT INTO arsip (kode_arsip, jenis_dokumen, referensi_id, judul, nomor_dokumen, tanggal_dokumen, klasifikasi_id, tahun, lokasi_fisik, file_path, status_retensi, is_important, created_at)
            VALUES (:kode, 'surat_masuk', :ref_id, :judul, :nomor_dokumen, :tgl, :klasifikasi, :tahun, :lokasi, :filepath, 'aktif', 0, datetime('now', 'localtime'))
        ");
        $stmtArsip->execute([
            ':kode' => $kodeArsip,
            ':ref_id' => $id,
            ':judul' => $data['perihal'],
            ':nomor_dokumen' => $data['nomor_surat'],
            ':tgl' => $data['tanggal_surat'],
            ':klasifikasi' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            ':tahun' => (int)date('Y', strtotime($data['tanggal_surat'])),
            ':lokasi' => $data['lokasi_fisik'] ?? 'Bantex Surat Masuk ' . date('Y'),
            ':filepath' => $data['file_dokumen'] ?? ''
        ]);

        AuditLog::record('CREATE', 'surat_masuk', $id, "Surat Masuk baru diregistrasi: {$data['nomor_surat']} dari {$data['pengirim']}");

        return $id;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $fields = [
            'nomor_surat' => trim($data['nomor_surat']),
            'tanggal_surat' => $data['tanggal_surat'],
            'tanggal_terima' => $data['tanggal_terima'],
            'pengirim' => trim($data['pengirim']),
            'asal_instansi' => trim($data['asal_instansi']),
            'perihal' => trim($data['perihal']),
            'isi_ringkas' => trim($data['isi_ringkas'] ?? ''),
            'kategori' => $data['kategori'] ?? 'Umum',
            'klasifikasi_id' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            'tingkat_urgensi' => $data['tingkat_urgensi'] ?? 'biasa',
            'status' => $data['status'] ?? 'diterima',
        ];

        if (!empty($data['file_dokumen'])) {
            $fields['file_dokumen'] = $data['file_dokumen'];
        }

        $setPart = [];
        $params = [':id' => $id];
        foreach ($fields as $key => $val) {
            $setPart[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }

        $sql = "UPDATE surat_masuk SET " . implode(', ', $setPart) . ", updated_at = datetime('now', 'localtime') WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);

        AuditLog::record('UPDATE', 'surat_masuk', $id, "Surat Masuk ID {$id} diperbarui");
        return $ok;
    }

    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE surat_masuk SET deleted_at = datetime('now', 'localtime') WHERE id = :id");
        $ok = $stmt->execute([':id' => $id]);
        AuditLog::record('DELETE', 'surat_masuk', $id, "Surat Masuk ID {$id} dihapus (soft delete)");
        return $ok;
    }

    public static function updateStatus(int $id, string $status): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE surat_masuk SET status = :status, updated_at = datetime('now', 'localtime') WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
