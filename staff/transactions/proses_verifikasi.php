<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/payment_service.php';

requireRole('staff');

$kode = trim($_POST['booking_code'] ?? '');
$kembali = 'staff/transactions/detail.php?code=' . urlencode($kode);
require_post_csrf($kembali);

try {
    $pesan = verifikasiPembayaran($conn, (int)($_POST['payment_id'] ?? 0), currentUserId(), $_POST['aksi'] ?? '', $_POST['catatan'] ?? '');
    flash('success', $pesan);
} catch (BookingException $e) {
    flash('error', $e->getMessage());
} catch (Throwable $e) {
    error_log('verifikasi gagal: ' . $e->getMessage());
    flash('error', 'Terjadi kesalahan saat memproses pembayaran.');
}

redirect($kembali);
