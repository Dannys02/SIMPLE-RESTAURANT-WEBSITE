<?php
define('AKSES_AMAN', true);
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php"); exit;
}
include '../config/db.php';

$page = $_GET['page'] ?? 'dashboard';
$routes = [
  'dashboard'      => 'dashboard.php',
  'menu'           => 'crud_produk.php',
  'produk'         => 'crud_produk.php', // Alias agar link index.php?page=produk tetap valid
  'menu_edit'      => 'edit_produk.php',
  'testimoni'      => 'crud_testimoni.php',
  'testimoni_edit' => 'edit_testimoni.php',
  'orders'         => 'orders.php',
  'edit_order'     => 'edit_order.php',
  'pengaturan'     => 'pengaturan.php',
];

if (!array_key_exists($page, $routes)) {
  http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin UMKM - <?= ucfirst(htmlspecialchars($page)) ?></title>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <style>
    @media print {
      /* Sembunyikan Sidebar, Navbar, dan Kolom Aksi saat cetak */
      .sidebar, .navbar, .no-print, th:last-child, td:last-child {
        display: none !important;
      }

      /* Buat tabel memenuhi lebar kertas */
      .bg-white {
        box-shadow: none !important;
        border: none !important;
      }

      table {
        width: 100% !important;
        border: 1px solid #ddd !important;
      }

      h1 {
        text-align: center;
        margin-bottom: 20px;
      }
    }
  </style>

</head>
<body class="bg-slate-100 pb-20">

  <main class="max-w-6xl mx-auto p-4 md:p-8">
    <div class="content-wrapper">
      <?php
      if (array_key_exists($page, $routes)) {
        include $routes[$page];
      } else {
      ?>
        <section class="max-w-xl mx-auto my-12 bg-white p-8 rounded-2xl border border-slate-200">
          <span class="inline-block px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-md mb-3">
            HTTP Error 404
          </span>
          <h1 class="text-2xl font-extrabold text-slate-900 mb-2">Halaman Admin Tidak Ditemukan</h1>
          <p class="text-slate-600 text-sm leading-relaxed mb-6">
            Rute halaman <code class="bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-800">?page=<?= htmlspecialchars($page) ?></code> tidak terdaftar di sistem manajemen admin.
          </p>
          <a href="index.php?page=dashboard" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl transition">
            Kembali ke Dashboard
          </a>
        </section>
      <?php
      }
      ?>
    </div>
  </main>

  <nav class="fixed bottom-0 left-0 z-50 w-full bg-white border-t border-gray-100 shadow-lg">
    <div class="flex justify-around items-center h-16">
      <a href="index.php?page=dashboard" class="flex-1 flex flex-col items-center justify-center h-full bg-slate-50 hover:bg-gray-100 <?= $page == 'dashboard' ? 'text-blue-600 border-t-2 border-blue-600 bg-blue-50' : 'text-gray-500' ?>">
        <span class="text-[10px] font-bold uppercase">Dashboard</span>
      </a>
      <a href="index.php?page=menu" class="flex-1 flex flex-col items-center justify-center h-full bg-slate-50 hover:bg-gray-100 <?= $page == 'menu' || $page == 'produk' || $page == 'menu_edit' ? 'text-blue-600 border-t-2 border-blue-600 bg-blue-50' : 'text-gray-500' ?>">
        <span class="text-[10px] font-bold uppercase">Menu</span>
      </a>
      <a href="index.php?page=testimoni" class="flex-1 flex flex-col items-center justify-center h-full bg-slate-50 hover:bg-gray-100 <?= $page == 'testimoni' || $page == 'testimoni_edit' ? 'text-blue-600 border-t-2 border-blue-600 bg-blue-50' : 'text-gray-500' ?>">
        <span class="text-[10px] font-bold uppercase">Testimoni</span>
      </a>
      <a href="index.php?page=orders" class="flex-1 flex flex-col items-center justify-center h-full bg-slate-50 hover:bg-gray-100 <?= $page == 'orders' || $page == 'edit_order' || $page == 'cetak_laporan' ? 'text-blue-600 border-t-2 border-blue-600 bg-blue-50' : 'text-gray-500' ?>">
        <span class="text-[10px] font-bold uppercase">Pesanan</span>
      </a>
      <a href="index.php?page=pengaturan" class="flex-1 flex flex-col items-center justify-center h-full bg-slate-50 hover:bg-gray-100 <?= $page == 'pengaturan' ? 'text-blue-600 border-t-2 border-blue-600 bg-blue-50' : 'text-gray-500' ?>">
        <span class="text-[10px] font-bold uppercase">Pengaturan</span>
      </a>
      <a href="logout.php" class="flex-1 flex flex-col items-center justify-center h-full text-red-500">
        <span class="text-[10px] font-bold uppercase">Keluar</span>
      </a>
    </div>
  </nav>

  <script>
    function handleSetuju(url, waLink) {
      if (confirm('Setujui pesanan dan hubungi via WhatsApp?')) {
        window.open(waLink, '_blank');
        window.location.href = url;
      }
    }

    $(document).ready(function() {
      $('#tabelPesanan').DataTable({
        // Kamu bisa ganti selector ini jadi '.tabel-data' jika ingin seragam
        "dom": 'tp',
        "ordering": false,
        "pageLength": 5,
        "language": {
          "emptyTable": "Data masih kosong!",
          "paginate": {
            "previous": "← Kembali",
            "next": "Lanjut →"
          }
        },
        "drawCallback": function(settings) {
          // 1. Ambil ID tabel yang sedang diproses agar tidak salah sasaran
          var api = this.api();
          var tableId = settings.sTableId;

          // 2. Hitung jumlah kolom dari header secara otomatis
          var jumlahKolom = api.columns().header().length;

          // 3. Styling baris kosong secara dinamis
          $('#' + tableId + ' .dataTables_empty')
          .addClass('text-center p-4 text-slate-600 font-medium italic')
          .attr('colspan', jumlahKolom); // <--- Sekarang otomatis (bisa 3, 4, 5, dst)

          // Styling Paginasi bawaan kamu
          $('.dataTables_paginate').addClass('w-full flex justify-center py-6 px-12 items-center gap-1 whitespace-nowrap');
          $('.paginate_button').addClass('px-3 py-2 bg-slate-100 rounded-md mx-1 text-xs font-bold hover:bg-orange-500 hover:text-white transition-colors');
          $('.paginate_button.current').addClass('bg-orange-600 text-white');
        },
      });
    });

  </script>
  <script src="../src/js/orders.js"></script>
</body>
</html>