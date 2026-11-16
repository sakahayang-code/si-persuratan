<?php
// persuratan/index.php
// Main entry point - forwards to public/

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = dirname($scriptName);
$dir = str_replace('\\', '/', $dir);
$baseUrl = ($dir === '/' || $dir === '.' || empty($dir)) ? '' : rtrim($dir, '/');

header('Location: ' . $baseUrl . '/public/');
exit;
