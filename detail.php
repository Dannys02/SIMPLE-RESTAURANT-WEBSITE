<?php
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

$isOut = ($p['stok'] <= 0);

// Nomor Admin Wa
$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = mysqli_fetch_assoc($q_wa);
$nomor_tujuan = $row_wa['nomor'] ?? '6285645837298';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Detail menu <?= htmlspecialchars($p['nama']) ?> - UMKM Berkualitas">
  <title><?= $p['nama'] ?> | Detail Menu TokoSaya</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .orange-gradient { background: linear-gradient(135deg, #FF7E5F 0%, #FEB47B 100%); }
  </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased">

  <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-orange-100">
    <div class="container mx-auto px-6 py-4">
      <a href="<?= base_url() ?>" class="text-2xl font-extrabold text-orange-600 tracking-tight">Toko<span class="text-slate-900">Saya</span></a>
    </div>
  </nav>

  <main class="container mx-auto px-4 py-8 md:py-16">
    <nav class="mb-8 flex items-center gap-2 text-sm font-medium text-gray-500">
      <a href="<?= base_url('#menu') ?>" class="hover:text-orange-500 transition">Halaman Utama</a>
      <span>/</span>
      <a href="<?= base_url('katalog.php') ?>" class="hover:text-orange-500 transition">Halaman Menu</a>
      <span>/</span>
      <span class="text-orange-600">Detail Menu</span>
    </nav>

    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-orange-100/50 overflow-hidden border border-orange-50">
      <div class="flex flex-col lg:flex-row">

        <div class="lg:w-1/2 p-4 md:p-8">
          <div class="relative group overflow-hidden rounded-[2rem] bg-gray-100 aspect-square">
            <img src="<?= (!empty($p['gambar'])) ? 'src/img/' . $p['gambar'] : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=800' ?>"
            alt="<?= htmlspecialchars($p['nama']) ?>"
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

            <?php if ($isOut): ?>
            <div class="absolute top-6 left-6 bg-red-500 text-white px-6 py-2 rounded-full font-bold shadow-lg">
              Stok Habis
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="lg:w-1/2 p-8 md:p-12 lg:pl-4 flex flex-col justify-center">
          <div class="mb-6">
            <span class="text-orange-500 font-bold uppercase tracking-widest text-xs">Menu Unggulan</span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mt-2 mb-4 leading-tight">
              <?= htmlspecialchars($p['nama']) ?>
            </h1>
            <div class="flex items-center gap-4 mb-6">
              <span class="text-3xl font-black text-slate-900">
                Rp <?= number_format($p['harga'], 0, ',', '.') ?>
              </span>
              <div class="h-6 w-[1px] bg-gray-300"></div>
              <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-lg text-sm font-bold">
                Stok: <?= $p['stok'] ?> Item
              </span>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-6 mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-3 uppercase tracking-wide">Deskripsi Menu</h3>
            <p class="text-gray-600 leading-relaxed text-lg">
              <?= nl2br(htmlspecialchars($p['deskripsi'])) ?>
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-4">
            <button onclick='openModal(<?= json_encode($p) ?>)'
              class="flex-1 orange-gradient text-white py-5 rounded-2xl font-bold text-lg shadow-lg shadow-orange-200 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:hover:scale-100"
              <?= $isOut ? 'disabled' : '' ?>>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Pesan Sekarang
            </button>
            <a href="https://wa.me/<?= $nomor_tujuan ?>?text=Halo admin, saya ingin tanya menu <?= urlencode($p['nama']) ?>"
              target="_blank"
              class="px-8 py-5 border-2 border-slate-200 text-slate-700 rounded-2xl font-bold hover:bg-slate-50 transition flex items-center justify-center">
              Tanya CS
            </a>
          </div>
        </div>

      </div>
    </div>
  </main>

  <div id="orderModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-y-auto transform transition-all scale-[0.9] md:scale-[1]">
      <div class="orange-gradient p-6 text-white text-center">
        <h3 class="text-2xl font-bold">Lengkapi Pesanan</h3>
        <p class="text-orange-100 text-sm opacity-90" id="modalSubTitle">Menu yang anda pilih</p>
      </div>

      <form action="api/create_order.php" method="POST" class="p-8">
        <input type="hidden" name="produk_id" id="produk_id">
        <input type="text" name="perangkap" class="hidden w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" tabindex="-1" autocomplete="off">

        <div class="space-y-4">
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">Nama Pembeli (Min. 3 Huruf)</label>
              <input type="text" name="nama_pembeli" maxlength="50" placeholder="Nama..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
            </div>
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">WhatsApp (10-14 Digit)</label>
              <input type="number" name="whatsapp" placeholder="628..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">Jumlah Beli</label>
              <input type="number" name="stok" id="stok_input" value="1" min="1" max="100" oninput="hitungTotal()" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">Alamat Pengiriman (Min. 10 Karakter)</label>
            <textarea name="alamat" maxlength="255" placeholder="Tuliskan alamat lengkap..." rows="3" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required></textarea>
          </div>

          <input type="hidden" name="harga" id="harga_modal">

          <div class="bg-orange-50 p-4 rounded-2xl flex justify-between items-center border border-orange-100">
            <span class="font-bold text-orange-800 text-lg">Total Pembayaran:</span>
            <span id="total_harga" class="text-2xl font-black text-orange-600">Rp 0</span>
          </div>
          
          <div class="g-recaptcha" data-sitekey="6LcBT4wsAAAAAMjKbTsmCm6W-tNGyG-3Ah0FNqOS"></div>
        </div>

        <div class="flex flex-col-reverse md:flex-row justify-end gap-3 mt-8">
          <button type="button" onclick="closeModal()" class="w-full md:w-auto px-6 py-3 font-semibold text-gray-500 hover:text-gray-700">Batal</button>
          <button type="submit" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-orange-200 transition">Konfirmasi & Kirim</button>
        </div>
      </form>
    </div>
  </div>

  <script src="src/js/index.js"></script>
</body>
</html>
