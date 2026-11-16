<?php
// persuratan/app/helpers/Database.php

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';
            $dbPath = $config['database'];
            $dbDir = dirname($dbPath);

            // Ensure storage/database directory exists
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            // Ensure other storage directories exist as required by Section 8
            $dirs = [
                STORAGE_PATH . '/documents/surat_masuk',
                STORAGE_PATH . '/documents/surat_keluar',
                STORAGE_PATH . '/documents/signatures',
                STORAGE_PATH . '/documents/lampiran',
                STORAGE_PATH . '/backups',
                STORAGE_PATH . '/logs'
            ];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }

            $isNewDb = !file_exists($dbPath) || filesize($dbPath) === 0;

            self::$instance = new PDO("sqlite:" . $dbPath, null, null, $config['options']);
            
            // Set PRAGMA settings for performance, safety and foreign keys
            self::$instance->exec("PRAGMA foreign_keys = ON;");
            self::$instance->exec("PRAGMA journal_mode = WAL;");
            self::$instance->exec("PRAGMA busy_timeout = 5000;");

            // If it's a new DB, auto run migrations
            if ($isNewDb) {
                Migration::run(self::$instance);
            }
        }

        return self::$instance;
    }

    /**
     * Check SQLite integrity as required in Section 8 & 12
     */
    public static function checkIntegrity(): array {
        $db = self::getInstance();
        $stmt = $db->query("PRAGMA integrity_check;");
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return [
            'status' => (count($results) === 1 && $results[0] === 'ok') ? 'OK' : 'CORRUPTED',
            'details' => $results
        ];
    }

    /**
     * Get Database size in human readable format
     */
    public static function getDatabaseSize(): string {
        $path = DB_PATH;
        if (!file_exists($path)) {
            return '0 B';
        }
        $bytes = filesize($path);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
