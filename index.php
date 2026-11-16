<?php
// persuratan/index.php
// Entry router forwarding requests to public/

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = dirname($scriptName);
$dir = str_replace('\\', '/', $dir);
$baseUrl = ($dir === '/' || $dir === '.' || empty($dir)) ? '' : rtrim($dir, '/');

// Forward to public directory cleanly
header('Location: ' . $baseUrl . '/public/');
exit;
