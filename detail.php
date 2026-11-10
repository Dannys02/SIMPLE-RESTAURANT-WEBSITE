<?php
session_start();
$_SESSION['load_time'] = time();
include 'config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
  abort_error(
    404,
    'Menu Tidak Ditemukan',
    'Parameter ID menu tidak valid atau tidak disertakan pada alamat URL.',
    base_url('katalog.php'),
    'Lihat Katalog Menu'
  );
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$p = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$p) {
  abort_error(
    404,
    'Menu Tidak Ditemukan',
    'Menu yang Anda cari tidak tersedia atau sudah dihapus dari daftar katalog kami.',
    base_url('katalog.php'),
    'Lihat Katalog Menu'
  );
}

$isOut = ((int)$p['stok'] <= 0);
$imgSrc = (!empty($p['gambar']))
  ? base_url('src/img/' . rawurlencode($p['gambar']))
  : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=800';

// Nomor Admin Wa
$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = $q_wa ? mysqli_fetch_assoc($q_wa) : null;
$nomor_tujuan = !empty($row_wa['nomor']) ? $row_wa['nomor'] : '6285645837298';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Detail hidangan <?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?> dari dapur RasaNusa.">
  <title><?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?> | Detail Menu RasaNusa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('src/css/style.css') ?>" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) & Stone (#F3EDE4) menjaga kesatuan visual dengan beranda dan katalog;
     *   Deep Slate (#0F172A) untuk teks utama (>15:1) dan Terracotta Orange (#C2410C) untuk harga & CTA utama (4.85:1).
     * - Typography: 'Fraunces' untuk nama hidangan utama dan 'Plus Jakarta Sans' untuk rincian stok, deskripsi, dan form modal.
     * - Layout: Split 2 kolom editorial (foto hidangan 1:1 di kiri, rincian harga, stok, deskripsi, dan tombol aksi di kanan).
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
      width: 1.5rem;
      height: 2px;
      background-color: var(--accent-orange);
    }
  </style>
</head>
<body class="antialiased min-h-dvh flex flex-col selection:bg-orange-700 selection:text-white">

  <a href="#detail-menu" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:bg-orange-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:font-bold">
    Lewati ke detail menu
  </a>

  <!-- Header & Navigasi -->
  <header class="sticky top-0 z-50 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-stone-200/80">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center" aria-label="Navigasi Utama">
      <a href="<?= base_url() ?>" class="flex items-baseline gap-0.5 text-2xl font-extrabold tracking-tight text-slate-900">
        <span class="font-serif-editorial text-orange-700">Rasa</span><span>Nusa</span>
      </a>

      <div class="flex items-center gap-3 sm:gap-6">
        <a href="<?= base_url('katalog.php') ?>" class="min-h-[44px] inline-flex items-center px-3 sm:px-4 py-2 rounded-lg text-sm font-bold text-slate-700 hover:text-orange-700 hover:bg-stone-200/50 transition-colors">
          Semua Menu Katalog
        </a>
      </div>
    </nav>
  </header>

  <main id="detail-menu" class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-14">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="mb-8 flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-600">
      <a href="<?= base_url() ?>" class="hover:text-orange-700 transition-colors">Beranda</a>
      <span aria-hidden="true">/</span>
      <a href="<?= base_url('katalog.php') ?>" class="hover:text-orange-700 transition-colors">Katalog Menu</a>
      <span aria-hidden="true">/</span>
      <span class="text-slate-900 font-bold" aria-current="page"><?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?></span>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
      
      <!-- Kolom Kiri: Foto Hidangan -->
      <div class="lg:col-span-6 p-4 sm:p-6 md:p-8 bg-[#F3EDE4]/60 flex items-center justify-center border-b lg:border-b-0 lg:border-r border-stone-200">
        <div class="relative overflow-hidden rounded-xl border border-stone-300 bg-stone-200 aspect-square w-full max-w-lg">
          <img
            src="<?= $imgSrc ?>"
            alt="Foto hidangan <?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?>"
            width="800"
            height="800"
            fetchpriority="high"
            class="w-full h-full object-cover"
          >
          <?php if ($isOut): ?>
          <div class="absolute top-4 left-4 bg-red-700 text-white px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider">
            Stok Habis
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Kolom Kanan: Rincian Menu & Aksi Pemesanan -->
      <div class="lg:col-span-6 p-6 sm:p-8 md:p-12 flex flex-col justify-between">
        <div>
          <span class="section-index mb-3">Rincian Hidangan</span>
          <h1 class="font-serif-editorial text-slate-900 font-bold mb-4" style="font-size: clamp(1.85rem, 3.5vw, 2.75rem); line-height: 1.15;">
            <?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?>
          </h1>

          <div class="flex flex-wrap items-center gap-4 pb-6 mb-6 border-b border-stone-200">
            <div>
              <span class="block text-xs font-semibold text-slate-600">Harga per porsi</span>
              <span class="text-2xl md:text-3xl font-extrabold text-orange-700">
                Rp <?= number_format((int)$p['harga'], 0, ',', '.') ?>
              </span>
            </div>
            <div class="h-8 w-px bg-stone-300 hidden sm:block" aria-hidden="true"></div>
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $isOut ? 'bg-red-50 text-red-800 border-red-200' : 'bg-[#FAF7F2] text-slate-800 border-stone-300' ?>">
              <?= $isOut ? 'Stok Sedang Habis' : 'Tersedia: ' . (int)$p['stok'] . ' Porsi' ?>
            </span>
          </div>

          <div class="mb-8">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Deskripsi Hidangan</h2>
            <p class="text-slate-700 leading-relaxed text-base">
              <?= !empty($p['deskripsi']) ? nl2br(htmlspecialchars($p['deskripsi'], ENT_QUOTES, 'UTF-8')) : 'Hidangan rumahan nusantara yang dimasak segar dengan bumbu pilihan.' ?>
            </p>
          </div>
        </div>

        <div class="pt-6 border-t border-stone-200 flex flex-col sm:flex-row gap-3">
          <button
            type="button"
            onclick='openModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'
            class="flex-1 min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white py-3.5 px-6 rounded-lg font-bold text-base transition-all inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:bg-slate-400"
            <?= $isOut ? 'disabled aria-disabled="true"' : '' ?>
          >
            <?= $isOut ? 'Stok Habis' : 'Pesan Menu Ini Sekarang' ?>
          </button>
          <a
            href="https://wa.me/<?= htmlspecialchars($nomor_tujuan, ENT_QUOTES, 'UTF-8') ?>?text=<?= urlencode('Halo Admin RasaNusa, saya ingin bertanya mengenai menu ' . $p['nama']) ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="min-h-[48px] px-6 py-3.5 border border-stone-300 bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-900 rounded-lg font-bold text-sm transition-colors inline-flex items-center justify-center"
          >
            Tanya via WhatsApp
          </a>
        </div>
      </div>

    </div>
  </main>

  <!-- Modal Pemesanan Produk -->
  <div id="orderModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="relative bg-white w-full max-w-lg rounded-2xl border border-stone-200 overflow-hidden my-8 z-10">
      <div class="bg-slate-900 p-6 text-white flex items-center justify-between">
        <div>
          <h2 id="modalTitle" class="text-xl font-bold">Formulir Pemesanan</h2>
          <p class="text-orange-300 text-sm font-medium mt-0.5" id="modalSubTitle">Menu pilihan Anda</p>
        </div>
        <button type="button" onclick="closeModal()" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-sm font-bold" aria-label="Tutup formulir pemesanan">
          ✕
        </button>
      </div>

      <form action="<?= base_url('api/create_order.php') ?>" method="POST" class="p-6 md:p-8">
        <input type="hidden" name="produk_id" id="produk_id">
        <input type="text" name="perangkap" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

        <div class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="nama_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Nama Pembeli (Min. 3 Huruf)</label>
              <input type="text" id="nama_pembeli" name="nama_pembeli" maxlength="50" placeholder="Nama lengkap penerima" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required>
            </div>
            <div>
              <label for="whatsapp_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Nomor WhatsApp (10-14 Digit)</label>
              <input type="tel" id="whatsapp_pembeli" name="whatsapp" placeholder="628123456789" pattern="[0-9]{10,14}" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required>
            </div>
          </div>

          <div>
            <label for="stok_input" class="block text-sm font-bold mb-1.5 text-slate-800">Jumlah Porsi</label>
            <input type="number" name="stok" id="stok_input" value="1" min="1" max="100" oninput="hitungTotal()" class="w-full sm:w-40 bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 focus:bg-white transition" required>
          </div>

          <div>
            <label for="alamat_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Alamat Pengiriman Lengkap (Min. 10 Karakter)</label>
            <textarea id="alamat_pembeli" name="alamat" maxlength="255" placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan lokasi..." rows="3" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required></textarea>
          </div>

          <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>"></div>

          <input type="hidden" name="harga" id="harga_modal">

          <div class="bg-[#F3EDE4] p-4 rounded-xl flex justify-between items-center border border-stone-300">
            <span class="font-bold text-slate-900 text-sm">Estimasi Total Pembayaran:</span>
            <span id="total_harga" class="text-xl font-extrabold text-orange-700">Rp 0</span>
          </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6 pt-4 border-t border-stone-200">
          <button type="button" onclick="closeModal()" class="min-h-[44px] px-5 py-2.5 rounded-lg border border-stone-300 font-bold text-slate-700 hover:bg-stone-100 text-sm transition">
            Batal
          </button>
          <button type="submit" class="min-h-[44px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white px-6 py-2.5 rounded-lg font-bold text-sm transition">
            Konfirmasi & Kirim Pesanan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
      <p>&copy; 2026 RasaNusa. Hak Cipta Dilindungi.</p>
      <div class="flex gap-6 font-semibold">
        <a href="<?= base_url() ?>" class="hover:text-orange-400 transition-colors">Beranda</a>
        <a href="<?= base_url('katalog.php') ?>" class="hover:text-orange-400 transition-colors">Katalog Lengkap</a>
      </div>
    </div>
  </footer>

  <script src="<?= base_url('src/js/index.js') ?>"></script>
</body>
</html>
