# ERD CampGo (mengikuti SQL kelompok, 20 tabel)

Diagram Mermaid di bawah menampilkan hubungan utama antartabel. Kolom PK/FK dan detail tipe data tetap mengacu pada `database/campgo_db.sql` sebagai sumber utama.

```mermaid
erDiagram
    users ||--o{ bookings : membuat
    users ||--o{ articles : menulis
    users ||--o{ activity_logs : melakukan
    categories ||--o{ equipment : mengelompokkan
    equipment ||--o{ stock_movements : mencatat
    packages ||--o{ package_items : terdiri_dari
    equipment ||--o{ package_items : dimasukkan
    bookings ||--o{ booking_packages : memesan
    packages ||--o{ booking_packages : dipesan
    bookings ||--o{ booking_details : memuat
    equipment ||--o{ booking_details : disewa
    booking_packages |o--o{ booking_details : rincian_paket
    booking_details ||--o{ handover_items : serah_terima
    bookings ||--o{ payments : dibayar
    extensions |o--o{ payments : pembayaran_perpanjangan
    bookings ||--o| deliveries : logistik
    bookings ||--o| cancellations : dibatalkan
    bookings ||--o{ extensions : diperpanjang
    bookings ||--o{ reschedules : dijadwal_ulang
    bookings ||--o| returns : dikembalikan
    returns ||--o{ return_details : pemeriksaan
    equipment ||--o{ return_details : kondisi
    users {
        bigint user_id PK
        varchar email UK
        enum role
    }
    categories {
        bigint category_id PK
        varchar category_name UK
    }
    equipment {
        bigint equipment_id PK
        bigint category_id FK
        int stock_total
        int stock_unavailable
    }
    stock_movements {
        bigint movement_id PK
        bigint equipment_id FK
        bigint changed_by FK
    }
    packages {
        bigint package_id PK
    }
    package_items {
        bigint package_item_id PK
        bigint package_id FK
        bigint equipment_id FK
    }
    bookings {
        bigint booking_id PK
        bigint user_id FK
        varchar booking_code UK
    }
    booking_packages {
        bigint booking_package_id PK
        bigint booking_id FK
        bigint package_id FK
    }
    booking_details {
        bigint booking_detail_id PK
        bigint booking_id FK
        bigint booking_package_id FK
        bigint equipment_id FK
    }
    handover_items {
        bigint handover_item_id PK
        bigint booking_detail_id FK
    }
    payments {
        bigint payment_id PK
        bigint booking_id FK
        bigint extension_id FK
    }
    deliveries {
        bigint delivery_id PK
        bigint booking_id FK,UK
    }
    cancellations {
        bigint cancellation_id PK
        bigint booking_id FK,UK
    }
    extensions {
        bigint extension_id PK
        bigint booking_id FK
    }
    reschedules {
        bigint reschedule_id PK
        bigint booking_id FK
    }
    returns {
        bigint return_id PK
        bigint booking_id FK,UK
    }
    return_details {
        bigint return_detail_id PK
        bigint return_id FK
        bigint equipment_id FK
    }
    articles {
        bigint article_id PK
        bigint author_id FK
    }
    activity_logs {
        bigint log_id PK
        bigint user_id FK
    }
    contacts {
        bigint contact_id PK
    }
```

**Catatan:** `contacts` berdiri sendiri. Foreign key petugas pada tabel operasi (`processed_by`, `checked_by`, `received_by`, dan sebagainya) mengarah ke `users`, tetapi tidak digambar seluruhnya agar diagram tetap terbaca. Kardinalitas mengacu pada kombinasi FK dan UNIQUE di SQL; relasi paket booking bersifat opsional.
