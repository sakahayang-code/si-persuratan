<?php
// persuratan/app/helpers/Migration.php

class Migration {
    public static function run(?PDO $db = null): array {
        if ($db === null) {
            $db = Database::getInstance();
        }

        // 1. Create migrations tracking table if not exists
        $db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // 2. Fetch applied migrations
        $stmt = $db->query("SELECT version FROM migrations");
        $applied = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Scan migrations directory
        $migrationsDir = BASE_PATH . '/migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }

        $files = glob($migrationsDir . '/*.sql');
        sort($files);

        $results = [];

        foreach ($files as $file) {
            $basename = basename($file);
            $parts = explode('_', $basename, 2);
            $version = $parts[0];
            $name = isset($parts[1]) ? pathinfo($parts[1], PATHINFO_FILENAME) : $basename;

            if (in_array($version, $applied)) {
                continue;
            }

            $sql = file_get_contents($file);
            if (empty(trim($sql))) {
                continue;
            }

            try {
                $db->beginTransaction();
                $db->exec($sql);
                $ins = $db->prepare("INSERT INTO migrations (version, name) VALUES (:version, :name)");
                $ins->execute([
                    ':version' => $version,
                    ':name' => $name
                ]);
                $db->commit();
                $results[] = [
                    'version' => $version,
                    'name' => $name,
                    'status' => 'SUCCESS'
                ];
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $results[] = [
                    'version' => $version,
                    'name' => $name,
                    'status' => 'FAILED',
                    'error' => $e->getMessage()
                ];
                break; // Stop on first error
            }
        }

        return $results;
    }

    public static function getStatus(): array {
        $db = Database::getInstance();
        try {
            $stmt = $db->query("SELECT * FROM migrations ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}
