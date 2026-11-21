<?php
// Session aman. Dipakai semua halaman (termasuk layout).
require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    // Nama session dibedakan per folder supaya CampGo dan CampGo2 tidak saling bentrok
    session_name('campgo_' . preg_replace('/\W/', '', basename(dirname(__DIR__))));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL === '' ? '/' : BASE_URL,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
