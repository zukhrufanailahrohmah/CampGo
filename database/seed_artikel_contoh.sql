-- =====================================================================
-- CAMPGO - 3 ARTIKEL CONTOH (Master Document 2.1 N: minimal tiga artikel)
-- Import lewat phpMyAdmin (database campgo_db > Import) atau tab SQL.
-- Aman diulang: slug yang sudah ada dilewati (INSERT IGNORE).
-- Isi artikel adalah teks biasa: paragraf dipisah baris kosong,
-- "## " = subjudul, "- " = daftar poin. Isi final boleh diganti kelompok
-- lewat CRUD artikel Staff/Admin.
-- =====================================================================

INSERT IGNORE INTO articles (author_id, title, slug, excerpt, content, article_status, published_at) VALUES
(NULL,
 'Tips Memilih Tenda untuk Pemula',
 'tips-memilih-tenda-untuk-pemula',
 'Kapasitas, bahan, dan cara pasang: tiga hal yang perlu kamu cek sebelum menyewa tenda pertamamu.',
 'Tenda adalah perlengkapan paling penting saat camping. Salah memilih tenda bisa membuat malam terasa dingin, basah, atau sempit. Berikut hal-hal yang perlu dicek sebelum kamu menyewa.\n\n## Sesuaikan kapasitas dengan jumlah orang\n\nTenda berkapasitas 2 orang biasanya terasa sempit kalau dipakai berdua dengan tas. Kalau ingin lebih lega, pilih satu ukuran di atas jumlah orang. Misalnya, untuk 3 orang pilih tenda kapasitas 4 orang.\n\n## Perhatikan bahan dan perlindungan hujan\n\nPilih tenda dengan flysheet yang menutup seluruh bagian atas dan lantai yang tidak mudah tembus air. Cek juga apakah ada bagian yang sobek atau jahitannya terbuka.\n\n## Pilih yang mudah dipasang\n\nBagi pemula, tenda dome lebih mudah dipasang karena rangkanya sederhana. Sebelum berangkat, coba pasang sebentar di rumah supaya tidak kebingungan di lokasi.\n\n## Periksa kelengkapan saat serah terima\n\nPastikan tenda, tiang, pasak, dan tali lengkap. Kelengkapan ini juga diperiksa oleh staf CampGo saat barang diserahkan dan dikembalikan.',
 'published', '2026-11-10 09:00:00'),

(NULL,
 'Checklist Perlengkapan Camping',
 'checklist-perlengkapan-camping',
 'Daftar perlengkapan yang sebaiknya dibawa, dikelompokkan per kebutuhan supaya tidak ada yang tertinggal.',
 'Barang yang tertinggal sering baru terasa saat sudah sampai di lokasi. Gunakan daftar ini sebagai pengingat sebelum berangkat.\n\n## Tempat tidur\n\n- Tenda lengkap dengan tiang dan pasak\n- Sleeping bag untuk setiap orang\n- Matras sebagai alas tidur\n- Bantal kecil atau bantal tiup\n\n## Memasak dan makan\n\n- Kompor portable dan gas cadangan\n- Nesting atau peralatan masak\n- Korek api atau pemantik\n- Botol minum dan air bersih\n\n## Penerangan\n\n- Lampu tenda\n- Senter atau headlamp\n- Baterai cadangan\n\n## Lain-lain\n\n- Jas hujan atau ponco\n- Obat pribadi dan P3K sederhana\n- Kantong sampah untuk dibawa pulang\n\nTidak semua perlengkapan harus dibeli. Tenda, sleeping bag, matras, kompor, dan lampu bisa disewa di CampGo sesuai tanggal camping kamu.',
 'published', '2026-11-12 09:00:00'),

(NULL,
 'Panduan Menyiapkan Camping Pertama Kali',
 'panduan-menyiapkan-camping-pertama-kali',
 'Langkah sederhana dari memilih lokasi sampai menyewa perlengkapan, supaya camping pertamamu berjalan lancar.',
 'Camping pertama kali memang menyenangkan, tapi butuh persiapan. Ikuti langkah-langkah berikut agar perjalananmu nyaman.\n\n## 1. Pilih lokasi dan tanggal\n\nMulailah dengan lokasi yang mudah dijangkau dan punya fasilitas dasar seperti air dan toilet. Cek juga prakiraan cuaca untuk tanggal yang kamu pilih.\n\n## 2. Tentukan jumlah peserta dan lama menginap\n\nJumlah orang dan durasi menentukan berapa tenda, sleeping bag, dan perlengkapan masak yang dibutuhkan. Kalau masih bingung, gunakan paket camping yang sudah disusun.\n\n## 3. Sewa perlengkapan\n\nPilih perlengkapan di katalog, tentukan tanggal sewa, lalu kirim reservasi. Kamu bisa mengambil barang di toko atau meminta staf mengantarnya ke alamatmu.\n\n## 4. Periksa barang saat serah terima\n\nCek jumlah dan kondisi barang bersama staf, lalu simpan kode booking dan Digital Rental Pass untuk pengambilan.\n\n## 5. Tinggalkan lokasi seperti semula\n\nBawa pulang semua sampah, bersihkan perlengkapan secukupnya, lalu kembalikan tepat waktu. Kalau perlu waktu lebih lama, ajukan perpanjangan sewa lewat website.',
 'published', '2026-11-15 09:00:00');
