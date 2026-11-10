<?php
session_start();
$_SESSION['load_time'] = time();
include 'config/db.php';

// Nomor Admin WhatsApp dari database
$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = $q_wa ? mysqli_fetch_assoc($q_wa) : null;
$nomor_tujuan = !empty($row_wa['nomor']) ? $row_wa['nomor'] : '6285645837298';

// Hitung statistik menu nyata dari database
$cek_jumlah = mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(IF(stok > 0, 1, 0)) as tersedia FROM produk");
$stat_produk = $cek_jumlah ? mysqli_fetch_assoc($cek_jumlah) : ['total' => 0, 'tersedia' => 0];
$total_data = (int)($stat_produk['total'] ?? 0);
$menu_tersedia = (int)($stat_produk['tersedia'] ?? 0);

// Query 6 menu terbaru untuk beranda
$query_limit = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC LIMIT 6");

// Query testimoni yang dipilih admin (tampil = 1)
$query_testi = mysqli_query($koneksi, "SELECT * FROM testimoni WHERE tampil = 1 ORDER BY id DESC LIMIT 3");
$has_testi = ($query_testi && mysqli_num_rows($query_testi) > 0);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="RasaNusa - Katalog hidangan rumahan nusantara yang dimasak segar dengan bahan pilihan. Pesan langsung secara online.">
  <title>RasaNusa | Hidangan Nusantara dari Dapur Rumahan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/css/style.css" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) & Stone (#F3EDE4) sebagai latar agar foto makanan tampak hangat dan menggugah selera;
     *   Deep Slate (#0F172A) untuk teks utama (kontras >15:1); Terracotta Orange (#C2410C) sebagai satu aksen terfokus (kontras 5.18:1).
     * - Typography: 'Fraunces' (serif editorial) pada judul utama memberi karakter rumah makan autentik; 'Plus Jakarta Sans' menjaga keterbacaan harga dan form.
     * - Layout & Rhythm (RHYTHM 3): Tiap seksi memiliki komposisi berbeda (Hero split 7:5, Tentang jurnal 5:7, Menu grid katalog, Testimoni editorial, Kontak split gelap-terang).
     */
    :root {
      --bg-warm: #FAF7F2;
      --bg-stone: #F3EDE4;
      --ink-primary: #0F172A;
      --ink-secondary: #475569;
      --accent-orange: #C2410C;
      --accent-orange-hover: #9A3412;
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
<body class="antialiased selection:bg-orange-700 selection:text-white">

  <!-- Skip link untuk aksesibilitas keyboard (R-32) -->
  <a href="#beranda" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:bg-orange-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:font-bold">
    Lewati ke konten utama
  </a>

  <!-- Header & Navigasi Utama -->
  <header class="sticky top-0 z-50 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-stone-200/80">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center" aria-label="Navigasi Utama">
      <a href="#beranda" class="flex items-baseline gap-0.5 text-2xl font-extrabold tracking-tight text-slate-900">
        <span class="font-serif-editorial text-orange-700">Rasa</span><span>Nusa</span>
      </a>

      <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-700">
        <a href="#beranda" class="py-2 hover:text-orange-700 transition-colors">Beranda</a>
        <a href="#tentang" class="py-2 hover:text-orange-700 transition-colors">Cerita Dapur</a>
        <a href="#menu" class="py-2 hover:text-orange-700 transition-colors">Menu Pilihan</a>
        <a href="<?= base_url('katalog.php') ?>" class="py-2 hover:text-orange-700 transition-colors">Katalog Lengkap</a>
        <?php if ($has_testi): ?>
        <a href="#testimoni" class="py-2 hover:text-orange-700 transition-colors">Ulasan</a>
        <?php endif; ?>
        <a href="#kontak" class="py-2 hover:text-orange-700 transition-colors">Kontak</a>
      </div>

      <div class="hidden md:flex items-center gap-3">
        <button type="button" onclick="handleContactClick()" class="min-h-[44px] px-5 py-2.5 bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white rounded-lg text-sm font-bold transition-all">
          Pesan via WhatsApp
        </button>
      </div>

      <button type="button" onclick="toggleMobileMenu()" aria-label="Buka menu navigasi" aria-expanded="false" id="mobileMenuBtn" class="md:hidden min-h-[44px] min-w-[44px] inline-flex items-center justify-center gap-2 px-3 rounded-lg border border-stone-300 text-slate-800 hover:bg-stone-200/60 font-bold text-sm">
        <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span>Menu</span>
      </button>
    </nav>
  </header>

  <main>
    <!-- Split Asimetris 7:5 -->
    <section id="beranda" class="py-12 md:py-20 lg:py-24 border-b border-stone-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
          
          <div class="lg:col-span-7 from-bottom">
            <h1 class="font-serif-editorial text-slate-900 font-bold tracking-tight mb-6" style="font-size: clamp(2.25rem, 4.5vw, 3.75rem); line-height: 1.12;">
              Masakan Hangat dari Resep Keluarga, Tersaji di Meja Anda.
            </h1>
            <p class="text-slate-700 text-base md:text-lg leading-relaxed mb-8 max-w-2xl">
              RasaNusa menghadirkan hidangan harian dan lauk rumahan yang dimasak segar menggunakan rempah lokal pilihan. Pilih menu favorit Anda, tentukan jumlah porsi, dan pesan langsung tanpa antre.
            </p>

            <div class="flex flex-wrap items-center gap-4">
              <a href="#menu" class="min-h-[48px] inline-flex items-center justify-center px-7 py-3.5 bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold rounded-lg transition-all">
                Pilih Menu Sekarang
              </a>
              <a href="<?= base_url('katalog.php') ?>" class="min-h-[48px] inline-flex items-center justify-center px-6 py-3.5 bg-white hover:bg-stone-100 active:translate-y-px text-slate-900 font-bold rounded-lg border border-stone-300 transition-all">
                Cari di Katalog Lengkap
              </a>
            </div>

            <!-- Info Operasional Singkat (Fakta Nyata) -->
            <dl class="mt-10 pt-8 border-t border-stone-200 grid grid-cols-2 sm:grid-cols-3 gap-6">
              <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Jam Buka Dapur</dt>
                <dd class="mt-1 text-sm md:text-base font-bold text-slate-900">Senin - Sabtu (09.00 - 20.00)</dd>
              </div>
              <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Menu Terdaftar</dt>
                <dd class="mt-1 text-sm md:text-base font-bold text-slate-900">
                  <?= $total_data > 0 ? $total_data . ' Pilihan Hidangan' : 'Segera Hadir' ?>
                </dd>
              </div>
              <div class="col-span-2 sm:col-span-1">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Lokasi Dapur</dt>
                <dd class="mt-1 text-sm md:text-base font-bold text-slate-900">Jawa Timur, Indonesia</dd>
              </div>
            </dl>
          </div>

          <div class="lg:col-span-5 from-bottom">
            <figure class="relative">
              <div class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-200 aspect-[4/3] lg:aspect-[5/6]">
                <img
                  src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&q=80&w=800"
                  alt="Sepiring hidangan sehat nusantara dengan sayuran segar dan lauk rempah"
                  width="800"
                  height="960"
                  fetchpriority="high"
                  class="w-full h-full object-cover"
                >
              </div>
              <figcaption class="mt-3 flex items-center justify-between text-xs text-slate-600 px-1">
                <span>Dimasak segar sesuai pesanan harian</span>
                <span class="font-semibold text-orange-700">100% Bahan Halal</span>
              </figcaption>
            </figure>
          </div>

        </div>
      </div>
    </section>

    <!-- Komposisi Jurnal Editorial 5:7 -->
    <section id="tentang" class="py-16 md:py-24 bg-[#F3EDE4] border-b border-stone-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
          
          <div class="lg:col-span-5 from-bottom">
            <div class="overflow-hidden rounded-2xl border border-stone-300 bg-stone-200 aspect-[4/3]">
              <img
                src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&q=80&w=800"
                alt="Proses pelayanan dan penyiapan pesanan pelanggan di dapur RasaNusa"
                width="800"
                height="600"
                loading="lazy"
                class="w-full h-full object-cover"
              >
            </div>
          </div>

          <div class="lg:col-span-7 from-bottom">
            <h2 class="font-serif-editorial text-slate-900 font-bold mb-5" style="font-size: clamp(1.75rem, 3.2vw, 2.5rem); line-height: 1.2;">
              Berawal dari Dapur Rumah, Menghadirkan Cita Rasa yang Jujur.
            </h2>
            <p class="text-slate-700 leading-relaxed mb-6 text-base">
              <strong class="font-bold text-slate-900">RasaNusa</strong> lahir dari keinginan sederhana untuk menyajikan masakan rumahan nusantara yang bersih, lezat, dan terjangkau. Kami memprioritaskan bahan baku segar dari pasar lokal agar setiap porsi yang sampai ke tangan Anda tetap terjaga kualitas dan rasanya.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
              <div class="bg-[#FAF7F2] p-5 rounded-xl border border-stone-300/80">
                <h3 class="text-base font-bold text-slate-900 mb-1">Bahan Segar & Halal</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                  Seluruh bumbu, daging, dan sayuran dipilih setiap pagi dan diolah secara higienis dengan bahan yang terjamin halal.
                </p>
              </div>
              <div class="bg-[#FAF7F2] p-5 rounded-xl border border-stone-300/80">
                <h3 class="text-base font-bold text-slate-900 mb-1">Stok & Harga Transparan</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                  <?= $menu_tersedia > 0
                    ? "Saat ini tersedia <strong>{$menu_tersedia} menu siap pesan</strong> dengan informasi sisa porsi dan estimasi harga otomatis."
                    : "Informasi ketersediaan porsi dan total harga dihitung secara otomatis sebelum Anda mengirim pesanan." ?>
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- MENU UNGGULAN: Katalog Terstruktur dengan Empty State (R-27) -->
    <section id="menu" class="py-16 md:py-24 bg-[#FAF7F2] border-b border-stone-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="from-bottom flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4 pb-6 border-b border-stone-200">
          <div>
            <h2 class="font-serif-editorial text-slate-900 font-bold" style="font-size: clamp(1.75rem, 3.2vw, 2.5rem); line-height: 1.2;">
              Menu Pilihan Hari Ini
            </h2>
          </div>
          <div class="flex items-center gap-4">
            <a href="<?= base_url('katalog.php') ?>" class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-900 font-bold text-sm transition-colors">
              Buka Katalog & Pencarian Menu
            </a>
          </div>
        </div>

        <?php if ($query_limit && mysqli_num_rows($query_limit) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
          <?php
          $i = 0;
          while ($row = mysqli_fetch_assoc($query_limit)):
            $isOut = ((int)$row['stok'] <= 0);
            $imgSrc = (!empty($row['gambar']))
              ? 'src/img/' . rawurlencode($row['gambar'])
              : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=600';
          ?>
          <article style="transition-delay: <?= min($i * 0.08, 0.3) ?>s" class="from-bottom bg-white rounded-xl overflow-hidden border border-stone-200/90 hover:border-orange-700/50 transition-all duration-300 flex flex-col">
            <div class="relative overflow-hidden bg-stone-100 aspect-[4/3]">
              <img
                src="<?= $imgSrc ?>"
                alt="Menu <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>"
                width="600"
                height="450"
                loading="lazy"
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
                <h3 class="text-lg font-bold text-slate-900 leading-snug">
                  <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
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
          <?php $i++; endwhile; ?>
        </div>

        <?php if ($total_data > 6): ?>
        <div class="from-bottom text-center mt-12">
          <a href="<?= base_url('katalog.php') ?>" class="min-h-[48px] inline-flex items-center justify-center px-8 py-3.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm transition-colors">
            Lihat Seluruh <?= $total_data ?> Menu di Katalog
          </a>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Empty State Jujur & Informatif ketika database produk masih kosong (R-27) -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 md:p-12 text-center max-w-xl mx-auto">
          <h3 class="text-xl font-bold text-slate-900 mb-2">Belum Ada Menu yang Ditampilkan</h3>
          <p class="text-slate-600 text-sm leading-relaxed mb-6">
            Saat ini daftar menu sedang diperbarui oleh dapur kami. Anda tetap dapat menanyakan menu harian yang tersedia langsung melalui WhatsApp.
          </p>
          <div class="flex flex-wrap justify-center gap-3">
            <button type="button" onclick="handleContactClick()" class="min-h-[44px] px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white rounded-lg font-bold text-sm transition">
              Tanya Menu Harian via WhatsApp
            </button>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </section>

    <!-- 04 / TESTIMONI PELANGGAN: Hanya tampil jika ada data nyata di database (R-18) -->
    <?php if ($has_testi): ?>
    <section id="testimoni" class="py-16 md:py-24 bg-[#F3EDE4] border-b border-stone-200/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="from-bottom max-w-2xl mb-12">
          <span class="section-index mb-3">04 / Ulasan Pelanggan</span>
          <h2 class="font-serif-editorial text-slate-900 font-bold mb-3" style="font-size: clamp(1.75rem, 3.2vw, 2.5rem); line-height: 1.2;">
            Cerita dari Meja Pelanggan
          </h2>
          <p class="text-slate-700 text-base">
            Ulasan langsung dari pelanggan yang telah mencicipi hidangan RasaNusa.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
          <?php
          $in = 0;
          while ($t = mysqli_fetch_assoc($query_testi)):
            $rating = max(1, min(5, (int)$t['bintang']));
          ?>
          <blockquote style="transition-delay: <?= min($in * 0.1, 0.3) ?>s" class="from-bottom bg-[#FAF7F2] p-6 md:p-8 rounded-2xl border border-stone-300/80 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-1 text-orange-700 mb-4" aria-label="Rating <?= $rating ?> dari 5 bintang">
                <?php for ($s = 1; $s <= $rating; $s++): ?>
                <svg class="w-4 h-4 fill-current" aria-hidden="true" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-.11.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <?php endfor; ?>
              </div>
              <p class="text-slate-800 text-base leading-relaxed mb-6">
                "<?= htmlspecialchars($t['isi'], ENT_QUOTES, 'UTF-8') ?>"
              </p>
            </div>

            <footer class="flex items-center gap-3 pt-4 border-t border-stone-200">
              <div class="w-10 h-10 rounded-full bg-orange-100 border border-orange-200 flex items-center justify-center font-bold text-orange-800 uppercase text-sm" aria-hidden="true">
                <?= htmlspecialchars(substr($t['nama_pelanggan'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div>
                <cite class="not-italic font-bold text-slate-900 text-sm block">
                  <?= htmlspecialchars($t['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?>
                </cite>
                <span class="text-xs text-slate-600">
                  <?= htmlspecialchars($t['pekerjaan'], ENT_QUOTES, 'UTF-8') ?>
                </span>
              </div>
            </footer>
          </blockquote>
          <?php $in++; endwhile; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- 05 / KONTAK & PESAN LANGSUNG: Split Panel Kontras -->
    <section id="kontak" class="py-16 md:py-24 bg-[#FAF7F2]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
          
          <!-- Panel Informasi Kontak (Latar Slate-900 dengan kontras putih >15:1) -->
          <div class="lg:col-span-5 bg-slate-900 text-white p-8 md:p-12 flex flex-col justify-between">
            <div>
              <span class="text-xs font-bold uppercase tracking-widest text-orange-400 block mb-3">05 / Hubungi Dapur Kami</span>
              <h2 class="font-serif-editorial text-2xl md:text-3xl font-bold mb-4 leading-snug">
                Ingin Pesan Porsi Besar atau Tanya Menu?
              </h2>
              <p class="text-slate-300 text-sm leading-relaxed mb-8">
                Kami melayani pemesanan harian maupun pesanan acara keluarga dan kantor. Kirimkan pertanyaan Anda melalui formulir di samping dan pesan akan langsung terhubung ke WhatsApp admin kami.
              </p>

              <dl class="space-y-6 text-sm">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 text-orange-400" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  </div>
                  <div>
                    <dt class="font-bold text-white">Alamat Dapur</dt>
                    <dd class="text-slate-300 mt-0.5">Jl. Nasional No. 3, Jawa Timur, Indonesia</dd>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 text-orange-400" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                    <dt class="font-bold text-white">Email Layanan</dt>
                    <dd class="text-slate-300 mt-0.5">
                      <a href="mailto:webToko@gmail.com" class="underline hover:text-orange-300">webToko@gmail.com</a>
                    </dd>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 text-orange-400" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <div>
                    <dt class="font-bold text-white">Jam Operasional</dt>
                    <dd class="text-slate-300 mt-0.5">Senin - Jumat: 09.00 - 20.00 WIB</dd>
                    <dd class="text-slate-300">Sabtu: 09.00 - 17.00 WIB (Minggu Tutup)</dd>
                  </div>
                </div>
              </dl>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-800">
              <button type="button" onclick="handleContactClick()" class="w-full min-h-[44px] py-3 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm border border-slate-700 transition">
                Chat Langsung Admin via WhatsApp
              </button>
            </div>
          </div>

          <!-- Formulir Kirim Pesan ke WhatsApp -->
          <div class="lg:col-span-7 p-8 md:p-12">
            <h3 class="text-xl font-bold text-slate-900 mb-2">Kirim Pesan Cepat</h3>
            <p class="text-slate-600 text-sm mb-6">
              Isi formulir di bawah ini, lalu tekan tombol kirim untuk meneruskan pesan ke WhatsApp Admin.
            </p>

            <form id="contactForm" onsubmit="event.preventDefault(); sendWA();" class="space-y-5" novalidate>
              <div id="contactFeedback" role="alert" class="hidden p-3.5 rounded-lg text-sm font-semibold border"></div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label for="nama" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Anda</label>
                  <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" class="w-full bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required>
                </div>
                <div>
                  <label for="email" class="block text-sm font-bold text-slate-800 mb-1.5">Alamat Email (Opsional)</label>
                  <input type="email" id="email" name="email" placeholder="nama@email.com" class="w-full bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition">
                </div>
              </div>

              <div>
                <label for="subjek" class="block text-sm font-bold text-slate-800 mb-1.5">Keperluan</label>
                <select id="subjek" name="subjek" class="w-full bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 focus:bg-white transition">
                  <option value="Tanya Menu">Tanya Ketersediaan Menu</option>
                  <option value="Pesanan Acara / Katering">Pesanan Acara / Porsi Besar</option>
                  <option value="Kritik & Saran">Masukan & Saran</option>
                  <option value="Kemitraan UMKM">Kerja Sama UMKM</option>
                </select>
              </div>

              <div>
                <label for="pesan" class="block text-sm font-bold text-slate-800 mb-1.5">Isi Pesan</label>
                <textarea id="pesan" name="pesan" rows="4" placeholder="Tuliskan pertanyaan atau detail pesanan Anda..." class="w-full bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition" required></textarea>
              </div>

              <button type="submit" class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white py-3.5 px-6 rounded-lg font-bold text-sm transition-all">
                Kirim Pesan ke WhatsApp
              </button>
            </form>
          </div>

        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-slate-900 text-slate-300 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8 pb-8 border-b border-slate-800">
        <div>
          <a href="#beranda" class="text-2xl font-extrabold tracking-tight text-white">
            <span class="font-serif-editorial text-orange-400">Rasa</span><span>Nusa</span>
          </a>
          <p class="text-sm text-slate-300 mt-2 max-w-md">
            Katalog hidangan rumahan nusantara. Dimasak segar dari bahan lokal pilihan di Jawa Timur.
          </p>
        </div>

        <div class="flex flex-wrap gap-6 text-sm font-semibold text-slate-200">
          <a href="#beranda" class="hover:text-orange-400 transition-colors">Beranda</a>
          <a href="#tentang" class="hover:text-orange-400 transition-colors">Cerita Dapur</a>
          <a href="#menu" class="hover:text-orange-400 transition-colors">Menu Pilihan</a>
          <a href="<?= base_url('katalog.php') ?>" class="hover:text-orange-400 transition-colors">Katalog Lengkap</a>
          <a href="#kontak" class="hover:text-orange-400 transition-colors">Kontak</a>
        </div>
      </div>

      <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-300">
        <p>&copy; 2026 RasaNusa. Hak Cipta Dilindungi.</p>
        <p>Jl. Nasional No. 3, Jawa Timur &bull; Senin - Sabtu (09.00 - 20.00 WIB)</p>
      </div>
    </div>
  </footer>

  <!-- Menu Navigasi Mobile (Drawer Bawah) -->
  <div id="mobileMenu" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>

    <div id="mobileSheet" class="absolute left-0 right-0 bottom-[-100%] bg-[#FAF7F2] rounded-t-2xl p-6 border-t border-stone-300 transition-all duration-300 ease-out">
      <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-200">
        <span class="font-bold text-slate-900 text-base">Navigasi RasaNusa</span>
        <button type="button" onclick="toggleMobileMenu()" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-lg text-slate-700 hover:bg-stone-200 font-bold text-sm" aria-label="Tutup menu navigasi">
          Tutup ✕
        </button>
      </div>
      <div class="flex flex-col gap-1.5">
        <a href="#beranda" onclick="toggleMobileMenu()" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Beranda</a>
        <a href="#tentang" onclick="toggleMobileMenu()" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Cerita Dapur</a>
        <a href="#menu" onclick="toggleMobileMenu()" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Menu Pilihan</a>
        <a href="<?= base_url('katalog.php') ?>" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Katalog Lengkap</a>
        <?php if ($has_testi): ?>
        <a href="#testimoni" onclick="toggleMobileMenu()" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Ulasan Pelanggan</a>
        <?php endif; ?>
        <a href="#kontak" onclick="toggleMobileMenu()" class="min-h-[44px] flex items-center px-4 rounded-lg font-bold text-slate-800 hover:bg-stone-200/70">Kontak</a>
        <div class="pt-3 mt-2 border-t border-stone-200">
          <button type="button" onclick="handleContactClick()" class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 text-white py-3 rounded-lg font-bold text-sm">
            Pesan via WhatsApp
          </button>
        </div>
      </div>
    </div>
  </div>

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

  <script src="src/js/index.js"></script>
  <script>
    function sendWA() {
      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      const subjek = document.getElementById('subjek').value;
      const pesan = document.getElementById('pesan').value.trim();
      const feedback = document.getElementById('contactFeedback');
      const nomorWA = "<?= htmlspecialchars($nomor_tujuan, ENT_QUOTES, 'UTF-8') ?>";

      if (nama === "" || pesan === "") {
        feedback.className = "p-3.5 rounded-lg text-sm font-semibold border bg-red-50 text-red-800 border-red-200";
        feedback.textContent = "Mohon lengkapi Nama Anda dan Isi Pesan sebelum mengirim.";
        return;
      }

      feedback.className = "p-3.5 rounded-lg text-sm font-semibold border bg-emerald-50 text-emerald-800 border-emerald-200";
      feedback.textContent = "Membuka WhatsApp untuk meneruskan pesan Anda ke Admin RasaNusa...";

      const teksPesan = `*PESAN DARI WEBSITE RASANUSA*\n`
        + `------------------------------------------\n`
        + `*Nama:* ${nama}\n`
        + `*Email:* ${email || '-'}\n`
        + `*Keperluan:* ${subjek}\n`
        + `------------------------------------------\n`
        + `*Pesan:* ${pesan}`;

      const url = "https://api.whatsapp.com/send?phone=" + nomorWA + "&text=" + encodeURIComponent(teksPesan);
      window.open(url, '_blank');
    }

    const handleContactClick = () => {
      const phoneNumber = "<?= htmlspecialchars($nomor_tujuan, ENT_QUOTES, 'UTF-8') ?>";
      const message = "Halo Admin RasaNusa, saya ingin bertanya mengenai ketersediaan menu hari ini. Mohon informasinya, terima kasih!";
      const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, "_blank");
    };
  </script>
</body>
</html>
