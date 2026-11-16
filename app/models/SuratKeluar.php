<?php
// persuratan/app/models/SuratKeluar.php

class SuratKeluar {
    public static function getAll(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "
            SELECT sk.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama,
                   p.nama as pejabat_nama, p.jabatan as pejabat_jabatan,
                   u.full_name as pembuat_nama, v.full_name as penyetuju_nama
            FROM surat_keluar sk
            LEFT JOIN klasifikasi_surat k ON sk.klasifikasi_id = k.id
            LEFT JOIN pejabat p ON sk.pejabat_id = p.id
            LEFT JOIN users u ON sk.created_by = u.id
            LEFT JOIN users v ON sk.verified_by = v.id
            WHERE sk.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (sk.nomor_surat LIKE :search 
                      OR sk.nomor_agenda LIKE :search 
                      OR sk.tujuan LIKE :search 
                      OR sk.perihal LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND sk.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['kerahasiaan'])) {
            $sql .= " AND sk.tingkat_kerahasiaan = :kerahasiaan";
            $params[':kerahasiaan'] = $filters['kerahasiaan'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sk.tanggal_surat >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sk.tanggal_surat <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY sk.tanggal_surat DESC, sk.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT sk.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama,
                   p.nama as pejabat_nama, p.jabatan as pejabat_jabatan, p.nip as pejabat_nip, p.signature_file, p.stempel_file,
                   t.nama_template, t.konten_template,
                   u.full_name as pembuat_nama, v.full_name as penyetuju_nama
            FROM surat_keluar sk
            LEFT JOIN klasifikasi_surat k ON sk.klasifikasi_id = k.id
            LEFT JOIN pejabat p ON sk.pejabat_id = p.id
            LEFT JOIN template_surat t ON sk.template_id = t.id
            LEFT JOIN users u ON sk.created_by = u.id
            LEFT JOIN users v ON sk.verified_by = v.id
            WHERE sk.id = :id AND sk.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByToken(string $token): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT sk.*, k.kode as klasifikasi_kode, k.nama as klasifikasi_nama,
                   p.nama as pejabat_nama, p.jabatan as pejabat_jabatan, p.nip as pejabat_nip,
                   v.full_name as penyetuju_nama
            FROM surat_keluar sk
            LEFT JOIN klasifikasi_surat k ON sk.klasifikasi_id = k.id
            LEFT JOIN pejabat p ON sk.pejabat_id = p.id
            LEFT JOIN users v ON sk.verified_by = v.id
            WHERE sk.token_verifikasi = :token AND sk.deleted_at IS NULL
        ");
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $nomorAgenda = !empty($data['nomor_agenda']) ? $data['nomor_agenda'] : Numbering::generateAgendaNumber('keluar');

        // Generate token verifikasi for QR verification
        $token = 'VERIF-' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 8)) . '-' . date('Y');

        $nomorSurat = trim($data['nomor_surat'] ?? '');
        if (empty($nomorSurat) && !empty($data['klasifikasi_kode'])) {
            $nomorSurat = Numbering::generateNextNumber($data['klasifikasi_kode']);
        } elseif (empty($nomorSurat)) {
            $nomorSurat = 'DRAFT/' . date('Ymd') . '/' . rand(100, 999);
        }

        $stmt = $db->prepare("
            INSERT INTO surat_keluar (
                nomor_agenda, nomor_surat, tanggal_surat, tujuan, perihal, isi_ringkas,
                template_id, template_data, klasifikasi_id, tingkat_kerahasiaan, pejabat_id,
                file_dokumen, lampiran_files, status, token_verifikasi, created_by, created_at
            ) VALUES (
                :nomor_agenda, :nomor_surat, :tanggal_surat, :tujuan, :perihal, :isi_ringkas,
                :template_id, :template_data, :klasifikasi_id, :tingkat_kerahasiaan, :pejabat_id,
                :file_dokumen, :lampiran_files, :status, :token_verifikasi, :created_by, datetime('now', 'localtime')
            )
        ");

        $stmt->execute([
            ':nomor_agenda' => $nomorAgenda,
            ':nomor_surat' => $nomorSurat,
            ':tanggal_surat' => $data['tanggal_surat'] ?? date('Y-m-d'),
            ':tujuan' => trim($data['tujuan']),
            ':perihal' => trim($data['perihal']),
            ':isi_ringkas' => trim($data['isi_ringkas'] ?? ''),
            ':template_id' => !empty($data['template_id']) ? (int)$data['template_id'] : null,
            ':template_data' => isset($data['template_data']) ? (is_array($data['template_data']) ? json_encode($data['template_data']) : $data['template_data']) : null,
            ':klasifikasi_id' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            ':tingkat_kerahasiaan' => $data['tingkat_kerahasiaan'] ?? 'biasa',
            ':pejabat_id' => !empty($data['pejabat_id']) ? (int)$data['pejabat_id'] : null,
            ':file_dokumen' => $data['file_dokumen'] ?? null,
            ':lampiran_files' => isset($data['lampiran_files']) ? json_encode($data['lampiran_files']) : '[]',
            ':status' => $data['status'] ?? 'draft',
            ':token_verifikasi' => $token,
            ':created_by' => Auth::id() ?: 1
        ]);

        $id = (int)$db->lastInsertId();

        // Catat otomatis ke buku agenda surat keluar
        $stmtAgenda = $db->prepare("
            INSERT INTO agenda_surat (jenis, nomor_agenda, tanggal_agenda, referensi_id, nomor_surat, tanggal_surat, pengirim_penerima, perihal, keterangan, created_at)
            VALUES ('keluar', :nomor_agenda, :tanggal_agenda, :ref_id, :nomor_surat, :tanggal_surat, :tujuan, :perihal, 'Otomatis dari pencatatan surat keluar', datetime('now', 'localtime'))
        ");
        $stmtAgenda->execute([
            ':nomor_agenda' => $nomorAgenda,
            ':tanggal_agenda' => $data['tanggal_surat'] ?? date('Y-m-d'),
            ':ref_id' => $id,
            ':nomor_surat' => $nomorSurat,
            ':tanggal_surat' => $data['tanggal_surat'] ?? date('Y-m-d'),
            ':tujuan' => $data['tujuan'],
            ':perihal' => $data['perihal']
        ]);

        AuditLog::record('CREATE', 'surat_keluar', $id, "Surat Keluar dibuat: {$nomorSurat} kepada {$data['tujuan']}");

        return $id;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $fields = [
            'nomor_surat' => trim($data['nomor_surat']),
            'tanggal_surat' => $data['tanggal_surat'],
            'tujuan' => trim($data['tujuan']),
            'perihal' => trim($data['perihal']),
            'isi_ringkas' => trim($data['isi_ringkas'] ?? ''),
            'klasifikasi_id' => !empty($data['klasifikasi_id']) ? (int)$data['klasifikasi_id'] : null,
            'tingkat_kerahasiaan' => $data['tingkat_kerahasiaan'] ?? 'biasa',
            'pejabat_id' => !empty($data['pejabat_id']) ? (int)$data['pejabat_id'] : null,
        ];

        if (isset($data['status'])) {
            $fields['status'] = $data['status'];
        }
        if (isset($data['template_id'])) {
            $fields['template_id'] = (int)$data['template_id'];
        }
        if (isset($data['template_data'])) {
            $fields['template_data'] = is_array($data['template_data']) ? json_encode($data['template_data']) : $data['template_data'];
        }
        if (!empty($data['file_dokumen'])) {
            $fields['file_dokumen'] = $data['file_dokumen'];
        }

        $setPart = [];
        $params = [':id' => $id];
        foreach ($fields as $key => $val) {
            $setPart[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }

        $sql = "UPDATE surat_keluar SET " . implode(', ', $setPart) . ", updated_at = datetime('now', 'localtime') WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);

        AuditLog::record('UPDATE', 'surat_keluar', $id, "Surat Keluar ID {$id} diperbarui");
        return $ok;
    }

    public static function approve(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE surat_keluar SET 
                status = 'disetujui',
                verified_by = :verified_by,
                verified_at = datetime('now', 'localtime'),
                updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            ':id' => $id,
            ':verified_by' => Auth::id()
        ]);

        AuditLog::record('APPROVE', 'surat_keluar', $id, "Surat Keluar ID {$id} disetujui oleh " . (Auth::user()['full_name'] ?? 'Pimpinan'));
        return $ok;
    }

    public static function publish(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE surat_keluar SET 
                status = 'diterbitkan',
                updated_at = datetime('now', 'localtime')
            WHERE id = :id
        ");
        $ok = $stmt->execute([':id' => $id]);

        // Auto arsipkan surat keluar saat diterbitkan
        $surat = self::findById($id);
        if ($surat) {
            $kodeArsip = 'ARS-SK-' . date('Y') . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
            $stmtArsip = $db->prepare("
                INSERT OR IGNORE INTO arsip (kode_arsip, jenis_dokumen, referensi_id, judul, nomor_dokumen, tanggal_dokumen, klasifikasi_id, tahun, lokasi_fisik, file_path, status_retensi, is_important, created_at)
                VALUES (:kode, 'surat_keluar', :ref_id, :judul, :nomor_dokumen, :tgl, :klasifikasi, :tahun, :lokasi, :filepath, 'aktif', 0, datetime('now', 'localtime'))
            ");
            $stmtArsip->execute([
                ':kode' => $kodeArsip,
                ':ref_id' => $id,
                ':judul' => $surat['perihal'],
                ':nomor_dokumen' => $surat['nomor_surat'],
                ':tgl' => $surat['tanggal_surat'],
                ':klasifikasi' => $surat['klasifikasi_id'],
                ':tahun' => (int)date('Y', strtotime($surat['tanggal_surat'])),
                ':lokasi' => 'Bantex Surat Keluar ' . date('Y'),
                ':filepath' => $surat['file_dokumen'] ?? ''
            ]);
        }

        AuditLog::record('PUBLISH', 'surat_keluar', $id, "Surat Keluar ID {$id} diterbitkan secara resmi");
        return $ok;
    }

    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE surat_keluar SET deleted_at = datetime('now', 'localtime') WHERE id = :id");
        $ok = $stmt->execute([':id' => $id]);
        AuditLog::record('DELETE', 'surat_keluar', $id, "Surat Keluar ID {$id} dihapus (soft delete)");
        return $ok;
    }
}
