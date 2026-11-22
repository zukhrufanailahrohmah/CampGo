<?php
// =================================
// CAMPGO - LOGIKA BOOKING, STOK, & DIGITAL RENTAL PASS
// Pemilik modul: Backend 2 (Irul). Fungsi cekKetersediaan() juga dipakai
// Backend 1 (Adel) untuk perpanjangan & reschedule.
// =================================
require_once __DIR__ . '/functions.php';

// Error yang aman ditampilkan ke customer (validasi, stok kurang, dll)
class BookingException extends Exception
{
}

// ---------- Tanggal ----------
function parseTanggal(string $s): ?DateTimeImmutable
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $s);
    return ($d && $d->format('Y-m-d') === $s) ? $d : null;
}

// Hari sewa dihitung INKLUSIF: 10 s/d 12 = 3 hari. (Perlu disepakati kelompok.)
function hitungHariSewa(string $start, string $end): int
{
    return (int)parseTanggal($start)->diff(parseTanggal($end))->days + 1;
}

function validasiPeriode(string $start, string $end): void
{
    $a = parseTanggal($start);
    $b = parseTanggal($end);

    if (!$a || !$b) {
        throw new BookingException('Format tanggal tidak valid.');
    }
    if ($a < new DateTimeImmutable('today')) {
        throw new BookingException('Tanggal mulai sewa tidak boleh sebelum hari ini.');
    }
    if ($b < $a) {
        throw new BookingException('Tanggal selesai tidak boleh sebelum tanggal mulai.');
    }
    if (hitungHariSewa($start, $end) > MAX_RENTAL_DAYS) {
        throw new BookingException('Durasi sewa maksimal ' . MAX_RENTAL_DAYS . ' hari.');
    }
}

function normalisasiTelepon(string $t): string
{
    $t = preg_replace('/\D/', '', $t);
    if (strncmp($t, '62', 2) === 0) {
        $t = '0' . substr($t, 2);
    }
    return $t;
}

// ---------- Input jumlah dari form: [id => qty] ----------
function bersihkanJumlah($raw, string $label): array
{
    $out = [];
    if (!is_array($raw)) {
        return $out;
    }
    foreach ($raw as $id => $qty) {
        if ($qty === '' || $qty === null) {
            continue;
        }
        $idInt = filter_var($id, FILTER_VALIDATE_INT);
        $q = filter_var($qty, FILTER_VALIDATE_INT);
        if ($idInt === false || $idInt < 1 || $q === false || $q < 0 || $q > MAX_QTY_PER_ITEM) {
            throw new BookingException("Jumlah {$label} tidak valid (0-" . MAX_QTY_PER_ITEM . ').');
        }
        if ($q > 0) {
            $out[$idInt] = $q;
        }
    }
    return $out;
}

// ---------- Paket toko ----------
function ambilPaket(mysqli $conn, array $ids): array
{
    [$in, $tipe] = dbIn($ids);
    $rows = dbFetchAll($conn, "SELECT package_id, package_name, price_per_day FROM packages
                               WHERE package_id IN ($in) AND package_status = 'active'", $tipe, $ids);
    return array_column($rows, null, 'package_id');
}

function ambilKomponenPaket(mysqli $conn, array $ids): array
{
    [$in, $tipe] = dbIn($ids);
    $rows = dbFetchAll($conn, "SELECT package_id, equipment_id, quantity FROM package_items WHERE package_id IN ($in)", $tipe, $ids);
    $out = [];
    foreach ($rows as $r) {
        $out[$r['package_id']][] = ['equipment_id' => (int)$r['equipment_id'], 'quantity' => (int)$r['quantity']];
    }
    return $out;
}

// ---------- STOK PER TANGGAL ----------
// Menghitung ketersediaan tiap barang untuk periode $start..$end.
//   $needs = [equipment_id => jumlah dibutuhkan] (sudah termasuk isi paket)
//   $excludeBookingId = booking yang diabaikan (untuk cek perpanjangan/reschedule booking itu sendiri)
//   $lock = true  -> baris equipment dikunci (FOR UPDATE), wajib di dalam transaksi
// Aturan: sisa stok = (stock_total - stock_unavailable) - PUNCAK pemakaian pada hari-hari di periode itu.
// Dihitung per hari, jadi dua booking yang tidak saling tumpang tindih tidak dihitung dobel.
function cekKetersediaan(mysqli $conn, array $needs, string $start, string $end, ?int $excludeBookingId = null, bool $lock = false): array
{
    if (!$needs) {
        return [];
    }
    $ids = array_keys($needs);
    sort($ids);
    [$in, $tipe] = dbIn($ids);

    $eq = dbFetchAll(
        $conn,
        "SELECT equipment_id, equipment_name, price_per_day, stock_total, stock_unavailable, item_status
         FROM equipment WHERE equipment_id IN ($in) ORDER BY equipment_id" . ($lock ? ' FOR UPDATE' : ''),
        $tipe,
        $ids
    );

    $aktif = "'" . implode("','", BOOKING_ACTIVE_STATUSES) . "'";
    $sql = "SELECT bd.equipment_id, b.start_date, b.end_date, SUM(bd.quantity) AS qty
            FROM booking_details bd
            JOIN bookings b ON b.booking_id = bd.booking_id
            WHERE bd.equipment_id IN ($in)
              AND b.booking_status IN ($aktif)
              AND b.start_date <= ? AND b.end_date >= ?";
    $params = array_merge($ids, [$end, $start]);
    $tipe2 = $tipe . 'ss';
    if ($excludeBookingId !== null) {
        $sql .= ' AND b.booking_id <> ?';
        $params[] = $excludeBookingId;
        $tipe2 .= 'i';
    }
    $sql .= ' GROUP BY bd.equipment_id, b.booking_id, b.start_date, b.end_date';
    $dipesan = dbFetchAll($conn, $sql, $tipe2, $params);

    // Jumlah terpakai per barang per hari
    $perHari = [];
    $hari = new DatePeriod(parseTanggal($start), new DateInterval('P1D'), parseTanggal($end)->modify('+1 day'));
    foreach ($hari as $d) {
        $tgl = $d->format('Y-m-d');
        foreach ($dipesan as $r) {
            if ($r['start_date'] <= $tgl && $r['end_date'] >= $tgl) {
                $perHari[$r['equipment_id']][$tgl] = ($perHari[$r['equipment_id']][$tgl] ?? 0) + (int)$r['qty'];
            }
        }
    }

    $hasil = [];
    foreach ($eq as $e) {
        $id = (int)$e['equipment_id'];
        $kapasitas = max(0, (int)$e['stock_total'] - (int)$e['stock_unavailable']);
        $puncak = isset($perHari[$id]) ? max($perHari[$id]) : 0;
        $tersedia = max(0, $kapasitas - $puncak);
        $hasil[$id] = [
            'equipment_id' => $id,
            'nama'         => $e['equipment_name'],
            'harga'        => (float)$e['price_per_day'],
            'status'       => $e['item_status'],
            'kapasitas'    => $kapasitas,
            'terpakai'     => $puncak,
            'tersedia'     => $tersedia,
            'dibutuhkan'   => $needs[$id],
            'cukup'        => $e['item_status'] === 'available' && $tersedia >= $needs[$id],
        ];
    }
    return $hasil;
}

// ---------- Rincian biaya + validasi stok (dipakai cek_stok & buatBooking) ----------
// $strict = true  -> lempar BookingException kalau ada yang tidak cukup
// $strict = false -> kembalikan hasil dengan 'ok' = false (untuk tombol "cek stok")
function susunRincian(mysqli $conn, array $items, array $pkgs, string $start, string $end, bool $lock = false, bool $strict = true): array
{
    validasiPeriode($start, $end);
    if (!$items && !$pkgs) {
        throw new BookingException('Pilih minimal satu perlengkapan atau paket.');
    }
    $hari = hitungHariSewa($start, $end);

    $paketDb = $pkgs ? ambilPaket($conn, array_keys($pkgs)) : [];
    $komponen = $pkgs ? ambilKomponenPaket($conn, array_keys($pkgs)) : [];
    foreach ($pkgs as $pid => $q) {
        if (!isset($paketDb[$pid])) {
            throw new BookingException('Ada paket yang tidak ditemukan atau sudah tidak aktif.');
        }
        if (empty($komponen[$pid])) {
            throw new BookingException('Paket "' . $paketDb[$pid]['package_name'] . '" belum memiliki isi barang.');
        }
    }

    // Kebutuhan fisik per barang = satuan + isi paket
    $needs = [];
    foreach ($items as $eid => $q) {
        $needs[$eid] = ($needs[$eid] ?? 0) + $q;
    }
    foreach ($pkgs as $pid => $q) {
        foreach ($komponen[$pid] as $k) {
            $needs[$k['equipment_id']] = ($needs[$k['equipment_id']] ?? 0) + $k['quantity'] * $q;
        }
    }
    ksort($needs);

    $cek = cekKetersediaan($conn, $needs, $start, $end, null, $lock);

    $pesan = [];
    foreach ($needs as $eid => $q) {
        if (!isset($cek[$eid])) {
            throw new BookingException('Ada perlengkapan yang tidak ditemukan.');
        }
        $r = $cek[$eid];
        if ($r['status'] !== 'available') {
            $pesan[] = $r['nama'] . ' sedang tidak tersedia.';
        } elseif (!$r['cukup']) {
            $pesan[] = "Stok {$r['nama']} tidak cukup pada periode ini (tersedia {$r['tersedia']}, dibutuhkan {$r['dibutuhkan']}).";
        }
    }
    if ($pesan && $strict) {
        throw new BookingException(implode(' ', $pesan));
    }

    // Harga selalu diambil dari database, bukan dari form
    $barisPaket = [];
    $subtotal = 0.0;
    foreach ($pkgs as $pid => $q) {
        $unit = (float)$paketDb[$pid]['price_per_day'];
        $sub = $unit * $q * $hari;
        $subtotal += $sub;
        $barisPaket[] = ['package_id' => $pid, 'nama' => $paketDb[$pid]['package_name'], 'qty' => $q,
                         'unit_price' => $unit, 'subtotal' => $sub, 'komponen' => $komponen[$pid]];
    }
    $barisItem = [];
    foreach ($items as $eid => $q) {
        $unit = $cek[$eid]['harga'];
        $sub = $unit * $q * $hari;
        $subtotal += $sub;
        $barisItem[] = ['equipment_id' => $eid, 'nama' => $cek[$eid]['nama'], 'qty' => $q,
                        'unit_price' => $unit, 'subtotal' => $sub];
    }

    return ['hari' => $hari, 'needs' => $needs, 'cek' => $cek, 'ok' => !$pesan, 'pesan' => $pesan,
            'paket' => $barisPaket, 'item' => $barisItem, 'subtotal' => $subtotal];
}

// ---------- Kode booking: CG-YYMMDD-XXXXX ----------
function buatKodeBooking(): string
{
    $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa O/0/I/1 supaya tidak salah baca
    $acak = '';
    for ($i = 0; $i < 5; $i++) {
        $acak .= $abjad[random_int(0, strlen($abjad) - 1)];
    }
    return BOOKING_CODE_PREFIX . '-' . date('ymd') . '-' . $acak;
}

// ---------- BUAT BOOKING (transaksi database) ----------
// $in: start_date, end_date, pickup_method, payment_method, customer_name, customer_phone,
//      delivery_address, customer_notes, items[equipment_id]=qty, packages[package_id]=qty
// Return: ['booking_id','booking_code','payment_id','total']
function buatBooking(mysqli $conn, int $userId, array $in): array
{
    $start = trim($in['start_date'] ?? '');
    $end = trim($in['end_date'] ?? '');
    $metode = $in['pickup_method'] ?? '';
    $bayar = $in['payment_method'] ?? '';
    $nama = trim($in['customer_name'] ?? '');
    $telp = trim($in['customer_phone'] ?? '');
    $alamat = trim($in['delivery_address'] ?? '');
    $catatan = trim($in['customer_notes'] ?? '');

    if (!in_array($metode, ['pickup_at_store', 'doorstep_delivery'], true)) {
        throw new BookingException('Pilih metode pengambilan barang.');
    }
    if (!in_array($bayar, ['transfer', 'cash'], true)) {
        throw new BookingException('Pilih metode pembayaran.');
    }
    if (panjangTeks($nama) < 2 || panjangTeks($nama) > 120) {
        throw new BookingException('Nama pemesan harus 2-120 karakter.');
    }
    if (!preg_match('/^(\+62|62|0)[0-9]{8,13}$/', preg_replace('/[\s\-]/', '', $telp))) {
        throw new BookingException('Nomor telepon tidak valid (contoh: 081234567890).');
    }
    if ($metode === 'doorstep_delivery' && panjangTeks($alamat) < 10) {
        throw new BookingException('Alamat pengantaran wajib diisi lengkap untuk layanan delivery.');
    }
    if ($metode === 'pickup_at_store') {
        $alamat = '';
    }

    $items = bersihkanJumlah($in['items'] ?? [], 'barang');
    $pkgs = bersihkanJumlah($in['packages'] ?? [], 'paket');

    mysqli_begin_transaction($conn);
    try {
        // lock = true: baris equipment dikunci, jadi dua customer yang berebut
        // unit terakhir akan diproses satu per satu (Master Doc 4.4 / catatan SQL)
        $r = susunRincian($conn, $items, $pkgs, $start, $end, true, true);

        $deliveryFee = $metode === 'doorstep_delivery' ? (float)DELIVERY_FEE : 0.0;
        $total = $r['subtotal'] + $deliveryFee;
        // Transfer: tunggu bukti dulu. Tunai: langsung antre untuk dikonfirmasi Staff.
        $statusBooking = $bayar === 'transfer' ? 'pending_payment' : 'waiting_verification';

        $bookingId = 0;
        $kode = '';
        for ($i = 0; $i < 5 && !$bookingId; $i++) {
            $kode = buatKodeBooking();
            try {
                $bookingId = dbInsert(
                    $conn,
                    'INSERT INTO bookings (user_id, booking_code, start_date, end_date, pickup_method,
                        customer_name, customer_phone, delivery_address, subtotal, delivery_fee,
                        discount_amount, total_amount, booking_status, customer_notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)',
                    'isssssssdddss',
                    [$userId, $kode, $start, $end, $metode, $nama, $telp, $alamat === '' ? null : $alamat,
                     $r['subtotal'], $deliveryFee, $total, $statusBooking, $catatan === '' ? null : $catatan]
                );
            } catch (RuntimeException $e) {
                if ($e->getCode() !== 1062) { // 1062 = kode kembar, coba kode lain
                    throw $e;
                }
            }
        }
        if (!$bookingId) {
            throw new RuntimeException('Gagal membuat kode booking unik.');
        }

        foreach ($r['paket'] as $p) {
            $bpId = dbInsert(
                $conn,
                'INSERT INTO booking_packages (booking_id, package_id, quantity, unit_price, rental_days, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)',
                'iiidid',
                [$bookingId, $p['package_id'], $p['qty'], $p['unit_price'], $r['hari'], $p['subtotal']]
            );
            // Isi paket dicatat untuk stok & checklist, harga 0 agar tidak ditagih dua kali
            foreach ($p['komponen'] as $k) {
                dbExecute(
                    $conn,
                    'INSERT INTO booking_details (booking_id, booking_package_id, equipment_id, quantity, unit_price, rental_days, subtotal)
                     VALUES (?, ?, ?, ?, 0, ?, 0)',
                    'iiiii',
                    [$bookingId, $bpId, $k['equipment_id'], $k['quantity'] * $p['qty'], $r['hari']]
                );
            }
        }
        foreach ($r['item'] as $it) {
            dbExecute(
                $conn,
                'INSERT INTO booking_details (booking_id, booking_package_id, equipment_id, quantity, unit_price, rental_days, subtotal)
                 VALUES (?, NULL, ?, ?, ?, ?, ?)',
                'iiidid',
                [$bookingId, $it['equipment_id'], $it['qty'], $it['unit_price'], $r['hari'], $it['subtotal']]
            );
        }

        // Catatan awal pengambilan (Backend 1 melanjutkan: jadwal, jaminan identitas, serah terima)
        dbExecute(
            $conn,
            "INSERT INTO deliveries (booking_id, fulfillment_method, delivery_address, delivery_status)
             VALUES (?, ?, ?, 'not_scheduled')",
            'iss',
            [$bookingId, $metode, $alamat === '' ? null : $alamat]
        );

        $paymentId = dbInsert(
            $conn,
            "INSERT INTO payments (booking_id, payment_method, payment_type, amount, payment_status)
             VALUES (?, ?, 'full_payment', ?, 'unpaid')",
            'isd',
            [$bookingId, $bayar, $total]
        );

        mysqli_commit($conn);
        return ['booking_id' => $bookingId, 'booking_code' => $kode, 'payment_id' => $paymentId, 'total' => $total];
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

// ---------- Riwayat & detail ----------
function riwayatBooking(mysqli $conn, int $userId): array
{
    return dbFetchAll(
        $conn,
        "SELECT b.booking_id, b.booking_code, b.start_date, b.end_date, b.pickup_method, b.total_amount,
                b.booking_status, b.created_at,
                p.payment_method, p.payment_status,
                c.cancellation_status,
                (SELECT e.extension_status FROM extensions e WHERE e.booking_id = b.booking_id
                  ORDER BY e.extension_id DESC LIMIT 1) AS extension_status
         FROM bookings b
         LEFT JOIN payments p ON p.payment_id = (SELECT MAX(p2.payment_id) FROM payments p2
                                                 WHERE p2.booking_id = b.booking_id AND p2.payment_type = 'full_payment')
         LEFT JOIN cancellations c ON c.booking_id = b.booking_id
         WHERE b.user_id = ?
         ORDER BY b.created_at DESC, b.booking_id DESC",
        'i',
        [$userId]
    );
}

// Detail lengkap satu booking. $userId diisi = hanya boleh milik user itu (customer).
// $userId null = tanpa batas pemilik (dipakai Staff/Admin).
function detailBooking(mysqli $conn, string $kode, ?int $userId = null): ?array
{
    $sql = 'SELECT b.*, u.email AS user_email, u.phone AS user_phone FROM bookings b
            JOIN users u ON u.user_id = b.user_id WHERE b.booking_code = ?';
    $tipe = 's';
    $params = [$kode];
    if ($userId !== null) {
        $sql .= ' AND b.user_id = ?';
        $tipe .= 'i';
        $params[] = $userId;
    }
    $b = dbFetchOne($conn, $sql, $tipe, $params);
    if (!$b) {
        return null;
    }
    $id = (int)$b['booking_id'];

    $paket = dbFetchAll($conn, 'SELECT bp.*, pk.package_name FROM booking_packages bp
                                JOIN packages pk ON pk.package_id = bp.package_id WHERE bp.booking_id = ?', 'i', [$id]);
    $detail = dbFetchAll($conn, 'SELECT bd.*, e.equipment_name FROM booking_details bd
                                 JOIN equipment e ON e.equipment_id = bd.equipment_id
                                 WHERE bd.booking_id = ? ORDER BY bd.booking_detail_id', 'i', [$id]);

    return [
        'booking'      => $b,
        'paket'        => $paket,
        'barang_satuan' => array_values(array_filter($detail, fn($d) => $d['booking_package_id'] === null)),
        'isi_paket'    => array_values(array_filter($detail, fn($d) => $d['booking_package_id'] !== null)),
        'payments'     => dbFetchAll($conn, 'SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_id', 'i', [$id]),
        'delivery'     => dbFetchOne($conn, 'SELECT * FROM deliveries WHERE booking_id = ?', 'i', [$id]),
        'cancellation' => dbFetchOne($conn, 'SELECT * FROM cancellations WHERE booking_id = ?', 'i', [$id]),
        'extensions'   => dbFetchAll($conn, 'SELECT * FROM extensions WHERE booking_id = ? ORDER BY extension_id', 'i', [$id]),
    ];
}

// ---------- Digital Rental Pass ----------
function kodeVerifikasi(string $kodeBooking): string
{
    return strtoupper(substr(hash_hmac('sha256', $kodeBooking, APP_SECRET), 0, 8));
}

// Pass hanya tampil untuk booking confirmed/ongoing.
// Pemilik yang login boleh langsung; tamu harus cocok kode booking + email ATAU nomor telepon.
function ambilPass(mysqli $conn, string $kode, string $kontak, ?int $loginUserId = null): ?array
{
    $d = detailBooking($conn, strtoupper(trim($kode)));
    if (!$d || !in_array($d['booking']['booking_status'], ['confirmed', 'ongoing'], true)) {
        return null;
    }
    $b = $d['booking'];

    $pemilik = $loginUserId !== null && (int)$b['user_id'] === $loginUserId;
    if (!$pemilik) {
        $kontak = trim($kontak);
        $cocokEmail = $kontak !== '' && strcasecmp($kontak, $b['user_email']) === 0;
        $telp = normalisasiTelepon($kontak);
        $cocokTelp = $telp !== '' && ($telp === normalisasiTelepon((string)$b['customer_phone'])
                                      || $telp === normalisasiTelepon((string)$b['user_phone']));
        if (!$cocokEmail && !$cocokTelp) {
            return null;
        }
    }

    $d['kode_verifikasi'] = kodeVerifikasi($b['booking_code']);
    return $d;
}
