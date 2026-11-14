# MASTER PROJECT DOCUMENT
# CAMPGO – Aplikasi Manajemen Persewaan Alat Camping

**Dokumen Induk Proyek UAS Pemrograman Web 2026/2027**

| Informasi | Keterangan |
|---|---|
| Nama proyek | CampGo |
| Jenis aplikasi | Full-stack Web Application |
| Kategori | Persewaan alat camping |
| Kelas | A |
| Anggota | Naila, Nisa, Mba Irul, Adel |
| Bahasa pemrograman | PHP Native |
| Database | MySQL |
| Repository | Akan ditentukan |
| Status proyek | Perencanaan dan persiapan pengerjaan |

**Fungsi dokumen:** Menjadi sumber acuan utama seluruh konsep, fitur, alur bisnis, rancangan database, pembagian pekerjaan, dan perkembangan proyek CampGo.

---

# BAB I. GAMBARAN UMUM PROYEK

## 1.1 Latar Belakang

Kegiatan camping semakin diminati oleh masyarakat, tetapi tidak semua orang memiliki perlengkapan camping sendiri. Membeli perlengkapan camping membutuhkan biaya yang cukup besar, terutama jika hanya digunakan sesekali.

Di sisi lain, proses penyewaan perlengkapan camping secara konvensional sering kali masih mengharuskan pelanggan datang langsung ke toko untuk melihat barang, memastikan ketersediaan stok, melakukan pemesanan, dan mengambil perlengkapan.

CampGo hadir sebagai solusi digital yang memudahkan pelanggan dalam mencari, memilih, memesan, dan menyewa perlengkapan camping melalui website.

CampGo tidak hanya menyediakan katalog perlengkapan, tetapi juga menawarkan sistem reservasi berdasarkan tanggal, layanan antar-jemput oleh staf toko, pembayaran fleksibel, perpanjangan masa sewa, serta pengelolaan transaksi dan inventaris secara terintegrasi.

## 1.2 Tujuan Aplikasi

1. Memudahkan pelanggan melakukan penyewaan alat camping secara online.
2. Memberikan informasi perlengkapan, harga, stok, dan ketersediaan barang.
3. Mempermudah proses reservasi berdasarkan tanggal penyewaan.
4. Menyediakan pilihan pengambilan barang di toko atau pengantaran oleh staf.
5. Memudahkan proses pengembalian dan pemeriksaan kondisi barang.
6. Membantu staf mengelola transaksi dan operasional persewaan.
7. Membantu admin mengelola akun, inventaris, artikel, serta laporan.
8. Meningkatkan efisiensi pengelolaan usaha persewaan alat camping.

## 1.3 Identitas dan Konsep Brand

**Nama:** CampGo

**Konsep:** Online Camping Equipment Rental.

**Karakter brand:**
- Outdoor dan adventure.
- Modern dan praktis.
- Ramah bagi pemula maupun pecinta camping.
- Terpercaya dan mudah digunakan.

**Slogan sementara:** "Your Adventure Starts Here."

Slogan dan identitas visual masih dapat disesuaikan.

## 1.4 Referensi Website

Website referensi:

https://www.forestaadventure.com/

Website tersebut menjadi referensi awal untuk memahami penyajian layanan outdoor, katalog, informasi produk, dan pendekatan promosi.

CampGo tidak dimaksudkan untuk menyalin website referensi, tetapi mengembangkan konsep persewaan yang memiliki karakter dan fitur tersendiri.

Pembeda utama CampGo:
- Pemesanan berdasarkan rentang tanggal dan validasi stok.
- Smart Camping Planner.
- Custom paket camping.
- Layanan antar-jemput oleh staf toko.
- Pemeriksaan barang dan jaminan identitas.
- Perpanjangan masa sewa secara online.
- Digital Rental Pass dan checklist pengembalian.

---

# BAB II. KONSEP DAN FITUR APLIKASI

CampGo memiliki tiga jenis pengguna:

1. Customer (pelanggan).
2. Staff (petugas operasional).
3. Admin (pengelola sistem).

## 2.1 Fitur Customer / Pengunjung

### A. Homepage

Halaman utama berisi:
- Ringkasan tentang CampGo.
- Banner promosi.
- Rekomendasi perlengkapan camping.
- Kategori perlengkapan.
- Informasi paket sewa.
- Cara melakukan reservasi.
- Keunggulan layanan.
- Artikel dan tips camping.
- Kontak WhatsApp dan media sosial.

### B. Katalog Perlengkapan

Customer dapat:
- Melihat daftar perlengkapan.
- Melihat foto dan deskripsi barang.
- Melihat harga sewa per hari.
- Melihat kategori barang.
- Melihat stok atau ketersediaan.
- Melihat detail kondisi dan spesifikasi barang.

Contoh kategori:
- Tenda.
- Sleeping bag.
- Matras.
- Kompor portable.
- Peralatan masak.
- Penerangan.
- Carrier.
- Perlengkapan pendukung lainnya.

### C. Smart Camping Planner

Fitur bantuan bagi pelanggan yang belum memahami perlengkapan apa saja yang dibutuhkan.

Customer dapat memilih kebutuhan berdasarkan:
- Jumlah peserta.
- Durasi camping.
- Jenis kegiatan.
- Tingkat pengalaman camping.

Sistem memberikan rekomendasi perlengkapan yang sesuai.

Fitur ini menggunakan aturan rekomendasi yang ditentukan dalam program, bukan AI eksternal yang wajib terhubung.

### D. Custom Camping Package

Customer dapat menyusun paket camping sendiri dengan memilih beberapa perlengkapan.

Sistem menghitung total harga berdasarkan:
- Barang yang dipilih.
- Jumlah unit.
- Lama penyewaan.
- Harga sewa masing-masing barang.

### E. Reservasi Online

Customer dapat:
- Memilih barang atau paket.
- Menentukan tanggal mulai sewa.
- Menentukan tanggal selesai sewa.
- Menentukan jumlah unit.
- Mengisi data kontak.
- Memilih metode pengambilan barang.
- Melihat rincian biaya.
- Mengirim permintaan reservasi.

Sistem wajib memeriksa ketersediaan stok berdasarkan rentang tanggal.

Pemesanan tidak boleh menyebabkan jumlah barang yang disewa melebihi stok yang tersedia.

### F. Metode Pengambilan Barang (Fitur Pembeda)

Customer memiliki dua pilihan:

**1. Pickup at Store**

Customer datang langsung ke toko untuk mengambil perlengkapan.

Proses:
- Customer menerima informasi booking.
- Customer datang sesuai jadwal.
- Staff memeriksa identitas pelanggan.
- Customer menyerahkan kartu identitas sebagai jaminan.
- Staff memeriksa kelengkapan dan kondisi barang.
- Barang diserahkan kepada customer.
- Data serah terima dicatat.

**2. Doorstep Delivery by CampGo Staff**

Barang diantarkan langsung oleh staf toko ke alamat pelanggan.

Fitur ini bukan menggunakan jasa ojek online atau kurir eksternal.

Proses:
- Customer memilih layanan delivery.
- Customer memasukkan alamat pengantaran.
- Sistem menampilkan biaya pengantaran apabila ada.
- Staff toko menyiapkan perlengkapan.
- Staff mengantarkan barang ke alamat customer.
- Staff memeriksa identitas pelanggan.
- Customer menyerahkan kartu identitas sebagai jaminan.
- Staff memeriksa kondisi dan kelengkapan barang.
- Kedua pihak melakukan konfirmasi serah terima.

Ketentuan penting:

Kartu identitas disimpan sebagai jaminan selama masa penyewaan dan harus dikelola secara aman oleh petugas. Website hanya mencatat status dan informasi administrasi yang diperlukan, bukan menyimpan foto identitas secara terbuka.

### G. Pembayaran

CampGo menyediakan dua metode pembayaran:

**1. Transfer Bank**

- Customer melakukan transfer ke rekening yang ditentukan.
- Customer mengunggah bukti pembayaran.
- Staff memeriksa bukti transfer.
- Status pembayaran diperbarui setelah verifikasi.

**2. Cash / Tunai**

- Customer memilih pembayaran tunai.
- Pembayaran dilakukan sesuai prosedur pengambilan barang atau ketentuan transaksi.
- Staff mencatat penerimaan uang.
- Status pembayaran diperbarui.

Pembayaran menggunakan sistem dummy/sandbox sesuai batasan tugas UAS. Tidak wajib terintegrasi dengan payment gateway sungguhan.

### H. Verifikasi Booking dan Digital Rental Pass

Customer dapat memasukkan:
- Kode booking.
- Email atau nomor telepon.

Jika booking terverifikasi dan berstatus confirmed, sistem menampilkan kartu pengambilan barang.

Kartu memuat:
- Kode booking.
- Nama customer.
- Daftar perlengkapan.
- Jumlah barang.
- Tanggal penyewaan.
- Jadwal pengambilan.
- Metode pengambilan.
- QR dummy atau kode verifikasi.
- Checklist serah terima.

### I. Rental Extension (Perpanjangan Sewa)

Customer dapat mengajukan perpanjangan masa sewa tanpa harus datang ke toko.

Proses:
1. Customer membuka riwayat sewa.
2. Memilih transaksi yang ingin diperpanjang.
3. Memasukkan tanggal pengembalian baru.
4. Sistem memeriksa ketersediaan barang pada periode tambahan.
5. Sistem menghitung tambahan biaya sewa.
6. Customer mengirim permintaan perpanjangan.
7. Staff memeriksa dan menerima atau menolak permintaan.
8. Jika disetujui, tanggal pengembalian dan biaya transaksi diperbarui.

Perpanjangan tidak otomatis disetujui jika stok atau jadwal berikutnya tidak memungkinkan.

### J. Pengembalian Barang

Customer memiliki dua pilihan pengembalian:

**1. Return to Store**

Customer membawa kembali barang ke toko.

**2. Staff Pickup**

Staff toko datang ke alamat customer untuk mengambil barang.

Pada proses pengembalian:
- Staff memeriksa jumlah barang.
- Staff memeriksa kelengkapan aksesori.
- Staff memeriksa kondisi barang.
- Customer dan staff melakukan konfirmasi pengembalian.
- Kartu identitas dikembalikan setelah proses pemeriksaan selesai.
- Status pengembalian dicatat dalam sistem.

### K. Checklist Pengembalian

Sistem menyediakan checklist untuk memudahkan pemeriksaan.

Contoh:
- Tenda lengkap.
- Tiang tenda lengkap.
- Pasak lengkap.
- Sleeping bag lengkap.
- Matras lengkap.
- Kompor lengkap.
- Aksesori tambahan lengkap.

### L. Kerusakan dan Kehilangan

Jika ditemukan kerusakan atau kehilangan:
- Staff mencatat barang yang bermasalah.
- Staff melampirkan keterangan kondisi.
- Admin dapat melakukan pemeriksaan dan menentukan tindak lanjut.
- Customer mendapatkan informasi terkait kewajiban penggantian.
- Biaya penggantian atau denda dicatat dalam administrasi.

### M. Pembatalan Reservasi

Customer dapat mengajukan pembatalan dengan mengisi alasan.

Status pembatalan:
- Pending.
- Approved.
- Rejected.

Customer dapat melihat status pengajuan melalui profil.

Jika pembatalan disetujui:
- Status booking menjadi cancelled.
- Stok barang dilepas kembali.
- Staff mencatat administrasi refund atau deposit.
- Status pengembalian dana dicatat, tanpa harus melakukan transfer refund sungguhan.

### N. Artikel dan Edukasi Camping

Website menyediakan minimal tiga artikel.

Contoh:
1. Tips Memilih Tenda untuk Pemula.
2. Checklist Perlengkapan Camping.
3. Panduan Menyiapkan Camping Pertama Kali.

Artikel dapat dibaca oleh pengunjung dan dikelola oleh Staff/Admin sesuai hak akses.

### O. Akun dan Profil Customer

Customer dapat:
- Register dengan email dan password.
- Login.
- Logout.
- Melihat profil.
- Mengedit data pribadi.
- Melihat riwayat sewa.
- Melihat status booking.
- Melihat status pembayaran.
- Melihat status pembatalan.
- Melihat status perpanjangan.

### P. Kontak dan Media Sosial

Website menyediakan:
- Tombol WhatsApp.
- Instagram.
- TikTok atau media sosial lain.
- Informasi alamat toko.
- Email kontak.

Nomor WhatsApp, username media sosial, dan alamat resmi akan diisi setelah kelompok menentukan identitas kontak CampGo.

### Q. Grafik Informasi

Sistem menyediakan visualisasi:
- Jumlah penyewaan per periode.
- Jumlah pembatalan.
- Tingkat pemakaian stok barang.
- Statistik transaksi.

---

## 2.2 Fitur Staff

Staff memiliki akses operasional untuk membantu pengelolaan persewaan.

### A. Dashboard Staff

Menampilkan:
- Jumlah penyewaan hari ini.
- Booking yang sedang berlangsung.
- Pembatalan menunggu persetujuan.
- Permintaan perpanjangan.
- Pengantaran dan pengambilan barang.
- Pendapatan dummy.
- Tabel transaksi terbaru.

### B. Manajemen Transaksi

Staff dapat:
- Melihat daftar booking.
- Memeriksa detail booking.
- Memverifikasi pembayaran.
- Memproses reservasi.
- Memperbarui status transaksi.
- Melihat riwayat transaksi.

### C. Manajemen Pembatalan

Staff dapat menerima atau menolak pengajuan pembatalan.

Jika disetujui:
- Booking menjadi cancelled.
- Stok dilepas.
- Catatan refund/deposit disimpan.
- Riwayat tindakan tercatat.

### D. Manajemen Perpanjangan

Staff memeriksa permintaan perpanjangan dan menentukan persetujuan berdasarkan ketersediaan barang.

### E. Operasional Antar-Jemput

Staff dapat melihat:
- Daftar pengantaran.
- Alamat customer.
- Jadwal pengantaran.
- Daftar barang.
- Daftar pengambilan kembali.
- Status serah terima.

### F. Pemeriksaan Pengembalian

Staff dapat mengisi checklist dan mencatat kerusakan atau kehilangan barang.

### G. CRUD Artikel

Staff dapat:
- Menambah artikel.
- Melihat artikel.
- Mengedit artikel.
- Menghapus artikel.

### H. CRUD Perlengkapan

Staff dapat mengelola data barang, kategori, stok, dan tarif sesuai hak akses operasional yang ditentukan.

---

## 2.3 Fitur Admin

Admin memiliki seluruh akses operasional Staff dengan tambahan pengelolaan sistem.

### A. Dashboard Admin

Menampilkan statistik:
- Jumlah pengguna.
- Jumlah Staff.
- Jumlah perlengkapan.
- Jumlah transaksi.
- Pendapatan dummy.
- Pembatalan.
- Grafik penyewaan.

### B. Manajemen Akun

Admin dapat:
- Melihat akun Customer.
- Menambah akun Staff.
- Mengedit akun.
- Menghapus atau menonaktifkan akun.
- Mengatur role.
- Mengelola akun Admin lainnya.

### C. Manajemen Inventaris

Admin dapat:
- Mengelola perlengkapan.
- Mengelola kategori.
- Mengelola stok total.
- Mengelola harga sewa.
- Memperbarui kondisi barang.
- Melihat laporan kerusakan dan kehilangan.

### D. Catatan Aktivitas Staff

Sistem mencatat aktivitas penting Staff, seperti:
- Login.
- Verifikasi pembayaran.
- Persetujuan pembatalan.
- Persetujuan perpanjangan.
- Serah terima barang.
- Pengembalian barang.
- Perubahan data inventaris.

Catatan aktivitas digunakan untuk transparansi dan pengawasan.

---

# BAB III. ALUR SISTEM

## 3.1 Alur Customer

1. Customer membuka website CampGo.
2. Customer melihat katalog dan informasi barang.
3. Customer dapat menggunakan Smart Camping Planner.
4. Customer memilih barang atau paket.
5. Customer menentukan tanggal sewa dan jumlah unit.
6. Sistem memeriksa stok berdasarkan periode.
7. Jika tersedia, customer melanjutkan reservasi.
8. Customer memilih pickup atau doorstep delivery.
9. Customer memilih pembayaran transfer atau cash.
10. Customer mengirim reservasi.
11. Sistem mencatat transaksi.
12. Staff memverifikasi pembayaran dan booking.
13. Booking dikonfirmasi.
14. Customer memperoleh kode booking dan Digital Rental Pass.
15. Barang diserahkan melalui metode yang dipilih.
16. Customer dapat mengajukan perpanjangan atau pembatalan sesuai ketentuan.
17. Barang dikembalikan ke toko atau dijemput Staff.
18. Staff memeriksa kelengkapan dan kondisi barang.
19. Jaminan identitas dikembalikan.
20. Transaksi diselesaikan.

## 3.2 Alur Staff

1. Staff login.
2. Staff membuka dashboard.
3. Staff melihat booking dan transaksi.
4. Staff memeriksa pembayaran.
5. Staff memproses reservasi.
6. Staff mengelola pengantaran atau pickup.
7. Staff melakukan serah terima barang dan mencatat jaminan identitas.
8. Staff memproses permintaan pembatalan.
9. Staff memproses permintaan perpanjangan.
10. Staff melakukan pemeriksaan pengembalian.
11. Staff mencatat kondisi barang.
12. Staff menyelesaikan transaksi.

## 3.3 Alur Admin

1. Admin login.
2. Admin membuka dashboard.
3. Admin mengelola akun pengguna.
4. Admin mengelola Staff.
5. Admin mengelola inventaris dan kategori.
6. Admin mengelola tarif.
7. Admin mengelola artikel.
8. Admin memantau transaksi.
9. Admin melihat laporan.
10. Admin memeriksa aktivitas Staff.
11. Admin menindaklanjuti kerusakan atau kehilangan.

---

# BAB IV. RANCANGAN DATABASE DAN ERD

## 4.1 Konsep Database

Database utama:

`campgo_db`

Database menggunakan MySQL dengan relasi antar tabel.

Rancangan ini masih bersifat awal dan perlu difinalisasi sebelum implementasi.

## 4.2 Daftar Tabel Awal

| No. | Nama tabel | Fungsi |
|---|---|---|
| 1 | users | Menyimpan akun dan role |
| 2 | categories | Kategori perlengkapan |
| 3 | equipment | Data barang dan stok |
| 4 | bookings | Data transaksi reservasi |
| 5 | booking_details | Detail barang dalam booking |
| 6 | payments | Informasi pembayaran |
| 7 | deliveries | Data pengantaran dan pengambilan |
| 8 | cancellations | Pengajuan pembatalan |
| 9 | extensions | Permintaan perpanjangan |
| 10 | returns | Data pengembalian barang |
| 11 | return_details | Detail kondisi dan checklist barang |
| 12 | articles | Data artikel |
| 13 | activity_logs | Catatan tindakan Staff/Admin |
| 14 | contacts | Informasi kontak dan media sosial |

Tabel tambahan dapat dibuat apabila diperlukan, misalnya tabel paket, stok pergerakan, atau detail jaminan transaksi.

## 4.3 Gambaran Relasi ERD

Relasi awal:

- `users` 1:N `bookings`.
- `users` 1:N `articles` sebagai penulis.
- `categories` 1:N `equipment`.
- `bookings` 1:N `booking_details`.
- `equipment` 1:N `booking_details`.
- `bookings` 1:N `payments`.
- `bookings` 1:N `deliveries`.
- `bookings` 1:N `cancellations`.
- `bookings` 1:N `extensions`.
- `bookings` 1:N `returns`.
- `returns` 1:N `return_details`.
- `users` 1:N `activity_logs`.

Relasi final akan disesuaikan dengan kebutuhan transaksi dan aturan bisnis.

## 4.4 Aturan Database Penting

1. Setiap akun memiliki ID unik.
2. Password harus disimpan dalam bentuk hash.
3. Setiap booking memiliki kode unik.
4. Satu booking dapat berisi banyak barang.
5. Stok harus diperiksa berdasarkan tanggal penggunaan.
6. Barang yang sedang disewa tidak boleh dianggap tersedia untuk tanggal yang bertabrakan.
7. Booking yang dibatalkan melepaskan alokasi stok.
8. Perpanjangan harus memeriksa kembali ketersediaan stok.
9. Transaksi harus memiliki catatan pembayaran.
10. Aktivitas penting Staff harus tercatat.

**Catatan teknis:** Stok total tidak cukup hanya dikurangi saat booking dibuat. Ketersediaan harus dihitung dari jumlah unit yang sedang dialokasikan pada periode tanggal yang bersangkutan. Proses pengecekan dan penyimpanan booking perlu dibuat secara aman agar tidak terjadi double booking.

---

# BAB V. PEMBAGIAN TUGAS EMPAT ANGGOTA

## 5.1 Prinsip Pembagian

Kelompok terdiri dari empat orang:

1. Naila.
2. Nisa.
3. Mba Irul.
4. Adel.

Tidak ada ketua atau koordinator khusus.

Semua anggota:
- Memiliki kedudukan yang sama.
- Wajib berkontribusi dalam coding.
- Wajib memahami database.
- Wajib melakukan testing.
- Wajib melakukan commit dan dokumentasi.
- Wajib memahami keseluruhan sistem.

Pembagian dibuat berdasarkan modul pekerjaan dengan tingkat tanggung jawab yang diusahakan seimbang.

## 5.2 Pembagian Tugas

### Anggota 1 – Naila

Fokus: Backend Core dan Akun Customer.

Tugas:
- Struktur awal aplikasi.
- Konfigurasi koneksi database.
- Register dan login.
- Session dan autentikasi.
- Pengaturan role dan proteksi halaman.
- Profil Customer.
- Edit data pribadi.
- Riwayat booking.
- Pengelolaan status akun.
- Integrasi backend bersama anggota lain.

### Anggota 2 – Nisa

Fokus: Customer Interface dan Reservasi.

Tugas:
- Homepage.
- Katalog perlengkapan.
- Detail produk.
- Kategori barang pada sisi Customer.
- Smart Camping Planner.
- Custom paket.
- Form reservasi.
- Validasi stok berdasarkan tanggal.
- Digital Rental Pass.
- Tampilan responsif halaman Customer.

### Anggota 3 – Mba Irul

Fokus: Transaksi dan Operasional Persewaan.

Tugas:
- Dashboard Staff.
- Daftar transaksi.
- Verifikasi pembayaran.
- Pembayaran transfer dan cash.
- Pengajuan pembatalan.
- Pengelolaan perpanjangan.
- Doorstep delivery dan staff pickup.
- Checklist serah terima dan pengembalian.
- Pencatatan kerusakan dan kehilangan.

### Anggota 4 – Adel

Fokus: Admin, Inventaris, dan Konten.

Tugas:
- Dashboard Admin.
- CRUD perlengkapan.
- CRUD kategori.
- Pengelolaan stok dan tarif.
- CRUD artikel.
- Manajemen akun Admin, Staff, dan Customer.
- Catatan aktivitas Staff.
- Grafik penyewaan, pembatalan, dan pemakaian stok.

## 5.3 Kolaborasi dan Keseimbangan

Pembagian di atas merupakan tanggung jawab utama, bukan pembatasan bantuan.

Setiap anggota dapat membantu anggota lain ketika diperlukan.

Untuk menghindari ketimpangan:
- Setiap anggota mengerjakan fitur backend dan frontend sesuai bagian masing-masing.
- Semua anggota melakukan review kode.
- Semua anggota mengikuti integrasi.
- Setiap anggota memiliki kontribusi commit yang nyata.
- Semua anggota wajib mampu menjelaskan fitur yang dibuat.

Pembagian dapat disesuaikan berdasarkan tingkat kesulitan aktual selama pengerjaan.

---

# BAB VI. STRUKTUR FOLDER DAN TEKNOLOGI

## 6.1 Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP Native |
| Database | MySQL |
| Database connection | MySQLi |
| Frontend | HTML, CSS |
| Interaksi | JavaScript |
| CSS Framework | Bootstrap |
| Server lokal | XAMPP |
| Database manager | phpMyAdmin |
| Code editor | Visual Studio Code |
| Version control | Git dan GitHub |
| API testing | Postman |

Pemilihan PHP Native mengikuti materi perkuliahan sehingga kelompok dapat menerapkan teori yang sudah dipelajari.

## 6.2 Materi Kuliah sebagai Acuan

Materi yang menjadi dasar implementasi:
- Pengenalan PHP.
- Syntax PHP.
- Control Statement.
- Function.
- GET dan POST.
- Database.
- Session dan Cookie.
- CRUD.
- REST API.
- File Handling.

Nomor pertemuan dan nama modul mengikuti berkas asli dari dosen.

## 6.3 Struktur Folder Awal

```text
campgo/
│
├── admin/
│   ├── dashboard.php
│   ├── users/
│   ├── equipment/
│   ├── articles/
│   └── reports/
│
├── staff/
│   ├── dashboard.php
│   ├── transactions/
│   ├── cancellations/
│   ├── extensions/
│   └── returns/
│
├── customer/
│   ├── catalog/
│   ├── booking/
│   ├── profile/
│   └── history/
│
├── config/
│   └── koneksi.php
│
├── includes/
│   ├── header.php
│   ├── navbar.php
│   ├── footer.php
│   └── functions.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── uploads/
│   ├── equipment/
│   ├── articles/
│   └── payments/
│
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── docs/
│
├── index.php
└── README.md
```

Struktur dapat dikembangkan ketika fitur mulai diimplementasikan.

---

# BAB VII. ATURAN GITHUB DAN DOKUMENTASI

## 7.1 Repository

Nama repository yang disarankan:

`campgo-rental`

Repository digunakan bersama oleh keempat anggota.

## 7.2 Branch

```text
main
│
└── develop
    │
    ├── feature/auth-profile
    ├── feature/customer-booking
    ├── feature/staff-operations
    └── feature/admin-inventory
```

Fungsi:
- `main`: versi stabil dan final.
- `develop`: tempat integrasi fitur.
- `feature/*`: tempat pengembangan fitur masing-masing.

Tidak ada satu anggota yang memiliki kewenangan eksklusif atas proyek. Penggabungan dilakukan berdasarkan kesepakatan dan hasil review bersama.

## 7.3 Aturan Commit

Contoh:

```bash
git commit -m "feat: add customer registration"
git commit -m "feat: add equipment catalog"
git commit -m "feat: add rental booking"
git commit -m "fix: validate equipment availability"
git commit -m "docs: update database design"
```

Jenis commit:
- `feat`: menambahkan fitur.
- `fix`: memperbaiki bug.
- `docs`: memperbarui dokumentasi.
- `style`: perubahan tampilan atau format kode.
- `refactor`: merapikan kode.
- `test`: menambahkan pengujian.

## 7.4 Proses Kerja GitHub

1. Anggota mengambil branch terbaru dari develop.
2. Membuat branch fitur.
3. Mengembangkan fitur.
4. Melakukan commit secara berkala.
5. Menguji fitur.
6. Membuat Pull Request.
7. Anggota lain melakukan review.
8. Perubahan digabungkan ke develop.
9. Setelah stabil, develop digabungkan ke main.

## 7.5 Dokumentasi Kontribusi

Folder `docs/` menyimpan:
- Konsep sistem.
- Pembagian tugas.
- Flowchart.
- Use case diagram.
- Activity diagram.
- ERD.
- Struktur database.
- Dokumentasi fitur.
- Hasil testing.
- Riwayat keputusan proyek.

GitHub commit history dan Pull Request digunakan sebagai bukti keterlibatan masing-masing anggota.

---

# BAB VIII. KEAMANAN DAN KETENTUAN TEKNIS

CampGo menerapkan keamanan dasar:

1. Password menggunakan `password_hash()` dan `password_verify()`.
2. Query database menggunakan prepared statements.
3. Validasi data di sisi server.
4. Proteksi halaman berdasarkan role.
5. Session dikelola dengan aman.
6. Upload file memeriksa tipe, ukuran, dan nama file.
7. Data identitas pelanggan dibatasi aksesnya.
8. Proses transaksi dan stok harus konsisten.
9. Menggunakan HTTPS ketika website sudah online.
10. Menghindari akses langsung ke file sensitif.

Pembayaran bersifat dummy/sandbox dan refund nyata tidak diwajibkan.

---

# BAB IX. PROGRES DAN KEPUTUSAN PROYEK

## 9.1 Keputusan yang Sudah Disepakati

| No. | Keputusan | Status |
|---|---|---|
| 1 | Nama aplikasi CampGo | Disepakati sementara |
| 2 | Fokus persewaan alat camping | Disepakati |
| 3 | PHP Native dan MySQL | Rekomendasi teknologi |
| 4 | Tiga role: Customer, Staff, Admin | Disepakati |
| 5 | Reservasi berdasarkan tanggal | Disepakati |
| 6 | Validasi stok berdasarkan periode | Disepakati |
| 7 | Smart Camping Planner | Disepakati |
| 8 | Custom Camping Package | Disepakati |
| 9 | Pickup at Store | Disepakati |
| 10 | Doorstep Delivery by Staff | Disepakati |
| 11 | Staff pickup untuk pengembalian | Disepakati |
| 12 | Identitas sebagai jaminan | Disepakati |
| 13 | Pembayaran transfer dan cash | Disepakati |
| 14 | Rental Extension | Disepakati |
| 15 | Pembatalan dengan status pending/approved/rejected | Disepakati |
| 16 | Digital Rental Pass / QR dummy | Disepakati |
| 17 | Checklist pengembalian | Disepakati |
| 18 | Pencatatan kerusakan/kehilangan | Disepakati |
| 19 | Artikel minimal tiga | Disepakati |
| 20 | Kontak WhatsApp dan media sosial | Disepakati |
| 21 | Repository GitHub bersama | Disepakati |
| 22 | Empat anggota dengan kedudukan setara | Disepakati |

## 9.2 Hal yang Masih Perlu Difinalisasi

- Desain visual dan logo CampGo.
- Nomor WhatsApp dan akun media sosial.
- Alamat toko.
- Besaran biaya delivery dan pickup.
- Aturan uang muka dan pelunasan.
- Batas waktu pembatalan.
- Besaran denda keterlambatan.
- Ketentuan penggantian barang rusak atau hilang.
- Detail struktur tabel dan relasi database.
- Format kode booking.
- Desain Digital Rental Pass.
- Hosting dan domain.

Hal-hal tersebut akan dibahas dan disepakati bersama ketika pengerjaan berjalan.

## 9.3 Progres Saat Ini

| Tahapan | Status |
|---|---|
| Pemilihan ide proyek | Selesai |
| Konsep awal aplikasi | Selesai sementara |
| Penentuan fitur | Selesai sementara |
| Pemilihan teknologi | Rekomendasi ditetapkan |
| Pembagian tugas | Rancangan awal |
| Repository GitHub | Belum dikonfirmasi |
| Use case diagram | Belum dibuat |
| Flowchart | Belum dibuat |
| ERD final | Belum dibuat |
| Database MySQL | Belum dibuat |
| Desain UI/UX | Belum dibuat |
| Implementasi PHP | Belum dimulai |
| Testing | Belum dimulai |
| Deployment | Belum dimulai |

---

# BAB X. TARGET AKHIR PROYEK

CampGo ditargetkan memenuhi seluruh deliverables UAS:

1. Website live yang responsif.
2. Repository GitHub dengan branch dan commit history jelas.
3. Dokumentasi lengkap.
4. Akun demo untuk Admin, Staff, dan Customer.
5. Seluruh fitur wajib berjalan.
6. Pengujian sistem dan perbaikan bug.
7. Dokumentasi kontribusi setiap anggota.

Akun demo sesuai instruksi dosen:

| Role | Email | Password |
|---|---|---|
| Admin | admin@demo.test | Admin#2026 |
| Staff | staff@demo.test | Staff#2026 |
| User | user@demo.test | User#2026 |

Password tersebut hanya digunakan untuk akun demonstrasi lokal/deployment tugas dan harus disimpan sebagai hash di database.

---

# BAB XI. ATURAN PEMBARUAN MASTER DOCUMENT

Dokumen ini bersifat dinamis dan menjadi sumber acuan utama proyek.

Setiap perubahan penting wajib diperbarui.

Format pencatatan keputusan:

| Tanggal | Bagian | Perubahan | Status |
|---|---|---|---|
| 14 November 2026 | Master Document | Penyusunan dokumen induk CampGo | Versi 1.0 |

Ketentuan pembaruan:

1. Jangan mengubah konsep yang sudah disepakati tanpa membahasnya terlebih dahulu.
2. Fitur baru dicatat sebagai usulan sampai disetujui bersama.
3. Perubahan fitur wajib memperbarui alur sistem dan database jika diperlukan.
4. Pembagian kerja dapat disesuaikan berdasarkan progres.
5. Keputusan terbaru menggantikan keputusan lama apabila sudah disepakati.
6. Setiap perubahan besar dicatat dalam riwayat versi dokumen.

## FORMAT ACUAN UNTUK CHAT BARU

Jika melanjutkan proyek melalui percakapan baru, gunakan dokumen ini sebagai referensi utama dengan instruksi:

"Gunakan Master Project Document CampGo sebagai acuan utama. Pertahankan konsep, fitur, alur bisnis, teknologi, database, pembagian empat anggota, dan keputusan yang sudah disepakati. Jangan mengubah konsep secara sepihak. Jika ada perubahan atau tambahan, diskusikan terlebih dahulu dan perbarui dokumen induk."

---

**PENUTUP**

CampGo merupakan proyek aplikasi persewaan perlengkapan camping berbasis web yang dirancang untuk memberikan kemudahan kepada pelanggan sekaligus membantu pengelolaan operasional usaha.

Proyek ini dikerjakan secara kolaboratif oleh empat anggota dengan pembagian tanggung jawab yang setara.

Seluruh pengembangan mengacu pada materi Pemrograman Web yang telah dipelajari, khususnya PHP Native, MySQL, pengelolaan database, CRUD, Session, dan File Handling.

Dokumen ini menjadi pedoman bersama agar seluruh anggota memiliki pemahaman dan arah pengerjaan yang sama hingga proyek selesai.

**CAMPGO – Your Adventure Starts Here.**
