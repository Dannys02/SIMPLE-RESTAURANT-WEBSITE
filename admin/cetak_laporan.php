<?php

/**
 * ============================================
 * HALAMAN CETAK/LAPORAN PENJUALAN
 * ============================================
 * Fungsi: Menampilkan laporan semua pesanan yang sudah SELESAI
 *         Dalam format yang bisa dicetak atau save sebagai PDF
 */

// Ambil data hanya yang statusnya 'selesai'
include '../config/db.php';

// Date Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

$query = "SELECT p.*, pr.nama as nama_produk
          FROM pesanan p
          JOIN produk pr ON p.produk_id = pr.id
          WHERE p.status = 'selesai'
          ORDER BY p.created_at DESC";

$result = mysqli_query($koneksi, $query);

$total_omzet = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #111827;
        }

        table {
            border-collapse: collapse;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 py-10 px-5">

    <!-- Container -->
    <div class="print-container max-w-6xl mx-auto bg-white border border-gray-200 rounded-xl shadow-md p-8">

        <!-- Tombol -->
        <div class="no-print flex justify-between items-center mb-8">
            <a href="index.php?page=orders"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg transition">
                ← Kembali
            </a>

            <!-- <button onclick="window.print()"
                class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg shadow">
                Cetak / Save PDF
            </button> -->
        </div>

        <!-- Header -->
        <div class="text-center border-b-2 border-gray-800 pb-5 mb-8">
            <h1 class="text-3xl font-black uppercase tracking-wide">
                Laporan Penjualan
            </h1>

            <p class="text-sm text-gray-500 mt-2">
                Data pesanan dengan status selesai
            </p>

            <p class="text-sm text-gray-500">
                Dicetak pada:
                <?= date('d F Y, H:i') ?> WIB
            </p>
        </div>

        <!-- Tabel -->
        <div class="overflow-hidden border border-gray-300 rounded-lg">
            <table class="w-full text-sm">

                <!-- Head -->
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 p-3 text-left">
                            Tanggal
                        </th>

                        <th class="border border-gray-300 p-3 text-left">
                            Pembeli
                        </th>

                        <th class="border border-gray-300 p-3 text-left">
                            Produk
                        </th>

                        <th class="border border-gray-300 p-3 text-center">
                            Qty
                        </th>

                        <th class="border border-gray-300 p-3 text-right">
                            Total Harga
                        </th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)):

                        $subtotal = $row['harga'] * $row['stok'];
                        $total_omzet += $subtotal;

                    ?>

                        <tr class="hover:bg-gray-50">

                            <!-- Tanggal -->
                            <td class="border border-gray-300 p-3">
                                <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                            </td>

                            <!-- Pembeli -->
                            <td class="border border-gray-300 p-3">
                                <?= htmlspecialchars($row['nama_pembeli']) ?>
                            </td>

                            <!-- Produk -->
                            <td class="border border-gray-300 p-3">
                                <?= htmlspecialchars($row['nama_produk']) ?>
                            </td>

                            <!-- Qty -->
                            <td class="border border-gray-300 p-3 text-center">
                                <?= $row['stok'] ?>
                            </td>

                            <!-- Harga -->
                            <td class="border border-gray-300 p-3 text-right font-medium">
                                Rp <?= number_format($subtotal, 0, ',', '.') ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>
                </tbody>

                <!-- Footer -->
                <tfoot>

                    <tr class="bg-gray-100 font-bold text-base">

                        <td colspan="4"
                            class="border border-gray-300 p-4 text-left uppercase">
                            Total Pendapatan
                        </td>

                        <td class="border border-gray-300 p-4 text-right text-orange-600">
                            Rp <?= number_format($total_omzet, 0, ',', '.') ?>
                        </td>

                    </tr>

                </tfoot>

            </table>
        </div>

        <!-- Footer bawah -->
        <!-- <div class="mt-12 flex justify-end">
            <div class="text-center">
                <p class="mb-16">Mengetahui,</p>
                <p class="font-semibold underline">( Admin )</p>
            </div>
        </div> -->

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>