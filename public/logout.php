<?php
// persuratan/public/logout.php

require_once __DIR__ . '/../config/app.php';

Auth::logout();
Flash::set('info', 'Anda telah berhasil keluar dari aplikasi.');
header('Location: /login.php');
exit;
