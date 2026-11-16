<?php
// persuratan/config/app.php

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Ensure timezone is set to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Paths definition
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('DB_PATH', STORAGE_PATH . '/database/persuratan.sqlite');
define('DOCUMENTS_PATH', STORAGE_PATH . '/documents');
define('BACKUPS_PATH', STORAGE_PATH . '/backups');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('SESSIONS_PATH', STORAGE_PATH . '/sessions');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Application metadata
define('APP_NAME', 'SI-PERSURATAN');
define('APP_VERSION', '1.0.0');
define('APP_RELEASE_DATE', '16 November 2026');
define('APP_SUBTITLE', 'Sistem Informasi Persuratan Digital & Arsip Terintegrasi');

// Dynamic Base URL detection (compatible with Root Domain, Subdomain, and cPanel Subfolders)
if (!defined('BASE_URL')) {
    if (getenv('APP_BASE_URL') !== false && getenv('APP_BASE_URL') !== '') {
        define('BASE_URL', rtrim(getenv('APP_BASE_URL'), '/'));
    } else {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        $scriptDir = str_replace('\\', '/', $scriptDir);
        // Strip trailing /public if present
        $scriptDir = preg_replace('/\/public$/', '', $scriptDir);
        $baseUrl = ($scriptDir === '/' || $scriptDir === '.' || empty($scriptDir)) ? '' : rtrim($scriptDir, '/');

        // Fallback: check REQUEST_URI if SCRIPT_NAME didn't capture the subfolder
        if (empty($baseUrl) && isset($_SERVER['REQUEST_URI'])) {
            $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
            if (preg_match('#^/([^/]+)#', $uriPath, $m) && $m[1] !== 'public') {
                $candidate = '/' . $m[1];
                if ((isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'] . $candidate))
                    || basename(BASE_PATH) === $m[1]) {
                    $baseUrl = $candidate;
                }
            }
        }
        define('BASE_URL', $baseUrl);
    }
}

/**
 * Helper to generate URL relative to application root
 */
function url(string $path = ''): string {
    $cleanPath = ltrim($path, '/');
    if (empty(BASE_URL)) {
        return '/' . $cleanPath;
    }
    return BASE_URL . '/' . $cleanPath;
}

/**
 * Helper to perform safe redirect relative to application root
 */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

// Autoloader for app classes and vendor
spl_autoload_register(function ($class) {
    $dirs = ['helpers', 'models', 'controllers'];
    foreach ($dirs as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load Composer vendor autoloader if present
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Safe Session Initialization for cPanel & shared hosting
if (session_status() === PHP_SESSION_NONE) {
    $sysSavePath = ini_get('session.save_path');
    if (!empty($sysSavePath) && is_dir($sysSavePath) && is_writable($sysSavePath)) {
        // Native system session path is writable and valid (recommended for cPanel)
    } else {
        if (!is_dir(SESSIONS_PATH)) {
            @mkdir(SESSIONS_PATH, 0777, true);
        }
        if (is_dir(SESSIONS_PATH) && is_writable(SESSIONS_PATH)) {
            @session_save_path(SESSIONS_PATH);
        } else {
            @session_save_path(sys_get_temp_dir());
        }
    }

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    @session_start();
}
