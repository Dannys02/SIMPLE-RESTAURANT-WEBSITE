-- ==========================================================
-- SKEMA DATABASE & DATA AWAL RASANUSA (penjualan_umkm)
-- Cara pakai: Import file ini melalui phpMyAdmin pada tab Import
-- ==========================================================

CREATE DATABASE IF NOT EXISTS penjualan_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE penjualan_umkm;

-- 1. Tabel Admin Pengelola
CREATE TABLE IF NOT EXISTS admins (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Nomor WhatsApp Admin
CREATE TABLE IF NOT EXISTS admins_wa (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nomor VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Katalog Produk (Menu)
CREATE TABLE IF NOT EXISTS produk (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  harga INT(11) NOT NULL,
  stok INT(11) NOT NULL DEFAULT 0,
  deskripsi TEXT,
  gambar VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Pesanan Pelanggan
CREATE TABLE IF NOT EXISTS pesanan (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  produk_id INT(11) NOT NULL,
  nama_pembeli VARCHAR(50) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  stok INT(11) NOT NULL,
  harga INT(11) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  status ENUM('pending', 'setuju', 'selesai', 'tolak', 'dibatalkan') NOT NULL DEFAULT 'pending',
  user_ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pesanan_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Ulasan / Testimoni Pelanggan
CREATE TABLE IF NOT EXISTS testimoni (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nama_pelanggan VARCHAR(100) NOT NULL,
  pekerjaan VARCHAR(100) NOT NULL,
  isi TEXT NOT NULL,
  bintang INT(1) NOT NULL DEFAULT 5,
  tampil TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Wajib:
-- Akun Admin Default -> Username: admin | Password: password
INSERT INTO admins (id, username, password)
VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Nomor WhatsApp Default Admin
INSERT INTO admins_wa (id, nomor)
VALUES (1, '6285645837298')
ON DUPLICATE KEY UPDATE nomor = VALUES(nomor);
