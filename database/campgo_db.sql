-- ============================================================
-- CAMPGO - DATABASE AWAL
-- Database : campgo_db
-- Teknologi: MySQL / MariaDB (XAMPP), PHP Native, MySQLi
--
-- Acuan: Master Project Document CampGo, Bab II, Bab III, Bab IV.
-- STATUS: DRAF AWAL UNTUK DITINJAU DAN DISEPAKATI KELOMPOK.
--
-- Catatan:
-- 1. Jalankan file ini pada phpMyAdmin (tab Import) atau MySQL.
-- 2. File ini membuat struktur tabel, bukan menyinkronkan data antar laptop.
-- 3. Password akun harus disimpan menggunakan password_hash() dari PHP.
-- 4. Ketersediaan stok wajib dihitung berdasarkan periode booking aktif.
--    Jangan hanya mengurangi equipment.stock_total saat booking dibuat.
-- 5. Aturan biaya, denda, refund, dan format booking code masih perlu
--    disepakati kelompok sesuai Master Project Document.
-- ============================================================

CREATE DATABASE IF NOT EXISTS campgo_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE campgo_db;

-- File ini tidak menghapus tabel/data yang sudah ada.
-- Untuk instalasi pertama, pastikan database campgo_db masih kosong.
-- Jika sudah ada tabel dengan nama sama, tinjau/backup dahulu; IF NOT EXISTS
-- tidak akan mengubah struktur tabel lama.

-- 1. USERS: akun Customer, Staff, dan Admin
CREATE TABLE IF NOT EXISTS users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    account_status ENUM('active', 'inactive', 'blocked') NOT NULL DEFAULT 'active',
    address TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CATEGORIES: kategori perlengkapan camping
CREATE TABLE IF NOT EXISTS categories (
    category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. EQUIPMENT: barang, tarif sewa harian, dan stok total
CREATE TABLE IF NOT EXISTS equipment (
    equipment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    equipment_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    specifications TEXT NULL,
    image_path VARCHAR(255) NULL,
    price_per_day DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock_total INT UNSIGNED NOT NULL DEFAULT 0,
    stock_unavailable INT UNSIGNED NOT NULL DEFAULT 0,
    item_condition ENUM('excellent', 'good', 'fair', 'maintenance') NOT NULL DEFAULT 'good',
    item_status ENUM('available', 'inactive') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_category
      FOREIGN KEY (category_id) REFERENCES categories(category_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_equipment_category (category_id),
    INDEX idx_equipment_name (equipment_name)
) ENGINE=InnoDB;


-- STOCK_MOVEMENTS: riwayat perubahan stok perlengkapan
-- Mencatat perubahan stok total dan stok tidak tersedia.
-- Bukan untuk mencatat alokasi stok setiap booking.
CREATE TABLE IF NOT EXISTS stock_movements (
    movement_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,

    movement_type ENUM(
        'stock_added',
        'stock_removed',
        'marked_unavailable',
        'restored_available',
        'adjustment'
    ) NOT NULL,

    stock_total_before INT UNSIGNED NOT NULL,
    stock_total_after INT UNSIGNED NOT NULL,

    stock_unavailable_before INT UNSIGNED NOT NULL,
    stock_unavailable_after INT UNSIGNED NOT NULL,

    movement_notes TEXT NULL,
    changed_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_stock_movements_equipment
      FOREIGN KEY (equipment_id)
      REFERENCES equipment(equipment_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_stock_movements_user
      FOREIGN KEY (changed_by)
      REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_stock_movements_equipment (equipment_id),
    INDEX idx_stock_movements_user (changed_by),
    INDEX idx_stock_movements_created (created_at)
) ENGINE=InnoDB;

-- PACKAGES: daftar paket camping
CREATE TABLE IF NOT EXISTS packages (
    package_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    price_per_day DECIMAL(12,2) NOT NULL,
    package_status ENUM('active', 'inactive')
        NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- PACKAGE_ITEMS: daftar barang dalam paket
CREATE TABLE IF NOT EXISTS package_items (
    package_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,

    FOREIGN KEY (package_id)
        REFERENCES packages(package_id)
        ON DELETE CASCADE,

    FOREIGN KEY (equipment_id)
        REFERENCES equipment(equipment_id)
        ON DELETE RESTRICT,

    UNIQUE KEY unique_package_equipment
        (package_id, equipment_id)
) ENGINE=InnoDB;

-- 4. BOOKINGS: data utama reservasi
CREATE TABLE IF NOT EXISTS bookings (
    booking_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    booking_code VARCHAR(40) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    pickup_method ENUM('pickup_at_store', 'doorstep_delivery') NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(25) NOT NULL,
    delivery_address TEXT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    booking_status ENUM(
        'pending_payment',
        'waiting_verification',
        'confirmed',
        'ongoing',
        'completed',
        'cancelled',
        'rejected'
    ) NOT NULL DEFAULT 'pending_payment',
    customer_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user
      FOREIGN KEY (user_id) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_dates (start_date, end_date),
    INDEX idx_bookings_status (booking_status)
) ENGINE=InnoDB;


-- 5. BOOKING_PACKAGES: paket toko yang dipesan dalam booking
-- Harga paket disalin saat transaksi agar riwayat harga tetap.
CREATE TABLE IF NOT EXISTS booking_packages (
    booking_package_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    package_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    rental_days INT UNSIGNED NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,

    CONSTRAINT fk_booking_packages_booking
      FOREIGN KEY (booking_id)
      REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE CASCADE,

    CONSTRAINT fk_booking_packages_package
      FOREIGN KEY (package_id)
      REFERENCES packages(package_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_booking_packages_booking (booking_id),
    INDEX idx_booking_packages_package (package_id)
) ENGINE=InnoDB;

-- 6. BOOKING_DETAILS: barang fisik pada setiap booking
-- Barang dapat berasal dari paket toko atau disewa satuan.
-- Barang dari paket tidak ditagihkan ulang di tabel ini.
CREATE TABLE IF NOT EXISTS booking_details (
    booking_detail_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    booking_package_id BIGINT UNSIGNED NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    rental_days INT UNSIGNED NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    item_notes TEXT NULL,

    CONSTRAINT fk_booking_details_booking
      FOREIGN KEY (booking_id)
      REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE CASCADE,

    CONSTRAINT fk_booking_details_booking_package
      FOREIGN KEY (booking_package_id)
      REFERENCES booking_packages(booking_package_id)
      ON UPDATE CASCADE ON DELETE CASCADE,

    CONSTRAINT fk_booking_details_equipment
      FOREIGN KEY (equipment_id)
      REFERENCES equipment(equipment_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_booking_details_booking (booking_id),
    INDEX idx_booking_details_booking_package (booking_package_id),
    INDEX idx_booking_details_equipment (equipment_id)
) ENGINE=InnoDB;

-- HANDOVER_ITEMS: checklist penyerahan barang
CREATE TABLE IF NOT EXISTS handover_items (
    handover_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_detail_id BIGINT UNSIGNED NOT NULL,
    quantity_handed_over INT UNSIGNED NOT NULL DEFAULT 0,
    condition_at_handover ENUM(
        'excellent',
        'good',
        'fair'
    ) NOT NULL DEFAULT 'good',
    checklist_complete TINYINT(1) NOT NULL DEFAULT 0,
    handover_notes TEXT NULL,
    checked_by BIGINT UNSIGNED NULL,
    checked_at DATETIME NULL,

    FOREIGN KEY (booking_detail_id)
        REFERENCES booking_details(booking_detail_id)
        ON DELETE CASCADE,

    FOREIGN KEY (checked_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. PAYMENTS: catatan pembayaran transfer/cash
-- Satu booking dapat memiliki beberapa pembayaran.
-- Contoh: pembayaran sewa awal, perpanjangan, dan denda.
CREATE TABLE IF NOT EXISTS payments (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    extension_id BIGINT UNSIGNED NULL,
    payment_method ENUM('transfer', 'cash') NOT NULL,
    payment_type ENUM(
      'full_payment', 
      'extension',
      'penalty'
    ) NOT NULL DEFAULT 'full_payment',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('unpaid', 'waiting_verification', 'paid', 'rejected', 'refunded')
      NOT NULL DEFAULT 'unpaid',
    proof_image VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    verification_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_booking
      FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payments_verifier
      FOREIGN KEY (verified_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_payments_status (payment_status)
) ENGINE=InnoDB;

-- 8. DELIVERIES: pengantaran barang dan metode pengembalian/pickup
-- Satu catatan operasional per booking pada rancangan awal.
CREATE TABLE IF NOT EXISTS deliveries (
    delivery_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    fulfillment_method ENUM('pickup_at_store', 'doorstep_delivery') NOT NULL,
    delivery_address TEXT NULL,
    delivery_schedule DATETIME NULL,
    delivery_status ENUM('not_scheduled', 'scheduled', 'on_the_way', 'delivered', 'picked_up')
      NOT NULL DEFAULT 'not_scheduled',
    handled_by BIGINT UNSIGNED NULL,
    handover_at DATETIME NULL,
    identity_type VARCHAR(60) NULL,
    identity_received TINYINT(1) NOT NULL DEFAULT 0,
    identity_received_at DATETIME NULL,
    identity_returned_at DATETIME NULL,
    identity_notes TEXT NULL,
    handover_notes TEXT NULL,
    return_method ENUM('customer_returns_to_store', 'staff_pickup') NULL,
    return_pickup_address TEXT NULL,
    return_pickup_schedule DATETIME NULL,
    return_pickup_status ENUM('not_required', 'pending', 'scheduled', 'completed')
      NOT NULL DEFAULT 'not_required',
    return_handled_by BIGINT UNSIGNED NULL,
    return_pickup_at DATETIME NULL,
    operational_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_booking
      FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_deliveries_handler
      FOREIGN KEY (handled_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_deliveries_return_handler
      FOREIGN KEY (return_handled_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. CANCELLATIONS: pengajuan dan keputusan pembatalan
CREATE TABLE IF NOT EXISTS cancellations (
    cancellation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    requested_by BIGINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    cancellation_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    processed_by BIGINT UNSIGNED NULL,
    processed_at DATETIME NULL,
    refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    refund_status ENUM(
      'not_required',
      'pending',
      'processing',
      'completed'
    ) NOT NULL DEFAULT 'not_required',
    refunded_at DATETIME NULL,
    refund_notes TEXT NULL,
    staff_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancellations_booking
      FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cancellations_requester
      FOREIGN KEY (requested_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cancellations_processor
      FOREIGN KEY (processed_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_cancellations_status (cancellation_status)
) ENGINE=InnoDB;

-- 10. EXTENSIONS: permintaan perpanjangan masa sewa
CREATE TABLE IF NOT EXISTS extensions (
    extension_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    old_end_date DATE NOT NULL,
    requested_end_date DATE NOT NULL,
    approved_end_date DATE NULL,
    additional_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    extension_status ENUM(
      'pending', 
      'approved', 
      'rejected',
      'completed'
    ) NOT NULL DEFAULT 'pending',
    processed_by BIGINT UNSIGNED NULL,
    processed_at DATETIME NULL,
    customer_notes TEXT NULL,
    staff_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_extensions_booking
      FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_extensions_requester
      FOREIGN KEY (requested_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_extensions_processor
      FOREIGN KEY (processed_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_extensions_booking (booking_id),
    INDEX idx_extensions_status (extension_status)
) ENGINE=InnoDB;

-- RESCHEDULES: pengajuan perubahan jadwal sewa
-- Reschedule mengubah periode sewa, bukan menambah durasi.
CREATE TABLE IF NOT EXISTS reschedules (
    reschedule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,

    old_start_date DATE NOT NULL,
    old_end_date DATE NOT NULL,
    new_start_date DATE NOT NULL,
    new_end_date DATE NOT NULL,

    reschedule_status ENUM(
        'pending',
        'approved',
        'rejected'
    ) NOT NULL DEFAULT 'pending',

    customer_reason TEXT NULL,
    staff_notes TEXT NULL,

    processed_by BIGINT UNSIGNED NULL,
    processed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reschedules_booking
      FOREIGN KEY (booking_id)
      REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_reschedules_requester
      FOREIGN KEY (requested_by)
      REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_reschedules_processor
      FOREIGN KEY (processed_by)
      REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_reschedules_booking (booking_id),
    INDEX idx_reschedules_status (reschedule_status),
    INDEX idx_reschedules_created (created_at)
) ENGINE=InnoDB;
-- Menghubungkan pembayaran dengan pengajuan perpanjangan
ALTER TABLE payments
ADD CONSTRAINT fk_payments_extension
FOREIGN KEY (extension_id)
REFERENCES extensions(extension_id)
ON UPDATE CASCADE
ON DELETE RESTRICT;

-- 11. RETURNS: proses pengembalian dan pemeriksaan umum
CREATE TABLE IF NOT EXISTS returns (
    return_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    received_by BIGINT UNSIGNED NULL,
    return_date DATETIME NULL,
    return_status ENUM('not_returned', 'inspection', 'completed') NOT NULL DEFAULT 'not_returned',
    general_condition ENUM('good', 'damaged', 'incomplete', 'lost') NULL,
    late_days INT UNSIGNED NOT NULL DEFAULT 0,
    late_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    inspection_notes TEXT NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_returns_booking
      FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_returns_receiver
      FOREIGN KEY (received_by) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_returns_status (return_status)
) ENGINE=InnoDB;

-- 12. RETURN_DETAILS: checklist kondisi setiap jenis barang yang dikembalikan
CREATE TABLE IF NOT EXISTS return_details (
    return_detail_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    quantity_returned INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_good INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_damaged INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_lost INT UNSIGNED NOT NULL DEFAULT 0,
    checklist_complete TINYINT(1) NOT NULL DEFAULT 0,
    condition_notes TEXT NULL,
    damage_description TEXT NULL,
    replacement_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_return_details_return
      FOREIGN KEY (return_id) REFERENCES returns(return_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_return_details_equipment
      FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_return_details_return (return_id),
    INDEX idx_return_details_equipment (equipment_id)
) ENGINE=InnoDB;

-- 13. ARTICLES: artikel dan tips camping
CREATE TABLE IF NOT EXISTS articles (
    article_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    article_status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_author
      FOREIGN KEY (author_id) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_articles_status (article_status)
) ENGINE=InnoDB;

-- 14. ACTIVITY_LOGS: catatan aktivitas Staff/Admin
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    target_table VARCHAR(100) NULL,
    target_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user
      FOREIGN KEY (user_id) REFERENCES users(user_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_activity_logs_user (user_id),
    INDEX idx_activity_logs_created (created_at),
    INDEX idx_activity_logs_target (target_table, target_id)
) ENGINE=InnoDB;

-- 15. CONTACTS: kontak WhatsApp, media sosial, dan informasi toko
CREATE TABLE IF NOT EXISTS contacts (
    contact_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_type ENUM('whatsapp', 'instagram', 'tiktok', 'email', 'address', 'other') NOT NULL,
    label VARCHAR(100) NOT NULL,
    contact_value TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- AKHIR STRUKTUR DATABASE CAMPGO
--
-- Belum ada akun demo atau data barang otomatis yang dimasukkan.
-- Buat akun melalui PHP dengan password_hash(), bukan password teks biasa.
--
-- PENGINGAT PENGEMBANG:
-- - Validasi start_date <= end_date di PHP.
-- - Cek stok untuk rentang tanggal terhadap booking yang masih aktif.
-- - Saat pembatalan disetujui, booking tidak lagi dihitung sebagai alokasi.
-- - Saat perpanjangan disetujui, cek ulang stok untuk tanggal tambahan.
-- - Gunakan transaksi SQL (BEGIN/COMMIT/ROLLBACK) saat membuat booking
--   agar dua pelanggan tidak memesan unit terakhir secara bersamaan.
-- - Tinjau kembali apakah aturan satu pembayaran, satu delivery, satu
--   cancellation, dan satu return per booking sesuai keputusan kelompok.
-- ============================================================
