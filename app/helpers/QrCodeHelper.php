<?php
// persuratan/app/helpers/QrCodeHelper.php

use chillerlan\QRCode\QRCode;

class QrCodeHelper {
    /**
     * Generate Data URI (SVG base64) for QR Code
     */
    public static function generateDataUri(string $content): string {
        try {
            $qrcode = new QRCode();
            return $qrcode->render($content);
        } catch (Throwable $e) {
            error_log("QRCode error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate Verification URL for a document token
     */
    public static function getVerificationUrl(string $token): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        return "{$protocol}://{$host}/verifikasi.php?doc=" . urlencode($token);
    }
}
