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

// Autoloader for app classes and vendor
spl_autoload_register(function ($class) {
    // Check in app/helpers
    $file = APP_PATH . '/helpers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    // Check in app/models
    $file = APP_PATH . '/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    // Check in app/controllers
    $file = APP_PATH . '/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
});

// Load Composer vendor autoloader if present
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Session security configuration
if (session_status() === PHP_SESSION_NONE) {
    if (!is_dir(SESSIONS_PATH)) {
        @mkdir(SESSIONS_PATH, 0755, true);
    }
    session_save_path(SESSIONS_PATH);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
