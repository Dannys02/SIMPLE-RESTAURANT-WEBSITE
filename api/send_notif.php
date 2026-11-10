<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    abort_error(403, 'Akses Ditolak', 'Endpoint notifikasi ini hanya dapat diakses oleh pengelola yang sedang masuk.', base_url('admin/login.php'), 'Masuk Admin');
}

$jumlah = isset($_GET['jumlah']) ? (int)$_GET['jumlah'] : 0;

if ($jumlah > 0 && !empty($telegramToken) && !empty($telegramChat)) {
    $pesan = "Admin RasaNusa, terdapat {$jumlah} pesanan pending baru di website.";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    @file_get_contents(
        "https://api.telegram.org/bot{$telegramToken}/sendMessage?chat_id=" . urlencode($telegramChat) . "&text=" . urlencode($pesan),
        false,
        $ctx
    );
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'notified_count' => $jumlah]);
?>
