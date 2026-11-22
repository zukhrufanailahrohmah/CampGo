<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

requireRole('customer');
require_post_csrf('customer/booking/form.php');

try {
    $hasil = buatBooking($conn, currentUserId(), $_POST);
} catch (BookingException $e) {
    flash('error', $e->getMessage());
    set_old($_POST);
    redirect('customer/booking/form.php');
} catch (Throwable $e) {
    error_log('buatBooking gagal: ' . $e->getMessage());
    flash('error', 'Terjadi kesalahan saat menyimpan reservasi. Silakan coba lagi.');
    set_old($_POST);
    redirect('customer/booking/form.php');
}

flash('success', 'Reservasi berhasil dibuat. Kode booking kamu: ' . $hasil['booking_code']);
redirect('customer/history/detail.php?code=' . urlencode($hasil['booking_code']));
