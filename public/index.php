<?php
// persuratan/public/index.php

require_once __DIR__ . '/../config/app.php';

if (Auth::check()) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;
