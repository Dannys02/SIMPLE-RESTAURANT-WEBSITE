<?php
session_start();
$_SESSION['load_time'] = time();
include 'config/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_escaped = mysqli_real_escape_string($koneksi, $search);

$sql = "SELECT * FROM produk WHERE nama LIKE '%$search_escaped%' OR deskripsi LIKE '%$search_escaped%' ORDER BY nama ASC";
$result = mysqli_query($koneksi, $sql);
$total_found = $result ? mysqli_num_rows($result) : 0;

$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = $q_wa ? mysqli_fetch_assoc($q_wa) : null;
$nomor_tujuan = !empty($row_wa['nomor']) ? $row_wa['nomor'] : '6285645837298';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Daftar katalog lengkap hidangan rumahan nusantara RasaNusa beserta harga dan ketersediaan stok harian.">
  <title>Katalog Menu Lengkap | RasaNusa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/css/style.css" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) & Stone (#F3EDE4) untuk menjaga kontinuitas visual dari beranda RasaNusa;
     *   Deep Slate (#0F172A) untuk teks utama (>15:1) dan Terracotta Orange (#C2410C) sebagai satu aksen CTA & harga (4.85:1).
     * - Typography: 'Fraunces' untuk judul halaman katalog dan 'Plus Jakarta Sans' untuk pencarian, filter, serta kartu menu.
     * - Layout: Header pencarian terpusat diikuti bar status hasil pencarian dan grid katalog responsif 1-2-3 kolom.
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

  <a href="#katalog-utama" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:bg-orange-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:font-bold">
    Lewati ke daftar menu
  </a>

  <!-- Header & Navigasi -->
  <header class="sticky top-0 z-50 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-stone-200/80">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center" aria-label="Navigasi Katalog">
      <a href="<?= base_url() ?>" class="flex items-baseline gap-0.5 text-2xl font-extrabold tracking-tight text-slate-900">
        <span class="font-serif-editorial text-orange-700">Rasa</span><span>Nusa</span>
      </a>

      <div class="flex items-center gap-3 sm:gap-6">
        <a href="<?= base_url('#menu') ?>" class="min-h-[44px] inline-flex items-center px-3 sm:px-4 py-2 rounded-lg text-sm font-bold text-slate-700 hover:text-orange-700 hover:bg-stone-200/50 transition-colors">
          Kembali ke Beranda
        </a>
        <a href="https://wa.me/<?= htmlspecialchars($nomor_tujuan, ENT_QUOTES, 'UTF-8') ?>?text=<?= urlencode('Halo Admin RasaNusa, saya ingin bertanya mengenai menu di katalog.') ?>" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex min-h-[44px] items-center px-4 py-2 rounded-lg bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold transition-colors">
          Tanya Admin
        </a>
      </div>
    </nav>
  </header>

  <main id="katalog-utama" class="flex-grow">
    <!-- Header Katalog & Pencarian -->
    <section class="bg-[#F3EDE4] py-12 md:py-16 border-b border-stone-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
          <span class="section-index mb-3">01 / Katalog Lengkap</span>
          <h1 class="font-serif-editorial text-slate-900 font-bold mb-3" style="font-size: clamp(2rem, 4vw, 3rem); line-height: 1.15;">
            Daftar Hidangan RasaNusa
          </h1>
          <p class="text-slate-700 text-base leading-relaxed mb-8">
            Cari hidangan berdasarkan nama atau bahan masakan. Informasi stok porsi dan harga diperbarui langsung dari dapur kami.
          </p>
        </div>

        <form action="<?= base_url('katalog.php') ?>" method="GET" class="max-w-2xl" role="search">
          <label for="search-input" class="block text-sm font-bold text-slate-800 mb-2">
            Cari Menu atau Deskripsi Hidangan
          </label>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="search"
                id="search-input"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Ketik nama masakan, misalnya: ayam, sambal, paket..."
                class="w-full min-h-[48px] pl-11 pr-4 py-3 rounded-lg bg-white border border-stone-300 text-slate-900 placeholder:text-slate-500 transition"
              >
            </div>
            <div class="flex items-center gap-2">
              <button type="submit" class="min-h-[48px] flex-1 sm:flex-initial px-6 py-3 bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm rounded-lg transition">
                Cari Menu
              </button>
              <?php if ($search !== ''): ?>
              <a href="<?= base_url('katalog.php') ?>" class="min-h-[48px] inline-flex items-center justify-center px-4 py-3 bg-white hover:bg-stone-100 text-slate-800 font-bold text-sm rounded-lg border border-stone-300 transition">
                Reset
              </a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </section>

    <!-- Daftar Hasil Menu -->
    <section class="py-12 md:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-6 mb-8 border-b border-stone-200">
          <p class="text-sm font-semibold text-slate-700">
            <?php if ($search !== ''): ?>
              Menampilkan <strong><?= $total_found ?> menu</strong> untuk kata kunci <span class="text-orange-700 font-bold">"<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"</span>
            <?php else: ?>
              Menampilkan total <strong><?= $total_found ?> menu</strong> yang terdaftar di katalog
            <?php endif; ?>
          </p>
        </div>

        <?php if ($result && $total_found > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
          <?php
          $idx = 0;
          while ($row = mysqli_fetch_assoc($result)):
            $isOut = ((int)$row['stok'] <= 0);
            $imgSrc = (!empty($row['gambar']))
              ? 'src/img/' . rawurlencode($row['gambar'])
              : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=600';
          ?>
          <article class="bg-white rounded-xl overflow-hidden border border-stone-200/90 hover:border-orange-700/50 transition-all duration-300 flex flex-col">
            <div class="relative overflow-hidden bg-stone-100 aspect-[4/3]">
              <img
                src="<?= $imgSrc ?>"
                alt="Menu <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>"
                width="600"
                height="450"
                <?= $idx === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
              >
              <?php if ($isOut): ?>
              <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center p-4">
                <span class="bg-red-700 text-white px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider">
                  Stok Habis
                </span>
              </div>
              <?php endif; ?>
            </div>

            <div class="p-5 md:p-6 flex flex-col flex-grow">
              <div class="flex items-start justify-between gap-3 mb-2">
                <h2 class="text-lg font-bold text-slate-900 leading-snug">
                  <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded border <?= $isOut ? 'bg-red-50 text-red-800 border-red-200' : 'bg-stone-100 text-slate-700 border-stone-200' ?>">
                  Stok: <?= (int)$row['stok'] ?>
                </span>
              </div>

              <?php if (!empty($row['deskripsi'])): ?>
              <p class="text-sm text-slate-600 line-clamp-2 mb-4 leading-relaxed">
                <?= htmlspecialchars($row['deskripsi'], ENT_QUOTES, 'UTF-8') ?>
              </p>
              <?php endif; ?>

              <div class="mt-auto pt-4 border-t border-stone-100 flex items-center justify-between gap-4">
                <div>
                  <span class="block text-xs text-slate-600 font-medium">Harga per porsi</span>
                  <span class="text-lg md:text-xl font-extrabold text-orange-700">
                    Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?>
                  </span>
                </div>

                <div class="flex items-center gap-2">
                  <a href="<?= base_url('detail.php?id=' . (int)$row['id']) ?>" class="min-h-[44px] inline-flex items-center justify-center px-3.5 py-2 rounded-lg border border-stone-300 text-slate-800 hover:bg-stone-100 font-bold text-sm transition-colors">
                    Detail
                  </a>
                  <button
                    type="button"
                    onclick='openModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'
                    class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 rounded-lg bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm transition-all disabled:opacity-50 disabled:bg-slate-400"
                    <?= $isOut ? 'disabled aria-disabled="true"' : '' ?>
                  >
                    <?= $isOut ? 'Habis' : 'Pesan' ?>
                  </button>
                </div>
              </div>
            </div>
          </article>
          <?php $idx++; endwhile; ?>
        </div>

        <?php else: ?>
        <!-- Empty State saat pencarian tidak cocok atau database kosong (R-27) -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 md:p-12 text-center max-w-xl mx-auto my-4">
          <h2 class="text-xl font-bold text-slate-900 mb-2">
            <?= $search !== '' ? 'Menu Tidak Ditemukan' : 'Katalog Menu Masih Kosong' ?>
          </h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-6">
            <?php if ($search !== ''): ?>
              Tidak ada hidangan yang cocok dengan kata kunci <strong>"<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"</strong>. Silakan gunakan kata kunci lain atau tampilkan seluruh daftar menu.
            <?php else: ?>
              Belum ada data menu yang ditambahkan ke dalam katalog. Silakan hubungi admin via WhatsApp untuk menanyakan menu harian yang tersedia.
            <?php endif; ?>
          </p>
          <div class="flex flex-wrap justify-center gap-3">
            <?php if ($search !== ''): ?>
            <a href="<?= base_url('katalog.php') ?>" class="min-h-[44px] inline-flex items-center px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white rounded-lg font-bold text-sm transition">
              Tampilkan Semua Menu
            </a>
            <?php endif; ?>
            <a href="<?= base_url() ?>" class="min-h-[44px] inline-flex items-center px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-slate-800 rounded-lg font-bold text-sm transition">
              Kembali ke Beranda
            </a>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </section>
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

      <form id="orderForm" action="<?= base_url('api/create_order.php') ?>" method="POST" class="p-6 md:p-8" novalidate>
        <input type="hidden" name="produk_id" id="produk_id">
        <input type="text" name="perangkap" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

        <div class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="nama_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Nama Pembeli</label>
              <input type="text" id="nama_pembeli" name="nama_pembeli" maxlength="50" placeholder="Nama lengkap penerima" aria-describedby="err_nama_pembeli" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required>
              <span id="err_nama_pembeli" role="alert" class="hidden block mt-1.5 text-xs font-semibold text-red-700"></span>
            </div>
            <div>
              <label for="whatsapp_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Nomor WhatsApp (10-14 Digit)</label>
              <input type="tel" id="whatsapp_pembeli" name="whatsapp" placeholder="628123456789" pattern="[0-9]{10,14}" aria-describedby="err_whatsapp_pembeli" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required>
              <span id="err_whatsapp_pembeli" role="alert" class="hidden block mt-1.5 text-xs font-semibold text-red-700"></span>
            </div>
          </div>

          <div>
            <label for="stok_input" class="block text-sm font-bold mb-1.5 text-slate-800">Jumlah Porsi</label>
            <input type="number" name="stok" id="stok_input" value="1" min="1" max="100" oninput="hitungTotal()" aria-describedby="err_stok_input" class="w-full sm:w-40 bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 focus:bg-white transition" required>
            <span id="err_stok_input" role="alert" class="hidden block mt-1.5 text-xs font-semibold text-red-700"></span>
          </div>

          <div>
            <label for="alamat_pembeli" class="block text-sm font-bold mb-1.5 text-slate-800">Alamat Pengiriman Lengkap (Min. 10 Karakter)</label>
            <textarea id="alamat_pembeli" name="alamat" maxlength="255" placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan lokasi..." rows="3" aria-describedby="err_alamat_pembeli" class="w-full bg-[#FAF7F2] border border-stone-300 p-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required></textarea>
            <span id="err_alamat_pembeli" role="alert" class="hidden block mt-1.5 text-xs font-semibold text-red-700"></span>
          </div>

          <div>
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
            <span id="err_recaptcha" role="alert" class="hidden block mt-1.5 text-xs font-semibold text-red-700"></span>
          </div>

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
        <a href="<?= base_url('#kontak') ?>" class="hover:text-orange-400 transition-colors">Hubungi Dapur</a>
      </div>
    </div>
  </footer>

  <script src="<?= base_url('src/js/index.js') ?>"></script>
</body>
</html>
