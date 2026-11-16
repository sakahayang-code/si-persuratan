<?php
// persuratan/app/helpers/AuditLog.php

class AuditLog {
    public static function record(string $action, string $module, ?int $recordId = null, ?string $details = null): void {
        try {
            $db = Database::getInstance();
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $stmt = $db->prepare("
                INSERT INTO aktivitas_log (user_id, action, module, record_id, details, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :module, :record_id, :details, :ip_address, :user_agent, datetime('now', 'localtime'))
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => strtoupper($action),
                ':module' => $module,
                ':record_id' => $recordId,
                ':details' => $details,
                ':ip_address' => $ip,
                ':user_agent' => substr($ua, 0, 255)
            ]);
        } catch (Throwable $e) {
            // Fail silently so logging error never blocks critical business transaction
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }

    public static function getRecent(int $limit = 20): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT a.*, u.full_name, u.username, r.name as role_name 
            FROM aktivitas_log a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY a.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
