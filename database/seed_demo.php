<?php
// =====================================================================
// CAMPGO - SEED DATA DEMO (khusus pengembangan lokal)
// Cara pakai:  php database/seed_demo.php   ATAU buka
//              http://localhost/CampGo2/database/seed_demo.php
// Aman dijalankan berulang: data yang sudah ada dilewati.
// - Akun demo mengikuti Master Document Bab X (password disimpan sebagai hash)
// - Barang & paket contoh HANYA untuk uji alur booking. Data asli
//   dikelola lewat CRUD inventaris (Backend 1 / Adel).
// =====================================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$cli = PHP_SAPI === 'cli';
if (!$cli && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('Seed hanya boleh dijalankan dari komputer lokal.');
}
if (!$cli) {
    header('Content-Type: text/plain; charset=utf-8');
}

$akun = [
    ['Admin Demo', 'admin@demo.test', '081200000001', 'Admin#2026', 'admin'],
    ['Staff Demo', 'staff@demo.test', '081200000002', 'Staff#2026', 'staff'],
    ['User Demo',  'user@demo.test',  '081200000003', 'User#2026',  'customer'],
];
foreach ($akun as [$nama, $email, $telp, $pw, $role]) {
    if (dbFetchOne($conn, 'SELECT user_id FROM users WHERE email = ?', 's', [$email])) {
        echo "lewati akun $email (sudah ada)\n";
        continue;
    }
    dbExecute($conn, 'INSERT INTO users (full_name, email, phone, password_hash, role, account_status, address)
                      VALUES (?, ?, ?, ?, ?, \'active\', ?)', 'ssssss',
        [$nama, $email, $telp, password_hash($pw, PASSWORD_DEFAULT), $role, 'Surakarta']);
    echo "buat akun $email ($role)\n";
}

if (dbFetchOne($conn, 'SELECT COUNT(*) AS n FROM equipment')['n'] > 0) {
    echo "Tabel equipment sudah berisi data, barang contoh dilewati.\n";
    exit;
}

$kategori = ['Tenda', 'Sleeping bag', 'Matras', 'Peralatan masak', 'Penerangan'];
$kid = [];
foreach ($kategori as $k) {
    $kid[$k] = dbInsert($conn, 'INSERT INTO categories (category_name) VALUES (?)', 's', [$k]);
}

// [kategori, nama, harga/hari, stok]
$barang = [
    ['Tenda', 'Tenda Dome 4 Orang', 60000, 5],
    ['Tenda', 'Tenda Dome 2 Orang', 40000, 6],
    ['Sleeping bag', 'Sleeping Bag Polar', 15000, 12],
    ['Matras', 'Matras Gulung', 8000, 15],
    ['Peralatan masak', 'Kompor Portable + Gas', 20000, 6],
    ['Penerangan', 'Lampu Tenda LED', 10000, 10],
];
$eid = [];
foreach ($barang as [$kat, $nama, $harga, $stok]) {
    $eid[$nama] = dbInsert($conn, 'INSERT INTO equipment (category_id, equipment_name, price_per_day, stock_total)
                                   VALUES (?, ?, ?, ?)', 'isdi', [$kid[$kat], $nama, $harga, $stok]);
}

$pid = dbInsert($conn, 'INSERT INTO packages (package_name, description, price_per_day) VALUES (?, ?, ?)', 'ssd',
    ['Paket Hemat 2 Orang', 'Tenda 2 orang, 2 sleeping bag, 2 matras, lampu', 90000]);
foreach ([['Tenda Dome 2 Orang', 1], ['Sleeping Bag Polar', 2], ['Matras Gulung', 2], ['Lampu Tenda LED', 1]] as [$n, $q]) {
    dbExecute($conn, 'INSERT INTO package_items (package_id, equipment_id, quantity) VALUES (?, ?, ?)', 'iii', [$pid, $eid[$n], $q]);
}
echo "Barang contoh, kategori, dan 1 paket contoh dibuat.\n";
