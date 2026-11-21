<?php
// =================================
// CAMPGO - PENGATURAN APLIKASI
// =================================

date_default_timezone_set('Asia/Jakarta');

// Alamat dasar aplikasi. Otomatis mengikuti nama folder di htdocs
// (CampGo, CampGo2, dst). Kalau pakai virtual host atau hosting di root
// domain, ganti jadi '' (kosong).
if (!defined('BASE_URL')) {
    define('BASE_URL', '/' . basename(dirname(__DIR__)));
}

// Kunci untuk kode verifikasi Digital Rental Pass. Ganti sebelum deploy.
define('APP_SECRET', 'campgo-ganti-sebelum-deploy');

// ---- Aturan bisnis (BELUM FINAL, perlu disepakati kelompok) ----
define('BOOKING_CODE_PREFIX', 'CG');   // format kode: CG-YYMMDD-XXXXX
define('DELIVERY_FEE', 0);             // biaya antar (Rp). Master Doc 9.2: belum ditentukan
define('MAX_RENTAL_DAYS', 30);         // batas durasi satu booking
define('MAX_QTY_PER_ITEM', 20);        // batas jumlah per barang/paket
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);
define('UPLOAD_DIR_PAYMENTS', dirname(__DIR__) . '/uploads/payments');

// Status booking yang MENAHAN stok. completed/cancelled/rejected melepas stok.
define('BOOKING_ACTIVE_STATUSES', ['pending_payment', 'waiting_verification', 'confirmed', 'ongoing']);
