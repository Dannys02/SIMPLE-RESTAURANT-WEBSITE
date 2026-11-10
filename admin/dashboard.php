<?php
if (!defined('AKSES_AMAN')) {
  require_once __DIR__ . '/../config/db.php';
  abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('d M Y');
if (class_exists('IntlDateFormatter')) {
  $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
  $formatter->setPattern('EEEE, dd MMM yyyy');
  $tanggal_hari_ini = $formatter->format(new DateTime());
}

// Hitung order pending
$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'pending'");
$count_pending = $q_pending ? (int)(mysqli_fetch_assoc($q_pending)['total'] ?? 0) : 0;

// Hitung total produk & stok menipis (< 5)
$q_produk = mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(IF(stok < 5, 1, 0)) as menipis FROM produk");
$res_produk = $q_produk ? mysqli_fetch_assoc($q_produk) : ['total' => 0, 'menipis' => 0];
$count_produk = (int)($res_produk['total'] ?? 0);
$stok_menipis = (int)($res_produk['menipis'] ?? 0);

// Hitung testimoni
$q_testi_count = mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(IF(tampil = 1, 1, 0)) as aktif FROM testimoni");
$res_testi_count = $q_testi_count ? mysqli_fetch_assoc($q_testi_count) : ['total' => 0, 'aktif' => 0];
$count_testi = (int)($res_testi_count['total'] ?? 0);
$testi_aktif = (int)($res_testi_count['aktif'] ?? 0);

// Hitung pendapatan selesai
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(harga * stok) as total FROM pesanan WHERE status = 'selesai'");
$total_pendapatan = $q_pendapatan ? (int)(mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0) : 0;

$all_testi = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC LIMIT 5");

$query_top = "SELECT pr.nama, SUM(p.stok) as total
              FROM pesanan p
              JOIN produk pr ON p.produk_id = pr.id
              WHERE p.status = 'selesai'
              GROUP BY p.produk_id
              ORDER BY total DESC LIMIT 1";
$res_top = mysqli_query($koneksi, $query_top);
$top_item = $res_top ? mysqli_fetch_assoc($res_top) : null;

// Pendapatan Harian, Mingguan, Bulanan
$q_harian = mysqli_query($koneksi, "SELECT SUM(harga * stok) as total FROM pesanan 
             WHERE status = 'selesai' AND DATE(created_at) = CURDATE()");
$total_harian = $q_harian ? (int)(mysqli_fetch_assoc($q_harian)['total'] ?? 0) : 0;

$q_mingguan = mysqli_query($koneksi, "SELECT SUM(harga * stok) as total FROM pesanan 
              WHERE status = 'selesai' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)");
$total_mingguan = $q_mingguan ? (int)(mysqli_fetch_assoc($q_mingguan)['total'] ?? 0) : 0;

$q_bulanan = mysqli_query($koneksi, "SELECT SUM(harga * stok) as total FROM pesanan 
             WHERE status = 'selesai' AND MONTH(created_at) = MONTH(CURDATE()) 
             AND YEAR(created_at) = YEAR(CURDATE())");
$total_bulanan = $q_bulanan ? (int)(mysqli_fetch_assoc($q_bulanan)['total'] ?? 0) : 0;
?>

<!-- Header Halaman Dashboard -->
<header class="flex flex-col sm:flex-row sm:justify-between sm:items-end border-b border-stone-200 pb-6 mb-8 gap-4">
  <div>
    <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">
      Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
    </h1>
    <p class="text-sm text-slate-600 mt-1">
      Pantau antrean pesanan pelanggan, ketersediaan porsi menu, dan rekapitulasi pendapatan dapur.
    </p>
  </div>

  <div class="inline-flex items-center bg-white px-4 py-2 rounded-lg border border-stone-200 text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0">
    <?= htmlspecialchars($tanggal_hari_ini, ENT_QUOTES, 'UTF-8') ?>
  </div>
</header>

<!-- Baris 1: Keputusan Utama Operasional (Antrean Pesanan & Stok) -->
<section aria-label="Status Operasional Utama" class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
  
  <!-- Kartu Prioritas: Pesanan Pending -->
  <div class="md:col-span-6 bg-slate-900 text-white p-6 sm:p-7 rounded-2xl border border-slate-800 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between gap-2 mb-3">
        <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Prioritas Tindakan</span>
        <span class="px-2.5 py-0.5 rounded bg-slate-800 text-slate-300 text-xs font-semibold border border-slate-700">Status: Pending</span>
      </div>
      <div class="flex items-baseline gap-3">
        <h2 id="count-pending-ui" class="font-serif-editorial text-4xl sm:text-5xl font-bold text-white"><?= $count_pending ?></h2>
        <span class="text-sm font-medium text-slate-300">pesanan menunggu konfirmasi</span>
      </div>
      <p class="text-xs text-slate-300 mt-2 leading-relaxed">
        Segera periksa pesanan baru untuk menyetujui porsi dan menghubungi pembeli melalui WhatsApp.
      </p>
    </div>

    <div class="mt-6 pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3">
      <a href="index.php?page=orders" class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded-lg bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold transition-colors">
        Kelola Pesanan Masuk
      </a>
      <a href="cetak_laporan.php" target="_blank" class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition-colors">
        Cetak Laporan Selesai
      </a>
    </div>
  </div>

  <!-- Kartu Ketersediaan Menu & Menu Terlaris -->
  <div class="md:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl border border-stone-200 flex flex-col justify-between">
      <div>
        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Katalog &amp; Stok</span>
        <p class="font-serif-editorial text-3xl font-bold text-slate-900 mt-2"><?= $count_produk ?> <span class="text-base font-sans font-semibold text-slate-600">Menu</span></p>
        <p class="text-xs mt-2 font-semibold <?= $stok_menipis > 0 ? 'text-orange-700' : 'text-emerald-700' ?>">
          <?= $stok_menipis > 0 ? $stok_menipis . ' menu memiliki sisa stok di bawah 5 porsi' : 'Seluruh stok menu dalam jumlah aman' ?>
        </p>
      </div>
      <div class="mt-5 pt-4 border-t border-stone-100">
        <a href="index.php?page=menu" class="text-xs font-bold text-orange-700 hover:underline">
          Atur Menu &amp; Tambah Stok
        </a>
      </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-stone-200 flex flex-col justify-between">
      <div>
        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Hidangan Terlaris</span>
        <?php if ($top_item): ?>
          <p class="font-serif-editorial text-xl font-bold text-slate-900 mt-2 line-clamp-2">
            <?= htmlspecialchars($top_item['nama'], ENT_QUOTES, 'UTF-8') ?>
          </p>
          <p class="text-xs font-semibold text-emerald-700 mt-2">
            Terjual <?= (int)$top_item['total'] ?> porsi (status selesai)
          </p>
        <?php else: ?>
          <p class="text-sm font-bold text-slate-800 mt-3">Belum Ada Data Terlaris</p>
          <p class="text-xs text-slate-600 mt-1">Akan muncul otomatis setelah ada pesanan berstatus selesai.</p>
        <?php endif; ?>
      </div>
      <div class="mt-5 pt-4 border-t border-stone-100 flex items-center justify-between text-xs text-slate-600">
        <span>Ulasan tampil: <strong><?= $testi_aktif ?>/3</strong></span>
        <a href="index.php?page=testimoni" class="font-bold text-orange-700 hover:underline">Kelola</a>
      </div>
    </div>
  </div>

</section>

<!-- Baris 2: Ringkasan Pendapatan (Hanya dari pesanan berstatus 'selesai') -->
<section aria-label="Rekapitulasi Pendapatan" class="mb-10">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Rekapitulasi Pendapatan (Pesanan Selesai)</h2>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <div class="bg-white p-5 sm:p-6 rounded-xl border border-stone-200">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Pendapatan Hari Ini</p>
      <p class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">
        Rp <?= number_format($total_harian, 0, ',', '.') ?>
      </p>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-xl border border-stone-200">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Minggu Ini</p>
      <p class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">
        Rp <?= number_format($total_mingguan, 0, ',', '.') ?>
      </p>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-xl border border-stone-200">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Bulan Ini</p>
      <p class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">
        Rp <?= number_format($total_bulanan, 0, ',', '.') ?>
      </p>
    </div>

    <div class="bg-[#F3EDE4] p-5 sm:p-6 rounded-xl border border-stone-300">
      <p class="text-xs font-bold uppercase tracking-wider text-orange-800">Total Akumulasi Omzet</p>
      <p class="text-xl sm:text-2xl font-extrabold text-orange-700 mt-2">
        Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
      </p>
    </div>
  </div>
</section>

<!-- Baris 3: Ulasan Pelanggan Terbaru -->
<section aria-label="Ulasan Pelanggan Terbaru" class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-6 border-b border-stone-200">
    <div>
      <h2 class="text-lg font-bold text-slate-900">Ulasan Pelanggan Terbaru</h2>
      <p class="text-xs text-slate-600">Menampilkan maksimal 5 testimoni terakhir dari total <?= $count_testi ?> ulasan.</p>
    </div>
    <a href="index.php?page=testimoni" class="min-h-[40px] inline-flex items-center px-4 py-2 rounded-lg border border-stone-300 text-xs font-bold text-slate-800 hover:bg-stone-100 transition-colors w-fit">
      Kelola Semua Ulasan
    </a>
  </div>

  <?php if ($all_testi && mysqli_num_rows($all_testi) > 0): ?>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php while ($t = mysqli_fetch_assoc($all_testi)): ?>
    <article class="p-4 bg-[#FAF7F2] rounded-xl border border-stone-200 flex flex-col justify-between">
      <p class="text-sm text-slate-800 leading-relaxed mb-3">
        "<?= htmlspecialchars($t['isi'], ENT_QUOTES, 'UTF-8') ?>"
      </p>
      <div class="flex items-center justify-between text-xs pt-2 border-t border-stone-200/80">
        <span class="font-bold text-slate-900">
          <?= htmlspecialchars($t['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?>
          <span class="font-normal text-slate-600">(<?= htmlspecialchars($t['pekerjaan'], ENT_QUOTES, 'UTF-8') ?>)</span>
        </span>
        <span class="font-bold text-orange-700">
          Rating: <?= (int)$t['bintang'] ?>/5 <?= ((int)$t['tampil'] === 1) ? '&bull; Tampil' : '' ?>
        </span>
      </div>
    </article>
    <?php endwhile; ?>
  </div>
  <?php else: ?>
  <div class="text-center py-8 bg-[#FAF7F2] rounded-xl border border-stone-200 p-6">
    <p class="text-sm font-bold text-slate-800 mb-1">Belum Ada Data Ulasan</p>
    <p class="text-xs text-slate-600 mb-4">Tambahkan ulasan pelanggan untuk ditampilkan pada halaman utama RasaNusa.</p>
    <a href="index.php?page=testimoni" class="min-h-[40px] inline-flex items-center px-4 py-2 rounded-lg bg-orange-700 text-white text-xs font-bold">
      Tambah Ulasan Pertama
    </a>
  </div>
  <?php endif; ?>
</section>
