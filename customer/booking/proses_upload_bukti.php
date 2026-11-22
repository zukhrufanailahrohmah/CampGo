<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/payment_service.php';

requireRole('customer');

$kode = trim($_POST['booking_code'] ?? '');
$kembali = 'customer/history/detail.php?code=' . urlencode($kode);
require_post_csrf($kembali);

try {
    simpanBuktiTransfer($conn, (int)($_POST['payment_id'] ?? 0), currentUserId(), $_FILES['proof'] ?? []);
    flash('success', 'Bukti pembayaran terkirim. Staff akan memverifikasi pembayaranmu.');
} catch (BookingException $e) {
    flash('error', $e->getMessage());
} catch (Throwable $e) {
    error_log('upload bukti gagal: ' . $e->getMessage());
    flash('error', 'Upload gagal. Silakan coba lagi.');
}

redirect($kembali);
