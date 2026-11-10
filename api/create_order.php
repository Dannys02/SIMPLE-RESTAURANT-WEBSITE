<?php
/**
 * ============================================
 * PROSES PEMBUATAN PESANAN BARU
 * ============================================
 * Langkah-langkah validasi:
 * 1. Verifikasi reCAPTCHA (anti-bot Google)
 * 2. Deteksi bot silent (perangkap form)
 * 3. Rate limiting (max 5 order/menit per session)
 * 4. Sanitasi & validasi input
 * 5. Cek ketersediaan stok
 * 6. Simpan ke database dengan Prepared Statement
 * 7. Kirim notifikasi WhatsApp ke admin & pelanggan
 */

session_start();
include __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort_error(
        403,
        'Metode Tidak Diizinkan',
        'Endpoint pemesanan ini hanya dapat diakses melalui pengiriman formulir pesanan.',
        base_url(),
        'Kembali ke Beranda'
    );
}

// Ambil nomor admin WhatsApp untuk notifikasi
$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = mysqli_fetch_assoc($q_wa);
$nomor_tujuan = $row_wa['nomor'] ?? '6285645837298';

// ========== PROSES ORDER (HANYA JIKA POST) ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // ===== LANGKAH 1: VERIFIKASI reCAPTCHA (Anti-Bot) =====
    if (empty($_POST['g-recaptcha-response'])) {
        abort_error(400, 'Verifikasi Keamanan Diperlukan', 'Mohon centang kotak reCAPTCHA terlebih dahulu sebelum mengirim pesanan.', base_url('#menu'), 'Kembali ke Menu');
    }

    $secret = "6LcBT4wsAAAAAL_pBRW0tYfRCa9PQLIhQcb7kbm8";
    $response = $_POST['g-recaptcha-response'];

    // Verifikasi token reCAPTCHA ke server Google
    $verify = @file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response"
    );
    $captcha = $verify ? json_decode($verify) : null;

    if (!$captcha || empty($captcha->success)) {
        abort_error(400, 'Verifikasi Anti-Bot Gagal', 'Verifikasi reCAPTCHA tidak valid atau sudah kedaluwarsa. Silakan ulangi pemesanan Anda.', base_url('#menu'), 'Kembali ke Menu');
    }

    // ===== LANGKAH 2: DETEKSI BOT SILENT =====
    // Honeypot field - jika di-isi, berarti bot
    if (!empty($_POST['perangkap'])) {
        abort_error(403, 'Akses Ditolak', 'Permintaan Anda terdeteksi sebagai aktivitas otomatis.', base_url(), 'Kembali ke Beranda');
    }

    // ===== LANGKAH 3: CEK KECEPATAN PENGIRIMAN (Anti-Spam) =====
    $load_time = $_SESSION['load_time'] ?? 0;
    if ((time() - $load_time) < 2) {
        abort_error(429, 'Pengiriman Terlalu Cepat', 'Mohon isi formulir pesanan dengan wajar sebelum menekan tombol kirim.', base_url('#menu'), 'Kembali ke Menu');
    }

    // ===== LANGKAH 4: RATE LIMITING (Max 5 order per menit) =====
    if (!isset($_SESSION['order_count'])) {
        $_SESSION['order_count'] = 0;
        $_SESSION['first_order_time'] = time();
    }

    // Reset counter jika sudah lewat 1 menit
    if (time() - $_SESSION['first_order_time'] > 60) {
        $_SESSION['order_count'] = 0;
        $_SESSION['first_order_time'] = time();
    }

    // Cek jika sudah mencapai limit
    if ($_SESSION['order_count'] >= 5) {
        abort_error(429, 'Batas Pemesanan Tercapai', 'Anda telah mencapai batas maksimal 5 pesanan per menit. Silakan tunggu 1 menit lagi.', base_url('#menu'), 'Kembali ke Menu');
    }

    // ===== LANGKAH 5: SANITASI & VALIDASI INPUT =====
    $produk_id = (int)($_POST['produk_id'] ?? 0);
    $nama = trim(htmlspecialchars($_POST['nama_pembeli'] ?? ''));
    $wa_pembeli = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');
    $stok = (int)($_POST['stok'] ?? 0);
    $harga = (int)($_POST['harga'] ?? 0);
    $alamat = trim(htmlspecialchars($_POST['alamat'] ?? ''));

    // Validasi nama minimal 3 karakter
    if (strlen($nama) < 3) {
        abort_error(400, 'Nama Pembeli Tidak Valid', 'Nama pembeli harus terdiri dari minimal 3 karakter.', base_url('#menu'), 'Kembali ke Menu');
    }

    // Validasi nomor WhatsApp (10-14 digit)
    if (strlen($wa_pembeli) < 10 || strlen($wa_pembeli) > 14) {
        abort_error(400, 'Nomor WhatsApp Tidak Valid', 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit angka.', base_url('#menu'), 'Kembali ke Menu');
    }

    // Validasi alamat minimal 10 karakter
    if (strlen($alamat) < 10) {
        abort_error(400, 'Alamat Belum Lengkap', 'Alamat pengiriman harus terdiri dari minimal 10 karakter agar kurir mudah menemukan lokasi Anda.', base_url('#menu'), 'Kembali ke Menu');
    }

    // ===== LANGKAH 6: CEK STOK PRODUK (Dengan Prepared Statement) =====
    $stmt_stok = mysqli_prepare($koneksi, "SELECT nama, stok FROM produk WHERE id = ?");
    mysqli_stmt_bind_param($stmt_stok, "i", $produk_id);
    mysqli_stmt_execute($stmt_stok);
    $data_produk = mysqli_stmt_get_result($stmt_stok)->fetch_assoc();

    // Cek apakah produk ada dan stok cukup
    if (!$data_produk || $data_produk['stok'] < $stok || $stok <= 0) {
        abort_error(400, 'Stok Menu Tidak Mencukupi', 'Maaf, stok menu yang Anda pilih sedang habis atau jumlah pesanan melebihi stok yang tersedia.', base_url('#menu'), 'Pilih Menu Lain');
    }

    // ===== LANGKAH 7: SIMPAN PESANAN KE DATABASE =====
    $query = "INSERT INTO pesanan (nama_pembeli, whatsapp, stok, harga, alamat, produk_id, status, user_ip) 
              VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
    $stmt_ins = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt_ins, "ssiisis", $nama, $wa_pembeli, $stok, $harga, $alamat, $produk_id, $user_ip);

    if (mysqli_stmt_execute($stmt_ins)) {
        $_SESSION['order_count']++;
        $nama_produk = $data_produk['nama'];

        // ===== LANGKAH 8: BUAT PESAN NOTIFIKASI UNTUK ADMIN =====
        $nomor_admin = $nomor_tujuan;
        $pesan = "Halo Admin, saya ingin memesan 🙋‍♂️\n"
            . "------------------------------------------\n"
            . "Berikut adalah data pesanan saya:\n\n"
            . "📑 *DETAIL PESANAN*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🍱 *Menu:* " . $nama_produk . "\n"
            . "🔢 *Jumlah:* " . $stok . " porsi\n"
            . "💰 *Total Estimasi:* Rp " . number_format($harga, 0, ',', '.') . "\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"
            . "🚚 *DATA PENGIRIMAN*\n"
            . "👤 *Nama:* " . $nama . "\n"
            . "📍 *Alamat:* " . $alamat . "\n"
            . "📱 *WhatsApp:* " . $wa_pembeli . "\n\n"
            . "------------------------------------------\n"
            . "Mohon segera diproses ya Min. Terima kasih! 🙏";

        // Encode pesan untuk URL (WhatsApp API)
        $url_wa = "https://api.whatsapp.com/send?phone=" . $nomor_admin . "&text=" . urlencode($pesan);
        $home_menu_url = base_url('#menu');

        // ===== LANGKAH 9: REDIRECT KE WhatsApp ADMIN & KEMBALI KE HALAMAN UTAMA =====
        echo "<script>
                alert('Pesanan Berhasil Dikirim, hubungi dan tunggu respon Admin!');
                window.open('$url_wa', '_blank');
                window.location.href = '$home_menu_url';
              </script>";
    } else {
        abort_error(500, 'Gagal Menyimpan Pesanan', 'Terjadi kesalahan saat menyimpan pesanan Anda ke database. Silakan coba beberapa saat lagi.', base_url('#menu'), 'Kembali ke Menu');
    }
}