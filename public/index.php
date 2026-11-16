<?php
// persuratan/public/index.php

require_once __DIR__ . '/../config/app.php';

if (Auth::check()) {
    header('Location: ' . url('dashboard.php'));
} else {
    header('Location: ' . url('login.php'));
}
exit;
