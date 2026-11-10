<?php
/**
 * ============================================
 * API: CEK JUMLAH PESANAN PENDING
 * ============================================
 * Fungsi: Endpoint AJAX yang mengembalikan jumlah pesanan
 *         dengan status 'pending' dalam format JSON
 * 
 * Respons: {"total_pending": <number>}
 * 
 * Digunakan untuk: Real-time notification dashboard admin
 */

include __DIR__ . '/../config/db.php';

// Query hitung pesanan dengan status pending
$q = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'pending'");
$res = mysqli_fetch_assoc($q);

// Return hasil dalam format JSON
header('Content-Type: application/json');
echo json_encode(['total_pending' => (int)($res['total'] ?? 0)]);
?>