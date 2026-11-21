<?php
// =================================
// CAMPGO - PEMBAYARAN (transfer / cash, dummy sesuai batasan UAS)
// =================================
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/booking_service.php';

// Customer mengunggah bukti transfer
function simpanBuktiTransfer(mysqli $conn, int $paymentId, int $userId, array $file): void
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new BookingException('File tidak valid.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new BookingException('Pilih file bukti pembayaran terlebih dahulu.');
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > MAX_UPLOAD_BYTES) {
        throw new BookingException('Ukuran file maksimal ' . (MAX_UPLOAD_BYTES / 1024 / 1024) . ' MB.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new BookingException('Upload gagal. Silakan coba lagi.');
    }

    // Tipe file diperiksa dari ISI file, bukan dari nama/ekstensi
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ekstensi === null || @getimagesize($file['tmp_name']) === false) {
        throw new BookingException('Bukti pembayaran harus berupa gambar JPG, PNG, atau WEBP.');
    }

    if (!is_dir(UPLOAD_DIR_PAYMENTS) && !mkdir(UPLOAD_DIR_PAYMENTS, 0775, true)) {
        throw new RuntimeException('Folder upload tidak bisa dibuat.');
    }

    mysqli_begin_transaction($conn);
    $namaBaru = null;
    try {
        $p = dbFetchOne(
            $conn,
            'SELECT p.payment_id, p.payment_method, p.payment_status, p.proof_image, b.booking_id, b.user_id, b.booking_status
             FROM payments p JOIN bookings b ON b.booking_id = p.booking_id
             WHERE p.payment_id = ? FOR UPDATE',
            'i',
            [$paymentId]
        );
        if (!$p || (int)$p['user_id'] !== $userId) {
            throw new BookingException('Pembayaran tidak ditemukan.');
        }
        if ($p['payment_method'] !== 'transfer') {
            throw new BookingException('Pembayaran ini bukan transfer bank.');
        }
        if (!in_array($p['payment_status'], ['unpaid', 'rejected'], true) || $p['booking_status'] !== 'pending_payment') {
            throw new BookingException('Bukti pembayaran tidak dapat diunggah untuk status booking saat ini.');
        }

        $namaBaru = 'bukti_' . $p['booking_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ekstensi;
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR_PAYMENTS . '/' . $namaBaru)) {
            throw new RuntimeException('File gagal disimpan.');
        }

        dbExecute($conn, "UPDATE payments SET proof_image = ?, payment_status = 'waiting_verification', paid_at = NOW(),
                          verified_by = NULL, verified_at = NULL WHERE payment_id = ?", 'si', [$namaBaru, $paymentId]);
        dbExecute($conn, "UPDATE bookings SET booking_status = 'waiting_verification' WHERE booking_id = ?", 'i', [$p['booking_id']]);

        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        if ($namaBaru !== null) {
            @unlink(UPLOAD_DIR_PAYMENTS . '/' . $namaBaru);
        }
        throw $e;
    }

    // Bukti lama (kalau ini upload ulang setelah ditolak) dihapus
    if (!empty($p['proof_image'])) {
        @unlink(UPLOAD_DIR_PAYMENTS . '/' . basename($p['proof_image']));
    }
}

// Staff/Admin memverifikasi pembayaran.
//   $aksi = 'setuju' -> payment paid, booking confirmed (berlaku untuk transfer & tunai)
//   $aksi = 'tolak'  -> hanya untuk transfer yang sudah ada bukti; booking kembali pending_payment
function verifikasiPembayaran(mysqli $conn, int $paymentId, int $staffId, string $aksi, string $catatan = ''): string
{
    if (!in_array($aksi, ['setuju', 'tolak'], true)) {
        throw new BookingException('Aksi tidak dikenal.');
    }
    $catatan = trim($catatan);
    if ($aksi === 'tolak' && $catatan === '') {
        throw new BookingException('Alasan penolakan wajib diisi supaya customer tahu apa yang harus diperbaiki.');
    }

    mysqli_begin_transaction($conn);
    try {
        $p = dbFetchOne(
            $conn,
            'SELECT p.payment_id, p.payment_method, p.payment_status, p.payment_type, b.booking_id, b.booking_code, b.booking_status
             FROM payments p JOIN bookings b ON b.booking_id = p.booking_id
             WHERE p.payment_id = ? FOR UPDATE',
            'i',
            [$paymentId]
        );
        if (!$p) {
            throw new BookingException('Pembayaran tidak ditemukan.');
        }
        if ($p['payment_type'] !== 'full_payment') {
            throw new BookingException('Pembayaran perpanjangan/denda diproses lewat modul masing-masing.');
        }
        if (!in_array($p['booking_status'], ['pending_payment', 'waiting_verification'], true)) {
            throw new BookingException('Booking berstatus "' . label_status('booking', $p['booking_status']) . '", tidak bisa diverifikasi.');
        }
        if (!in_array($p['payment_status'], ['unpaid', 'waiting_verification'], true)) {
            throw new BookingException('Pembayaran ini sudah diproses.');
        }

        if ($aksi === 'setuju') {
            if ($p['payment_method'] === 'transfer' && $p['payment_status'] !== 'waiting_verification') {
                throw new BookingException('Customer belum mengunggah bukti transfer.');
            }
            dbExecute($conn, "UPDATE payments SET payment_status = 'paid', paid_at = COALESCE(paid_at, NOW()),
                              verified_by = ?, verified_at = NOW(), verification_notes = ? WHERE payment_id = ?",
                'isi', [$staffId, $catatan === '' ? null : $catatan, $paymentId]);
            dbExecute($conn, "UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?", 'i', [$p['booking_id']]);
            $pesan = 'Pembayaran diverifikasi, booking ' . $p['booking_code'] . ' terkonfirmasi.';
            $log = 'payment_verified';
        } else {
            if ($p['payment_method'] !== 'transfer' || $p['payment_status'] !== 'waiting_verification') {
                throw new BookingException('Hanya bukti transfer yang menunggu verifikasi yang bisa ditolak.');
            }
            dbExecute($conn, "UPDATE payments SET payment_status = 'rejected', verified_by = ?, verified_at = NOW(),
                              verification_notes = ? WHERE payment_id = ?", 'isi', [$staffId, $catatan, $paymentId]);
            dbExecute($conn, "UPDATE bookings SET booking_status = 'pending_payment' WHERE booking_id = ?", 'i', [$p['booking_id']]);
            $pesan = 'Bukti ditolak, customer diminta mengunggah ulang.';
            $log = 'payment_rejected';
        }

        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    logActivity($conn, $staffId, $log, 'payments', $paymentId, $p['booking_code'] . ($catatan !== '' ? ': ' . $catatan : ''));
    return $pesan;
}
