# RasaNusa - Katalog Menu & Sistem Pemesanan Restoran UMKM

Aplikasi web siap pakai untuk Usaha Mikro, Kecil, dan Menengah (UMKM) kuliner di Indonesia. RasaNusa menyediakan katalog menu hidangan nusantara online, alur pemesanan langsung terintegrasi dengan WhatsApp dan basis data, serta panel admin lengkap untuk mempermudah operasional harian.

Dokumentasi ini tersedia dalam bahasa lain:
* [English](README.md)

---

## 1. Penjelasan Aplikasi & Masalah yang Diselesaikan

### Latar Belakang Masalah
Banyak rumah makan atau usaha katering rumahan di Indonesia masih menjalankan transaksi secara manual melalui chat pesan instan atau media sosial. Kendala yang sering dihadapi antara lain:
* Pelanggan kesulitan mengetahui daftar menu, harga terbaru, dan ketersediaan porsi harian.
* Risiko kesalahan hitung total harga dan salah catat alamat pengiriman.
* Chat pesanan menumpuk atau terlewat oleh pengelola saat jam sibuk.
* Kerentanan spam formulir dan bot pengganggu jika membuka formulir tanpa proteksi.

### Solusi RasaNusa
RasaNusa menyederhanakan alur tersebut melalui etalase digital terpadu:
* **Katalog Real-Time**: Pelanggan dapat melihat foto menu, penjelasan hidangan, harga, dan indikator sisa stok secara transparan.
* **Transaksi Presisi**: Formulir pemesanan dalam modal dengan validasi inline (`<span>`), kalkulasi estimasi otomatis, penguncian harga di sisi server, dan pengurangan stok otomatis via transaksi database.
* **Notifikasi Ganda**: Meneruskan rincian pesanan rapi ke WhatsApp pembeli dan admin, sekaligus mengirim peringatan instan ke Telegram pengelola secara non-blocking.
* **Perlindungan Bot & Keamanan**: Google reCAPTCHA v2, kolom honeypot tersembunyi, pembatasan laju pemesanan (rate limiting), dan prepared statement untuk mencegah serangan SQL Injection.
* **Panel Kendali Responsif**: Navigasi sidebar terpadu untuk mengelola menu, memantau status pesanan, kurasi ulasan pelanggan, serta memperbarui nomor kontak dan alamat dapur tanpa menyentuh kode program.

---

## 2. Fitur Utama

### Antarmuka Publik (Pelanggan)
* **Halaman Beranda (`index.php`)**: Profil restoran, jam buka operasional, pilihan menu unggulan, ulasan pelanggan, dan formulir kirim pesan cepat.
* **Katalog Lengkap (`katalog.php`)**: Seluruh daftar menu dengan pencarian teks langsung dan status ketersediaan.
* **Detail Menu (`detail.php`)**: Halaman rincian tiap menu dengan tombol langsung membuka formulir pemesanan.
* **Modal Pemesanan (`#orderModal`)**: Seragam di semua halaman publik, dilengkapi teks error inline di bawah input, masking angka WhatsApp, batas porsi dinamis sesuai stok, dan reCAPTCHA.
* **Kirim Pesan Cepat (`#contactForm`)**: Menghubungi admin via WhatsApp dengan proteksi honeypot dan jeda klik anti-spam.

### Panel Pengelola (`/admin`)
* **Autentikasi Akun (`admin/login.php`)**: Masuk dengan session aman dan verifikasi password hash standar industri.
* **Dashboard (`admin/index.php`)**: Ringkasan omzet, total pesanan, jumlah produk aktif, dan daftar transaksi terbaru.
* **Kelola Menu (`admin/menu.php`)**: Operasi CRUD (tambah, edit, hapus) hidangan, pengelolaan stok harian, dan upload foto produk.
* **Kelola Pesanan (`admin/orders.php`)**: Tabel data transaksi terstruktur dengan filter pencarian dan pembaruan status (`pending`, `setuju`, `selesai`, `tolak`, `dibatalkan`).
* **Kelola Testimoni (`admin/testimoni.php`)**: Memilih ulasan pelanggan yang layak ditampilkan di halaman depan.
* **Pengaturan Toko (`admin/pengaturan.php`)**: Memperbarui nomor WhatsApp penerima pesanan, alamat fisik dapur, email layanan, serta kata sandi akun admin.

---

## 3. Teknologi yang Digunakan

* **Backend**: PHP 7.4+ (Native dengan ekstensi MySQLi, prepared statement, dan transaksi basis data)
* **Basis Data**: MySQL 5.7+ / MariaDB 10.3+
* **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript (ES6+), jQuery, DataTables
* **Tipografi**: Fraunces (Serif Editorial) dan Plus Jakarta Sans (Geometric Sans)
* **Integrasi Eksternal**: Google reCAPTCHA v2, WhatsApp Click-to-Chat API, Telegram Bot API

---

## 4. Instalasi & Menjalankan di Lingkungan Lokal

### Prasyarat
* PHP 7.4 atau versi lebih baru
* Server MySQL / MariaDB
* Web Server (Apache via Laragon / XAMPP, atau PHP CLI bawaan)

### Langkah 1: Unduh / Clone Repositori
```bash
git clone https://github.com/dannysmarthaf/SIMPLE-RESTAURANT-WEBSITE.git
cd SIMPLE-RESTAURANT-WEBSITE
```

### Langkah 2: Menyiapkan Basis Data
1. Nyalakan layanan MySQL/MariaDB Anda.
2. Buka phpMyAdmin atau terminal database.
3. Impor skema tabel yang telah disediakan:
```sql
SOURCE database.sql;
```
Skema ini akan menyiapkan database `penjualan_umkm` beserta tabel relasi, data akun default admin, menu awal, dan konfigurasi kontak.

### Langkah 3: Konfigurasi Koneksi
Pengaturan koneksi tersimpan di file `config/db.php`. File ini otomatis mendeteksi path folder projek melalui fungsi helper `base_url()`. Parameter koneksi dapat disesuaikan langsung atau via environment variables:

| Variabel | Nilai Bawaan | Keterangan |
| :--- | :--- | :--- |
| `DB_HOST` | `127.0.0.1` | Alamat host database |
| `DB_USER` | `root` | Pengguna database |
| `DB_PASS` | `""` (kosong) | Kata sandi database |
| `DB_NAME` | `penjualan_umkm` | Nama database |
| `RECAPTCHA_SITE_KEY` | *(Key bawaan)* | Google reCAPTCHA Site Key |
| `RECAPTCHA_SECRET_KEY`| *(Key bawaan)* | Google reCAPTCHA Secret Key |
| `TELEGRAM_BOT_TOKEN` | *(Terkonfigurasi)* | Token Bot Telegram untuk alert pesanan |
| `TELEGRAM_CHAT_ID` | *(Terkonfigurasi)* | Chat ID Telegram tujuan |

### Langkah 4: Menjalankan Web Server

#### Pilihan A: Menggunakan Laragon atau XAMPP
1. Letakkan folder projek di dalam direktori web server Anda (`C:\laragon\www\` atau `C:\xampp\htdocs\`).
2. Jalankan Apache dan MySQL.
3. Buka tautan berikut di browser:
   * Halaman Publik: `http://localhost/SIMPLE-RESTAURANT-WEBSITE/`
   * Panel Admin: `http://localhost/SIMPLE-RESTAURANT-WEBSITE/admin/`

#### Pilihan B: Menggunakan PHP Built-in Server
Buka terminal di dalam folder projek, lalu jalankan:
```bash
php -S localhost:8000
```
Buka alamat `http://localhost:8000` pada peramban Anda.

---

## 5. Panduan Penggunaan

### Alur Pelanggan (Pemesanan Menu)
1. Buka halaman utama atau buka katalog lengkap melalui menu navigasi (`/katalog.php`).
2. Pilih hidangan yang diinginkan lalu klik tombol **Pesan**.
3. Lengkapi formulir modal: isi nama pembeli, nomor WhatsApp aktif, jumlah porsi, dan alamat pengiriman lengkap.
4. Selesaikan centang verifikasi keamanan Google reCAPTCHA.
5. Klik **Konfirmasi & Kirim Pesanan**.
6. Sistem memotong stok produk di database, mencatat transaksi, dan membuka aplikasi WhatsApp pelanggan dengan teks pesanan terstruktur untuk diteruskan ke admin.

### Alur Pengelola (Manajemen Pesanan & Toko)
1. Akses halaman login admin di `/admin/` menggunakan akun default:
   * **Username**: `admin`
   * **Password**: `password`
2. **Memproses Pesanan Masuk**: Buka menu **Kelola Pesanan** untuk memeriksa rincian pesanan baru dan perbarui status pesanan menjadi Disetujui atau Selesai.
3. **Mengatur Menu**: Buka menu **Kelola Menu** untuk menambah hidangan baru, menyesuaikan harga, memperbarui porsi stok, atau menonaktifkan menu.
4. **Memperbarui Data Toko**: Buka menu **Pengaturan** untuk mengganti nomor WhatsApp penerima pesanan, alamat dapur, email layanan, atau kata sandi login admin.

---

## 6. Struktur Direktori Projek

```text
SIMPLE-RESTAURANT-WEBSITE/
├── admin/                 # Modul panel pengelola (dashboard, menu, orders, settings)
├── api/                   # Endpoint backend transaksi (create_order, check_orders, send_notif)
├── config/                # Konfigurasi database & helper global (db.php)
├── src/
│   ├── css/               # Berkas stylesheet & styling Tailwind
│   ├── img/               # Direktori foto menu makanan & aset grafis
│   └── js/                # Logika JavaScript client-side (index.js untuk modal & validasi)
├── database.sql           # Dump skema database relasional lengkap
├── detail.php             # Tampilan halaman detail menu
├── error.php              # Halaman error HTTP terpadu (400, 403, 404, 429, 500)
├── index.php              # Halaman beranda utama RasaNusa
├── katalog.php            # Halaman katalog menu lengkap dengan fitur cari
├── README.md              # Dokumentasi utama (Bahasa Inggris)
└── README.id.md           # Dokumentasi kedua (Bahasa Indonesia)
```

---

## 7. Lisensi

Dibuat untuk keperluan edukasi, penggunaan personal, dan implementasi operasional UMKM kuliner. Dikembangkan oleh **Dannys Martha Favrillia**.
