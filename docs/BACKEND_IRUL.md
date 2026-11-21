# Backend 2 (Irul): Akun, Booking, Pembayaran, Rental Pass

Dokumen kontrak input/output untuk Frontend 1/2 (Naila, Nisa) dan Backend 1 (Adel).
Sesuai Master Document 5.3: perubahan kontrak diinformasikan ke semua anggota sebelum digabungkan.

## Cara menjalankan (lokal)
1. XAMPP: Apache + MySQL aktif, **PHP 8.0 atau lebih baru** (kode memakai `match` dan `fn`).
2. Import `database/campgo_db.sql` lewat phpMyAdmin.
3. Jalankan seed: buka `http://localhost/CampGo/database/seed_demo.php` (atau `php database/seed_demo.php`).
   Membuat akun demo (Admin#2026 / Staff#2026 / User#2026) dan barang contoh. Aman diulang.
4. Folder boleh bernama apa saja (CampGo, CampGo2): `BASE_URL` di `config/app.php` mengikuti nama folder.

## File baru / berubah
| File | Isi |
|---|---|
| `config/app.php` | BASE_URL, aturan bisnis (biaya antar, batas durasi, dll) |
| `includes/session.php` | session aman (httponly, SameSite, nama session per folder) |
| `includes/functions.php` | `h()`, `url()`, `redirect()`, flash, CSRF, helper DB (`dbFetchAll/One/Execute/Insert`), `logActivity()` |
| `includes/auth.php` | `requireLogin()`, `requireRole()` (string/array; Admin otomatis boleh halaman Staff) |
| `includes/booking_service.php` | `cekKetersediaan()`, `susunRincian()`, `buatBooking()`, `riwayatBooking()`, `detailBooking()`, `ambilPass()` |
| `includes/payment_service.php` | `simpanBuktiTransfer()`, `verifikasiPembayaran()` |
| `customer/*`, `customer/booking/*`, `customer/history/*` | halaman + handler (tampilan sederhana, boleh diganti Frontend) |
| `staff/transactions/*` | antrean, detail, verifikasi pembayaran, lihat bukti |
| `database/seed_demo.php`, `database/campgo_db.sql` | seed dan salinan SQL |

Semua query memakai prepared statement. Semua form POST wajib `csrf_field()`.

## Kontrak handler (untuk Frontend)
Semua handler: POST, field `csrf_token` (pakai `<?= csrf_field(); ?>`), hasilnya redirect + flash message (`flash_render()` untuk menampilkan).

| Handler | Field POST | Sukses -> |
|---|---|---|
| `customer/proses_register.php` | full_name, email, phone, password (8-72), address | login.php |
| `customer/proses_login.php` | email, password | dashboard sesuai role / halaman yang tadi dituju |
| `customer/proses_profile.php` | full_name, phone, address | profile.php |
| `customer/proses_ganti_password.php` | password_lama, password_baru, password_ulang | profile.php |
| `customer/booking/proses_booking.php` | start_date, end_date (Y-m-d), pickup_method (`pickup_at_store`/`doorstep_delivery`), payment_method (`transfer`/`cash`), customer_name, customer_phone, delivery_address (wajib bila delivery), customer_notes, `items[equipment_id]=qty`, `packages[package_id]=qty` | history/detail.php?code=... |
| `customer/booking/proses_upload_bukti.php` | payment_id, booking_code, file `proof` (JPG/PNG/WEBP, maks 2 MB) | history/detail.php |
| `staff/transactions/proses_verifikasi.php` | payment_id, booking_code, aksi (`setuju`/`tolak`), catatan (wajib bila tolak) | detail.php |

**Endpoint JSON** `GET customer/booking/cek_stok.php?start_date=&end_date=&pickup_method=&items[id]=qty&packages[id]=qty`
-> `{success, ok, hari, subtotal, delivery_fee, total, barang:[{equipment_id,nama,tersedia,dibutuhkan,cukup}]}`; error 422 `{success:false,message}`.

**Digital Rental Pass**: `customer/pass.php` (tamu: kode booking + email/telepon; customer login: `?code=`). Hanya booking `confirmed`/`ongoing`.

## Aturan yang dipakai (perlu disepakati, lihat Master Doc 9.2)
1. **Hari sewa inklusif**: 10 s/d 12 = 3 hari (`hitungHariSewa()`).
2. **Stok per tanggal**: sisa = (`stock_total` - `stock_unavailable`) - *puncak pemakaian harian* di periode itu. Status yang menahan stok: `pending_payment`, `waiting_verification`, `confirmed`, `ongoing` (`BOOKING_ACTIVE_STATUSES`). Isi paket ikut dihitung; paket + barang satuan yang sama dijumlah.
3. **Anti rebutan stok**: `buatBooking()` memakai transaksi + `SELECT ... FOR UPDATE` pada baris equipment.
4. **Kode booking**: `CG-YYMMDD-XXXXX` (tanpa huruf O/I dan angka 0/1).
5. **Status awal**: transfer = `pending_payment` (menunggu bukti); tunai = `waiting_verification` (langsung antre Staff).
6. **Verifikasi**: setuju -> payment `paid` + booking `confirmed`. Tolak (hanya transfer) -> payment `rejected` + booking kembali `pending_payment` agar customer unggah ulang.
7. **Biaya antar** `DELIVERY_FEE` = 0 sampai kelompok menentukan.
8. Harga selalu dari database (harga di form diabaikan) dan disalin ke `booking_*` saat transaksi.

## Hal yang menyentuh modul Backend 1 (Adel)
- `buatBooking()` langsung membuat 1 baris `deliveries` (status `not_scheduled`) dan 1 baris `payments`. Adel tinggal UPDATE, bukan INSERT.
- `cekKetersediaan($conn, $needs, $start, $end, $excludeBookingId)` bisa dipakai untuk validasi perpanjangan/reschedule (isi `$excludeBookingId` dengan booking yang sedang diubah).
- `logActivity()` dipakai bersama untuk `activity_logs`. Saat ini baru mencatat `payment_verified` dan `payment_rejected`.
- Peralihan status `confirmed` -> `ongoing` -> `completed` (serah terima/pengembalian) belum ada; itu bagian Adel.

## Catatan temuan di SQL / repo (untuk Adel & Naila)
- `cancellations.booking_id` UNIQUE: pembatalan yang ditolak tidak bisa diajukan lagi. Cek apakah memang itu maunya.
- Booking `ongoing` yang lewat `end_date` tapi belum dikembalikan tidak lagi menahan stok di hitungan tanggal. Perlu aturan keterlambatan di modul return.
- `pending_payment` tidak punya masa kedaluwarsa, jadi booking transfer yang tidak dibayar menahan stok selamanya sampai dibatalkan manual.
- `style.css` belum mendefinisikan `.container`, padahal homepage memakainya.
- `navbar`/footer menaut ke `customer/katalog.php` dan `customer/artikel.php` yang belum ada (404).
- Master Doc menyebut `config/koneksi.php`, repo memakai `config/database.php`. Disamakan dengan `database.php`.

## Yang belum dikerjakan (di luar jatah / butuh keputusan)
- Nomor rekening, WhatsApp, alamat toko, besaran biaya antar, denda keterlambatan, kebijakan refund.
- Pengajuan cancel/extension/reschedule (Adel), tampilan final (Naila/Nisa), deployment.
- Upload bukti di hosting: pastikan folder `uploads/payments/` dapat ditulis. `.htaccess`-nya memblokir akses langsung; bukti hanya bisa dilihat Staff lewat `staff/transactions/bukti.php`.
