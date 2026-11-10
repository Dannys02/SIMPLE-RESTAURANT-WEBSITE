<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';

date_default_timezone_set('Asia/Jakarta');

$query = "SELECT p.*, pr.nama as nama_produk
          FROM pesanan p
          JOIN produk pr ON p.produk_id = pr.id
          WHERE p.status = 'selesai'
          ORDER BY p.created_at DESC";

$result = mysqli_query($koneksi, $query);
$total_rows = $result ? mysqli_num_rows($result) : 0;
$total_omzet = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan Selesai | RasaNusa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('src/css/style.css') ?>" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) di layar dan Putih bersih (#FFFFFF) pada lembar laporan cetak;
     *   Deep Slate (#0F172A) untuk teks tabel dan Terracotta Orange (#C2410C) untuk sorotan total pendapatan.
     * - Typography: 'Fraunces' untuk kop laporan RasaNusa dan 'Plus Jakarta Sans' untuk keterbacaan angka transaksi.
     */
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: #FAF7F2;
      color: #0F172A;
    }
    .font-serif-editorial {
      font-family: 'Fraunces', Georgia, serif;
    }
    table {
      border-collapse: collapse;
    }
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        background: #FFFFFF !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .print-container {
        border: none !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
      }
    }
  </style>
</head>
<body class="py-8 px-4 sm:px-6">

  <main class="print-container max-w-5xl mx-auto bg-white border border-stone-200 rounded-2xl p-6 sm:p-10">

    <!-- Baris Tombol Navigasi & Cetak (Disembunyikan saat Print) -->
    <div class="no-print flex flex-wrap justify-between items-center gap-4 mb-8 pb-6 border-b border-stone-200">
      <a
        href="index.php?page=orders"
        class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg border border-stone-300 bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-800 text-xs font-bold transition-colors"
      >
        Kembali ke Daftar Pesanan
      </a>

      <button
        type="button"
        onclick="window.print()"
        class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded-lg bg-orange-700 hover:bg-orange-800 text-white text-xs font-bold transition-colors"
      >
        Cetak / Simpan PDF
      </button>
    </div>

    <!-- Kop Laporan -->
    <header class="flex flex-col sm:flex-row sm:items-end justify-between border-b-2 border-slate-900 pb-6 mb-8 gap-4">
      <div>
        <div class="text-2xl font-extrabold tracking-tight text-slate-900 mb-1">
          <span class="font-serif-editorial text-orange-700">Rasa</span><span>Nusa</span>
        </div>
        <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">
          Laporan Rekapitulasi Penjualan
        </h1>
        <p class="text-xs text-slate-600 mt-1">
          Daftar transaksi pesanan pelanggan dengan status <strong>Selesai</strong>
        </p>
      </div>

      <div class="text-xs text-slate-600 sm:text-right">
        <p class="font-bold text-slate-900">Dicetak pada:</p>
        <p><?= date('d M Y, H:i') ?> WIB</p>
        <p class="mt-1">Oleh: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong></p>
      </div>
    </header>

    <?php if ($result && $total_rows > 0): ?>
    <div class="overflow-x-auto border border-stone-300 rounded-xl">
      <table class="w-full text-sm">
        <thead class="bg-[#F3EDE4] text-slate-800">
          <tr>
            <th class="border-b border-r border-stone-300 p-3.5 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
            <th class="border-b border-r border-stone-300 p-3.5 text-left text-xs font-bold uppercase tracking-wider">Nama Pembeli</th>
            <th class="border-b border-r border-stone-300 p-3.5 text-left text-xs font-bold uppercase tracking-wider">Hidangan</th>
            <th class="border-b border-r border-stone-300 p-3.5 text-center text-xs font-bold uppercase tracking-wider">Porsi</th>
            <th class="border-b border-stone-300 p-3.5 text-right text-xs font-bold uppercase tracking-wider">Subtotal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
          <?php while ($row = mysqli_fetch_assoc($result)):
            $subtotal = (int)$row['harga'] * (int)$row['stok'];
            $total_omzet += $subtotal;
          ?>
            <tr class="hover:bg-[#FAF7F2]">
              <td class="border-r border-stone-200 p-3.5 text-slate-700 whitespace-nowrap">
                <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
              </td>
              <td class="border-r border-stone-200 p-3.5 font-semibold text-slate-900">
                <?= htmlspecialchars($row['nama_pembeli'], ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="border-r border-stone-200 p-3.5 text-slate-800">
                <?= htmlspecialchars($row['nama_produk'], ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="border-r border-stone-200 p-3.5 text-center font-semibold text-slate-900">
                <?= (int)$row['stok'] ?>
              </td>
              <td class="p-3.5 text-right font-bold text-slate-900 whitespace-nowrap">
                Rp <?= number_format($subtotal, 0, ',', '.') ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr class="bg-[#F3EDE4] border-t-2 border-stone-300 font-bold text-base">
            <td colspan="4" class="border-r border-stone-300 p-4 text-left uppercase text-xs tracking-wider text-slate-800">
              Total Pendapatan (<?= $total_rows ?> Transaksi Selesai)
            </td>
            <td class="p-4 text-right text-orange-700 font-extrabold whitespace-nowrap">
              Rp <?= number_format($total_omzet, 0, ',', '.') ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php else: ?>
    <div class="p-8 text-center bg-[#FAF7F2] rounded-xl border border-stone-200">
      <h2 class="text-base font-bold text-slate-900 mb-1">Belum Ada Transaksi Selesai</h2>
      <p class="text-xs text-slate-600">Laporan penjualan akan terisi secara otomatis setelah ada pesanan yang ditandai Selesai.</p>
    </div>
    <?php endif; ?>

  </main>

  <script>
    window.onload = function() {
      window.print();
    };
  </script>
</body>
</html>
