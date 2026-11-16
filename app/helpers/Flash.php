<?php
// persuratan/app/helpers/Flash.php

class Flash {
    public static function set(string $type, string $message): void {
        $_SESSION['flash_messages'][] = [
            'type' => $type, // 'success', 'danger', 'warning', 'info'
            'message' => $message
        ];
    }

    public static function get(): array {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    public static function has(): bool {
        return !empty($_SESSION['flash_messages']);
    }
}
