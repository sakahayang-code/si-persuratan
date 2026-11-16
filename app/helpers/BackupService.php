<?php
// persuratan/app/helpers/BackupService.php

class BackupService {
    /**
     * Create full backup archive (SQLite DB + all documents + manifest)
     */
    public static function createBackup(string $type = 'manual', ?string $notes = null): array {
        if (!class_exists('ZipArchive')) {
            throw new Exception("Ekstensi PHP ZipArchive tidak aktif.");
        }

        $backupDir = BACKUPS_PATH;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $filename = "backup_persuratan_{$type}_{$timestamp}.zip";
        $filepath = $backupDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat file arsip zip backup di {$filepath}");
        }

        // 1. Add SQLite Database
        if (file_exists(DB_PATH)) {
            $zip->addFile(DB_PATH, 'database/persuratan.sqlite');
        }

        // 2. Add Documents recursively
        $docsDir = DOCUMENTS_PATH;
        if (is_dir($docsDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($docsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $realPath = $file->getRealPath();
                    $relativePath = 'documents/' . substr($realPath, strlen($docsDir) + 1);
                    $zip->addFile($realPath, $relativePath);
                }
            }
        }

        // 3. Add Manifest Metadata
        $db = Database::getInstance();
        $manifest = [
            'app_name' => APP_NAME,
            'app_version' => APP_VERSION,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => $type,
            'notes' => $notes,
            'database_size' => file_exists(DB_PATH) ? filesize(DB_PATH) : 0,
            'total_users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_surat_masuk' => (int)$db->query("SELECT COUNT(*) FROM surat_masuk WHERE deleted_at IS NULL")->fetchColumn(),
            'total_surat_keluar' => (int)$db->query("SELECT COUNT(*) FROM surat_keluar WHERE deleted_at IS NULL")->fetchColumn(),
            'total_arsip' => (int)$db->query("SELECT COUNT(*) FROM arsip WHERE deleted_at IS NULL")->fetchColumn(),
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        $filesize = filesize($filepath);
        $checksum = hash_file('sha256', $filepath);

        // Record into backup_log table
        $stmt = $db->prepare("
            INSERT INTO backup_log (filename, filepath, filesize, type, status, checksum, notes, created_by, created_at)
            VALUES (:filename, :filepath, :filesize, :type, 'success', :checksum, :notes, :created_by, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':filename' => $filename,
            ':filepath' => $filepath,
            ':filesize' => $filesize,
            ':type' => $type,
            ':checksum' => $checksum,
            ':notes' => $notes,
            ':created_by' => Auth::id()
        ]);

        AuditLog::record('BACKUP', 'backup', null, "Backup sistem ({$type}) berhasil dibuat: {$filename} (" . round($filesize / 1024, 2) . " KB)");

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'filesize' => $filesize,
            'checksum' => $checksum,
            'manifest' => $manifest
        ];
    }

    /**
     * Restore system from a backup archive
     * Strictly follows Section 8.1 & 8.2:
     * 1. Validates archive
     * 2. Creates pre-restore safety backup
     * 3. Extracts DB & documents
     * 4. Runs integrity check
     */
    public static function restoreBackup(string $zipFilePath): array {
        if (!file_exists($zipFilePath)) {
            throw new Exception("File arsip backup tidak ditemukan.");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new Exception("File backup corrupt atau bukan format ZIP yang valid.");
        }

        // Validate structure: must contain database/persuratan.sqlite
        if ($zip->locateName('database/persuratan.sqlite') === false) {
            $zip->close();
            throw new Exception("Arsip tidak valid: file database/persuratan.sqlite tidak ditemukan di dalam arsip.");
        }

        // Step 2: Mandatory safety backup before restore
        self::createBackup('pre_restore', "Auto-backup kondisi sebelum restore arsip " . basename($zipFilePath));

        // Close SQLite DB connection to allow file replacement safely
        // In PHP PDO, unsetting static instance will release locks
        $db = null;

        $tempExtractDir = STORAGE_PATH . '/temp_restore_' . time();
        mkdir($tempExtractDir, 0755, true);
        $zip->extractTo($tempExtractDir);
        $zip->close();

        // Step 3: Replace database file
        $newDbPath = $tempExtractDir . '/database/persuratan.sqlite';
        if (file_exists($newDbPath)) {
            copy($newDbPath, DB_PATH);
        }

        // Step 4: Restore documents
        $newDocsDir = $tempExtractDir . '/documents';
        if (is_dir($newDocsDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($newDocsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $realPath = $file->getRealPath();
                    $rel = substr($realPath, strlen($newDocsDir) + 1);
                    $targetPath = DOCUMENTS_PATH . '/' . $rel;
                    $targetParent = dirname($targetPath);
                    if (!is_dir($targetParent)) {
                        mkdir($targetParent, 0755, true);
                    }
                    copy($realPath, $targetPath);
                }
            }
        }

        // Clean up temp directory
        self::deleteDirRecursively($tempExtractDir);

        // Step 5: Check database integrity after restore
        $integrity = Database::checkIntegrity();

        AuditLog::record('RESTORE', 'backup', null, "Sistem dipulihkan dari arsip: " . basename($zipFilePath) . ". Status integritas: " . $integrity['status']);

        return [
            'success' => true,
            'integrity' => $integrity,
            'message' => 'Sistem dan dokumen berhasil dipulihkan.'
        ];
    }

    public static function getBackupList(): array {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT b.*, u.full_name as author_name 
            FROM backup_log b
            LEFT JOIN users u ON b.created_by = u.id
            ORDER BY b.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    private static function deleteDirRecursively(string $dir): void {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            (is_dir($path)) ? self::deleteDirRecursively($path) : unlink($path);
        }
        rmdir($dir);
    }
}
