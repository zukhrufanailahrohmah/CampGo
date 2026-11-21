<?php
require_once __DIR__ . '/../includes/session.php';

// Menghapus semua data session
session_unset();

// Menghancurkan session
session_destroy();

// Kembali ke halaman login
header('Location: ' . BASE_URL . '/customer/login.php');
exit;
