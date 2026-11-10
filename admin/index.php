<?php
define('AKSES_AMAN', true);
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit;
}
include '../config/db.php';

$page = $_GET['page'] ?? 'dashboard';
$routes = [
  'dashboard'      => 'dashboard.php',
  'menu'           => 'crud_produk.php',
  'produk'         => 'crud_produk.php',
  'menu_edit'      => 'edit_produk.php',
  'testimoni'      => 'crud_testimoni.php',
  'testimoni_edit' => 'edit_testimoni.php',
  'orders'         => 'orders.php',
  'edit_order'     => 'edit_order.php',
  'pengaturan'     => 'pengaturan.php',
];

$page_titles = [
  'dashboard'      => 'Ringkasan Operasional',
  'menu'           => 'Manajemen Menu',
  'produk'         => 'Manajemen Menu',
  'menu_edit'      => 'Edit Rincian Menu',
  'testimoni'      => 'Manajemen Ulasan',
  'testimoni_edit' => 'Edit Ulasan Pelanggan',
  'orders'         => 'Pesanan Masuk',
  'edit_order'     => 'Edit Data Pesanan',
  'pengaturan'     => 'Pengaturan Akun & Kontak',
];

if (!array_key_exists($page, $routes)) {
  http_response_code(404);
}

// Hitung jumlah pesanan pending untuk indikator navigasi
$q_nav_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'pending'");
$nav_pending_count = $q_nav_pending ? (int)(mysqli_fetch_assoc($q_nav_pending)['total'] ?? 0) : 0;

$current_title = $page_titles[$page] ?? 'Halaman Tidak Ditemukan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($current_title, ENT_QUOTES, 'UTF-8') ?> | Admin RasaNusa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('src/css/style.css') ?>" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) sebagai latar ruang kerja admin, Deep Slate (#0F172A) untuk sidebar kiri agar navigasi jelas terpisah dari konten,
     *   dan Terracotta Orange (#C2410C) sebagai satu aksen terfokus pada menu aktif & tindakan utama.
     * - Typography: 'Fraunces' untuk judul halaman utama dan identitas brand; 'Plus Jakarta Sans' untuk tabel data, angka omzet, dan kontrol form.
     * - Layout: Sidebar kiri tetap (264px) pada layar desktop (lg:) yang berubah menjadi off-canvas drawer pada layar mobile/tablet (<1024px).
     */
    :root {
      --bg-warm: #FAF7F2;
      --bg-stone: #F3EDE4;
      --ink-primary: #0F172A;
      --ink-secondary: #475569;
      --accent-orange: #C2410C;
    }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: var(--bg-warm);
      color: var(--ink-primary);
    }
    .font-serif-editorial {
      font-family: 'Fraunces', Georgia, serif;
    }
    .section-index {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--accent-orange);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .section-index::before {
      content: "";
      display: inline-block;
      width: 1.25rem;
      height: 2px;
      background-color: var(--accent-orange);
    }
    @media print {
      .sidebar, .mobile-topbar, .no-print, th:last-child, td:last-child {
        display: none !important;
      }
      .admin-main {
        margin-left: 0 !important;
        padding: 0 !important;
      }
      .bg-white {
        box-shadow: none !important;
        border: none !important;
      }
      table {
        width: 100% !important;
        border: 1px solid #d6d3d1 !important;
      }
    }
  </style>
</head>
<body class="antialiased min-h-dvh flex flex-col lg:flex-row selection:bg-orange-700 selection:text-white">

  <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:bg-orange-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:font-bold">
    Lewati ke konten admin
  </a>

  <!-- Topbar Khusus Mobile & Tablet (< 1024px) -->
  <header class="mobile-topbar lg:hidden sticky top-0 z-40 bg-slate-900 text-white border-b border-slate-800 px-4 h-16 flex items-center justify-between">
    <a href="index.php?page=dashboard" class="flex items-baseline gap-0.5 text-xl font-extrabold tracking-tight">
      <span class="font-serif-editorial text-orange-400">Rasa</span><span>Nusa</span>
    </a>

    <div class="flex items-center gap-2">
      <?php if ($nav_pending_count > 0): ?>
      <a href="index.php?page=orders" class="min-h-[40px] inline-flex items-center px-2.5 py-1 rounded-lg bg-orange-700 text-white text-xs font-bold">
        <?= $nav_pending_count ?> Pending
      </a>
      <?php endif; ?>
      <button
        type="button"
        id="sidebarToggleBtn"
        onclick="toggleAdminSidebar()"
        aria-label="Buka menu navigasi admin"
        aria-expanded="false"
        class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center gap-2 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold border border-slate-700"
      >
        <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span>Menu</span>
      </button>
    </div>
  </header>

  <!-- Backdrop Overlay untuk Sidebar Mobile -->
  <div id="sidebarBackdrop" onclick="toggleAdminSidebar()" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm hidden lg:hidden" aria-hidden="true"></div>

  <!-- Sidebar Kiri (Fixed di Desktop, Off-canvas Drawer di Mobile) -->
  <aside
    id="adminSidebar"
    aria-label="Menu Samping Admin"
    class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 border-r border-slate-800 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
  >
    <div>
      <!-- Brand Header Sidebar -->
      <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800">
        <a href="index.php?page=dashboard" class="flex items-baseline gap-0.5 text-2xl font-extrabold tracking-tight text-white text-center">
          <span class="font-serif-editorial text-orange-400">Rasa</span><span>Nusa</span>
        </a>

        <button
          type="button"
          onclick="toggleAdminSidebar()"
          class="lg:hidden min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-sm font-bold"
          aria-label="Tutup menu samping"
        >
          ✕
        </button>
      </div>

      <!-- Daftar Menu Navigasi -->
      <nav class="p-4 space-y-1.5" aria-label="Navigasi Panel Admin">
        <?php
        $is_dashboard = ($page === 'dashboard');
        $is_orders    = in_array($page, ['orders', 'edit_order', 'cetak_laporan'], true);
        $is_menu      = in_array($page, ['menu', 'produk', 'menu_edit'], true);
        $is_testi     = in_array($page, ['testimoni', 'testimoni_edit'], true);
        $is_settings  = ($page === 'pengaturan');
        ?>
        <a
          href="index.php?page=dashboard"
          <?= $is_dashboard ? 'aria-current="page"' : '' ?>
          class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-bold transition-colors <?= $is_dashboard ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
        >
          <span class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Ringkasan</span>
          </span>
        </a>

        <a
          href="index.php?page=orders"
          <?= $is_orders ? 'aria-current="page"' : '' ?>
          class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-bold transition-colors <?= $is_orders ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
        >
          <span class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Pesanan Masuk</span>
          </span>
          <?php if ($nav_pending_count > 0): ?>
          <span class="px-2 py-0.5 text-xs font-extrabold rounded-full <?= $is_orders ? 'bg-white text-orange-800' : 'bg-orange-700 text-white' ?>">
            <?= $nav_pending_count ?>
          </span>
          <?php endif; ?>
        </a>

        <a
          href="index.php?page=menu"
          <?= $is_menu ? 'aria-current="page"' : '' ?>
          class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-bold transition-colors <?= $is_menu ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
        >
          <span class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Menu &amp; Stok</span>
          </span>
        </a>

        <a
          href="index.php?page=testimoni"
          <?= $is_testi ? 'aria-current="page"' : '' ?>
          class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-bold transition-colors <?= $is_testi ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
        >
          <span class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <span>Ulasan Pelanggan</span>
          </span>
        </a>

        <a
          href="index.php?page=pengaturan"
          <?= $is_settings ? 'aria-current="page"' : '' ?>
          class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-bold transition-colors <?= $is_settings ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
        >
          <span class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Pengaturan</span>
          </span>
        </a>
      </nav>
    </div>

    <!-- Bagian Bawah Sidebar: Link Website Publik & Logout -->
    <div class="p-4 border-t border-slate-800 space-y-2">
      <a
        href="<?= base_url() ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="min-h-[44px] flex items-center justify-between px-3.5 py-2.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-800 transition-colors"
      >
        <span>Lihat Website Publik</span>
        <span aria-hidden="true">↗</span>
      </a>
      <a
        href="logout.php"
        onclick="return confirm('Keluar dari sesi pengelola RasaNusa?')"
        class="min-h-[44px] flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-bold text-red-300 hover:bg-red-950/60 hover:text-red-200 transition-colors"
      >
        <svg class="w-5 h-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span>Keluar Sesi</span>
      </a>
    </div>
  </aside>

  <!-- Area Konten Utama Admin -->
  <div class="admin-main flex-1 lg:ml-64 flex flex-col min-h-dvh">
    <main id="admin-content" class="flex-1 w-full max-w-6xl mx-auto p-4 sm:p-6 md:p-8 lg:p-10">
      <?php
      if (array_key_exists($page, $routes)) {
        include $routes[$page];
      } else {
      ?>
        <section class="max-w-xl mx-auto my-12 bg-white p-8 rounded-2xl border border-stone-200">
          <span class="inline-block px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-md mb-3">
            HTTP Error 404
          </span>
          <h1 class="font-serif-editorial text-2xl font-bold text-slate-900 mb-2">Halaman Admin Tidak Ditemukan</h1>
          <p class="text-slate-600 text-sm leading-relaxed mb-6">
            Rute halaman <code class="bg-[#FAF7F2] border border-stone-200 px-2 py-0.5 rounded font-mono text-slate-800">?page=<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?></code> tidak terdaftar di dalam sistem manajemen RasaNusa.
          </p>
          <a href="index.php?page=dashboard" class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white font-bold text-sm rounded-lg transition">
            Kembali ke Ringkasan
          </a>
        </section>
      <?php
      }
      ?>
    </main>

    <footer class="no-print border-t border-stone-200/80 py-5 px-6 text-xs text-slate-600 flex flex-col sm:flex-row items-center justify-between gap-2">
      <span>&copy; 2026 RasaNusa. Panel Pengelola Dapur &amp; Katalog UMKM.</span>
      <span>Gunakan navigasi kiri untuk berpindah modul.</span>
    </footer>
  </div>

  <script>
    function toggleAdminSidebar() {
      const sidebar = document.getElementById('adminSidebar');
      const backdrop = document.getElementById('sidebarBackdrop');
      const btn = document.getElementById('sidebarToggleBtn');
      const isClosed = sidebar.classList.contains('-translate-x-full');

      if (isClosed) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
    }

    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
          toggleAdminSidebar();
        }
      }
    });

    $(document).ready(function() {
      if ($('#tabelPesanan').length) {
        $('#tabelPesanan').DataTable({
          "dom": 'tp',
          "ordering": false,
          "pageLength": 6,
          "language": {
            "emptyTable": "Belum ada data yang tercatat pada tabel ini.",
            "paginate": {
              "previous": "Sebelumnya",
              "next": "Berikutnya"
            }
          },
          "drawCallback": function(settings) {
            var api = this.api();
            var tableId = settings.sTableId;
            var jumlahKolom = api.columns().header().length;

            $('#' + tableId + ' .dataTables_empty')
              .addClass('text-center p-8 text-slate-600 text-sm font-medium')
              .attr('colspan', jumlahKolom);

            $('.dataTables_paginate').addClass('w-full flex flex-wrap justify-center sm:justify-end py-4 px-6 items-center gap-1.5 border-t border-stone-200 bg-[#FAF7F2]');
            $('.paginate_button').addClass('min-h-[38px] inline-flex items-center px-3.5 py-1.5 bg-white border border-stone-300 rounded-lg text-xs font-bold text-slate-700 hover:bg-stone-100 transition-colors cursor-pointer');
            $('.paginate_button.current').removeClass('bg-white text-slate-700').addClass('bg-orange-700 text-white border-orange-700');
          }
        });
      }
    });
  </script>
  <script src="<?= base_url('src/js/orders.js') ?>"></script>
</body>
</html>
