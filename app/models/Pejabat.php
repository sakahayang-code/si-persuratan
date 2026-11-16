<?php
// persuratan/app/models/Pejabat.php

class Pejabat {
    public static function getAll(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM pejabat ORDER BY is_active DESC, id ASC")->fetchAll();
    }

    public static function findActive(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM pejabat WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM pejabat WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO pejabat (nip, nama, jabatan, unit_kerja, signature_file, stempel_file, is_active, created_at)
            VALUES (:nip, :nama, :jabatan, :unit_kerja, :sig, :stempel, :active, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':nip' => trim($data['nip']),
            ':nama' => trim($data['nama']),
            ':jabatan' => trim($data['jabatan']),
            ':unit_kerja' => trim($data['unit_kerja'] ?? ''),
            ':sig' => $data['signature_file'] ?? null,
            ':stempel' => $data['stempel_file'] ?? null,
            ':active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        $id = (int)$db->lastInsertId();
        AuditLog::record('CREATE', 'master', $id, "Pejabat baru ditambahkan: {$data['nama']} ({$data['jabatan']})");
        return $id;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $fields = [
            'nip' => trim($data['nip']),
            'nama' => trim($data['nama']),
            'jabatan' => trim($data['jabatan']),
            'unit_kerja' => trim($data['unit_kerja'] ?? ''),
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ];

        if (!empty($data['signature_file'])) {
            $fields['signature_file'] = $data['signature_file'];
        }
        if (!empty($data['stempel_file'])) {
            $fields['stempel_file'] = $data['stempel_file'];
        }

        $setPart = [];
        $params = [':id' => $id];
        foreach ($fields as $key => $val) {
            $setPart[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }

        $sql = "UPDATE pejabat SET " . implode(', ', $setPart) . ", updated_at = datetime('now', 'localtime') WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);

        AuditLog::record('UPDATE', 'master', $id, "Data pejabat ID {$id} diperbarui");
        return $ok;
    }
}
