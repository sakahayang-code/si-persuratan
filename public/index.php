<?php
// SI-PERSURATAN - Public Entry Point
// Serve index.html directly if available

if (file_exists(__DIR__ . '/index.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/index.html');
    exit;
}

if (file_exists(__DIR__ . '/../index.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/../index.html');
    exit;
}

header('Location: index.html');
exit;
