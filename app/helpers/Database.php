<?php
// persuratan/app/helpers/Database.php

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';
            $dbPath = $config['database'];
            $dbDir = dirname($dbPath);

            // Ensure storage/database directory exists with writable permissions
            if (!is_dir($dbDir)) {
                @mkdir($dbDir, 0777, true);
            }
            @chmod($dbDir, 0777);

            // Ensure other storage directories exist as required by Section 8
            $dirs = [
                STORAGE_PATH . '/documents/surat_masuk',
                STORAGE_PATH . '/documents/surat_keluar',
                STORAGE_PATH . '/documents/signatures',
                STORAGE_PATH . '/documents/lampiran',
                STORAGE_PATH . '/backups',
                STORAGE_PATH . '/logs',
                STORAGE_PATH . '/sessions'
            ];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @chmod($dir, 0777);
            }

            $isNewDb = !file_exists($dbPath) || filesize($dbPath) === 0;

            try {
                self::$instance = new PDO("sqlite:" . $dbPath, null, null, $config['options']);
            } catch (PDOException $e) {
                // Friendly diagnostic error for cPanel / hosting environments
                $msg = $e->getMessage();
                if (str_contains($msg, 'could not find driver')) {
                    die("<div style='font-family:sans-serif; padding:30px; line-height:1.6; max-width:600px; margin:40px auto; border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 10px 25px rgba(0,0,0,0.05);'>
                        <h2 style='color:#dc2626; margin-top:0;'>⚠️ Ekstensi PDO SQLite Tidak Aktif di cPanel</h2>
                        <p>Server cPanel Anda belum mengaktifkan modul <strong>pdo_sqlite</strong> dan <strong>sqlite3</strong>.</p>
                        <p><strong>Cara mengaktifkan di cPanel:</strong></p>
                        <ol>
                            <li>Masuk ke cPanel hosting Anda.</li>
                            <li>Buka menu <strong>Select PHP Version</strong> atau <strong>MultiPHP INI Editor</strong>.</li>
                            <li>Buka tab <strong>Extensions</strong>.</li>
                            <li>Beri tanda centang pada <code>pdo_sqlite</code> dan <code>sqlite3</code>.</li>
                            <li>Muat ulang halaman ini.</li>
                        </ol>
                        <p style='color:#64748b; font-size:12px;'>Kunjungi <a href='" . url('check.php') . "'>Halaman Pemeriksaan Sistem (check.php)</a> untuk diagnosa lengkap.</p>
                    </div>");
                } elseif (str_contains($msg, 'unable to open database file') || str_contains($msg, 'readonly')) {
                    die("<div style='font-family:sans-serif; padding:30px; line-height:1.6; max-width:600px; margin:40px auto; border:1px solid #e2e8f0; border-radius:12px; background:#fff;'>
                        <h2 style='color:#dc2626; margin-top:0;'>⚠️ Izin Akses Direktori Database Terkunci</h2>
                        <p>Web server cPanel tidak memiliki izin menulis (write permission) pada folder database SQLite.</p>
                        <p><strong>Solusi cPanel:</strong></p>
                        <ol>
                            <li>Buka cPanel <strong>File Manager</strong>.</li>
                            <li>Buka folder <code>storage/</code> dan klik kanan pada folder <code>database</code>.</li>
                            <li>Pilih <strong>Change Permissions</strong> dan ubah menjadi <strong>775</strong> atau <strong>777</strong>.</li>
                            <li>Pastikan file <code>storage/database/persuratan.sqlite</code> juga memiliki izin <strong>664</strong> atau <strong>666</strong>.</li>
                        </ol>
                    </div>");
                }
                throw $e;
            }
            
            // Set PRAGMA settings for performance, safety and foreign keys
            try {
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                self::$instance->exec("PRAGMA journal_mode = WAL;");
                self::$instance->exec("PRAGMA busy_timeout = 5000;");
            } catch (Throwable $e) {
                // If WAL is restricted by filesystem, fallback to normal journal mode
                @self::$instance->exec("PRAGMA journal_mode = DELETE;");
            }

            if (file_exists($dbPath)) {
                @chmod($dbPath, 0666);
            }

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
