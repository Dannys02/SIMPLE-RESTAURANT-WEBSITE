<?php
include 'config/db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

$sql = "SELECT * FROM produk WHERE nama LIKE '%$search%' OR deskripsi LIKE '%$search%' ORDER BY nama ASC";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Menu Lengkap | TokoSaya</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .orange-gradient {
      background: linear-gradient(135deg, #FF7E5F 0%, #FEB47B 100%);
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800">

  <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <a href="<?= base_url() ?>" class="text-2xl font-extrabold text-orange-600">Toko<span class="text-slate-900">Saya</span></a>
      <a href="<?= base_url('#menu') ?>" class="text-sm font-bold text-gray-500 hover:text-orange-500 transition">← Kembali ke Beranda</a>
    </div>
  </nav>

  <header class="bg-white py-12 border-b border-gray-50">
    <div class="container mx-auto px-4 text-center">
      <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Semua Menu Kami</h1>
      <p class="text-gray-500 mb-8 max-w-xl mx-auto">
        Temukan berbagai pilihan menu terbaik kami dengan mudah melalui fitur pencarian di bawah ini.
      </p>

      <form action="" method="GET" class="max-w-2xl mx-auto relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-orange-500 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
        placeholder="Cari nama menu atau deskripsi..."
        class="w-full pl-12 pr-32 py-4 rounded-2xl border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all shadow-sm">
        <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 orange-gradient text-white font-bold rounded-xl shadow-md hover:scale-105 transition-transform active:scale-95">
          Cari
        </button>
      </form>

      <?php if ($search): ?>
      <p class="mt-4 text-sm text-gray-400 font-medium">
        Menampilkan hasil untuk: <span class="text-orange-600 font-bold">"<?= htmlspecialchars($search) ?>"</span>
        <a href="katalog.php" class="ml-2 text-gray-300 hover:text-red-500 underline">Bersihkan</a>
      </p>
      <?php endif; ?>
    </div>
  </header>

  <main class="container mx-auto px-4 py-16">
    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
      <?php while ($row = mysqli_fetch_assoc($result)): $isOut = ($row['stok'] <= 0); ?>
      <article class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col">
        <div class="relative overflow-hidden">
          <img src="<?= (!empty($row['gambar'])) ? 'src/img/' . $row['gambar'] : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=500' ?>"
          alt="<?= htmlspecialchars($row['nama']) ?>"
          class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500">
          <?php if ($isOut): ?>
          <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center">
            <span class="bg-red-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">Stok Habis</span>
          </div>
          <?php endif; ?>
        </div>

        <div class="p-4 md:p-6 flex flex-col flex-grow">
          <h4 class="text-xl truncate font-bold group-hover:text-orange-600 transition-colors duration-300"><?= $row['nama'] ?></h4>

          <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 pt-4 border-t border-gray-50 gap-2">
            <span class="text-sm md:text-xl font-extrabold text-slate-900">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
            <span class="text-xs font-semibold px-2 py-1 bg-slate-100 rounded-md text-slate-500">Stok: <?= $row['stok'] ?></span>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <button onclick='openModal(<?= json_encode($row) ?>)'
              class="bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold text-sm transition shadow-md shadow-orange-100 disabled:opacity-50 disabled:cursor-not-allowed"
              <?= $isOut ? 'disabled' : '' ?>>
              Pesan
            </button>
            <a href="detail.php?id=<?= $row['id'] ?>" class="border border-orange-200 text-orange-600 hover:bg-orange-50 py-3 rounded-xl font-bold text-sm text-center shadow-sm transition">
              Detail
            </a>
          </div>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
    <?php else : ?>
    <div class="text-center py-20">
      <div class="text-6xl mb-4">
        🔍
      </div>
      <h3 class="text-2xl font-bold text-slate-800">Menu Tidak Ditemukan</h3>
      <p class="text-gray-500 mt-2">
        Maaf, menu yang Anda cari tidak tersedia dalam katalog kami.
      </p>
      <a href="katalog.php" class="mt-8 inline-block text-orange-500 font-bold hover:underline">Tampilkan Semua Menu</a>
    </div>
    <?php endif; ?>
  </main>

  <div id="orderModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-y-auto transform transition-all scale-[0.9] md:scale-[1]">
      <div class="orange-gradient p-6 text-white text-center">
        <h3 class="text-2xl font-bold">Lengkapi Pesanan</h3>
        <p class="text-orange-100 text-sm opacity-90" id="modalSubTitle">
          Menu yang anda pilih
        </p>
      </div>

      <form action="api/create_order.php" method="POST" class="p-8">
        <input type="hidden" name="produk_id" id="produk_id">
        <input type="text" name="perangkap" class="hidden w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" tabindex="-1" autocomplete="off">

        <div class="space-y-4">
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">Nama Pembeli (Min. 3 Huruf)</label>
              <input type="text" name="nama_pembeli" maxlength="50" placeholder="Contoh: Budi" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
            </div>
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">WhatsApp (10-14 Digit)</label>
              <input type="number" name="whatsapp" placeholder="62812..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
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

  <footer class="bg-white border-t border-gray-100 py-10 mt-20">
    <div class="container mx-auto px-4 text-center text-gray-400 text-sm font-medium">
      &copy; 2026 TokoSaya. Hak Cipta Dilindungi.
    </div>
  </footer>

  <script src="src/js/index.js"></script>
</body>
</html>