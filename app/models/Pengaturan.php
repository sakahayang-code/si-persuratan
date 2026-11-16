<?php
// persuratan/app/models/Pengaturan.php

class Pengaturan {
    private static array $cache = [];

    public static function getAll(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM pengaturan ORDER BY category, key_name");
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $r) {
            $result[$r['key_name']] = $r['value_text'];
        }
        self::$cache = $result;
        return $result;
    }

    public static function get(string $key, ?string $default = null): ?string {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT value_text FROM pengaturan WHERE key_name = :key");
        $stmt->execute([':key' => $key]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            self::$cache[$key] = $val;
            return $val;
        }
        return $default;
    }

    public static function set(string $key, ?string $value, string $category = 'umum', ?string $description = null): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO pengaturan (key_name, value_text, category, description, updated_at)
            VALUES (:key, :val, :cat, :desc, datetime('now', 'localtime'))
            ON CONFLICT(key_name) DO UPDATE SET 
                value_text = excluded.value_text,
                updated_at = datetime('now', 'localtime')
        ");
        $stmt->execute([
            ':key' => $key,
            ':val' => $value,
            ':cat' => $category,
            ':desc' => $description
        ]);
        self::$cache[$key] = $value;
        AuditLog::record('UPDATE', 'settings', null, "Pengaturan '{$key}' diperbarui");
    }
}
