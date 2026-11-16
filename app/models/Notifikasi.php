<?php
// persuratan/app/models/Notifikasi.php

class Notifikasi {
    public static function getUnread(int $userId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM notifikasi WHERE user_id = :uid AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function markAsRead(int $id, int $userId): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifikasi SET is_read = 1 WHERE id = :id AND user_id = :uid");
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }

    public static function markAllAsRead(int $userId): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = :uid");
        return $stmt->execute([':uid' => $userId]);
    }

    public static function createNotification(int $userId, string $title, string $message, ?string $link = null, string $type = 'info'): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO notifikasi (user_id, judul, pesan, link_url, tipe, is_read, created_at)
            VALUES (:uid, :title, :msg, :link, :type, 0, datetime('now', 'localtime'))
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':msg' => $message,
            ':link' => $link,
            ':type' => $type
        ]);
        return (int)$db->lastInsertId();
    }
}
