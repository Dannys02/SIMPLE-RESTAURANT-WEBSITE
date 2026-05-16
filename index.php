<?php
session_start();
$_SESSION['load_time'] = time();
include 'config/db.php';

// Nomor Admin Wa
$q_wa = mysqli_query($koneksi, "SELECT nomor FROM admins_wa LIMIT 1");
$row_wa = mysqli_fetch_assoc($q_wa);
$nomor_tujuan = $row_wa['nomor'] ?? '6285645837298';

$all_product = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");
$all_testi = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Katalog Menu UMKM Terbaik - Segar, Lezat, dan Berkualitas.">
  <title>Toko Saya | Katalog UMKM Pilihan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/css/style.css" />
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .orange-gradient {
      background: linear-gradient(135deg, #FF7E5F 0%, #FEB47B 100%);
    }
  </style>
</head>
<body class="bg-gray-50 text-slate-800">

  <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-orange-100">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <a href="#" class="text-2xl font-extrabold text-orange-600 tracking-tight">Rumah<span class="text-slate-900">Martha</span></a>
      <div class="hidden md:flex items-center gap-8 font-medium">
        <a href="#beranda" class="hover:text-orange-500 transition">Beranda</a>
        <a href="#tentang" class="hover:text-orange-500 transition">Tentang</a>
        <?php if (mysqli_num_rows($all_product) > 0): ?>
        <a href="#menu" class="hover:text-orange-500 transition">Menu</a>
        <?php endif; ?>
        <?php if (mysqli_num_rows($all_testi) > 0): ?>
        <a href="#testimoni" class="hover:text-orange-500 transition">Testimoni</a>
        <?php endif; ?>
        <a href="#kontak" class="hover:text-orange-500 transition">Kontak</a>
        <button onclick="handleContactClick()" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-full text-sm font-semibold transition-all shadow-md">
          Hubungi Kami
        </button>
      </div>
      <button onclick="toggleMobileMenu()" class="md:hidden text-3xl text-slate-800 focus:outline-none">
        ≡
      </button>
    </div>
  </nav>

  <section id="beranda" class="relative overflow-hidden bg-white py-12 md:py-24 lg:py-0">
    <div class="container mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
      <div class="from-bottom">
        <h2 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
          Rasakan Kelezatan <span class="text-orange-500">Autentik</span> di Setiap Gigitan.
        </h2>
        <p class="text-gray-600 text-lg mb-8 leading-relaxed">
          Kami menghadirkan menu UMKM pilihan yang dibuat dengan bahan baku premium dan resep warisan yang menggugah selera.
        </p>
        <a href="#menu" class="inline-block orange-gradient text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-orange-200 hover:scale-105 transition-transform">
          Lihat Katalog Menu
        </a>
      </div>
      <div class="relative from-bottom">
        <div class="absolute -z-10 w-72 h-72 bg-orange-200 rounded-full blur-3xl opacity-50 top-0 right-0"></div>
        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&q=80&w=800" alt="Hero Image" class="rounded-3xl shadow-2xl">
      </div>
    </div>
  </section>

  <section id="tentang" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-12">
        <div class="md:w-1/2 relative">
          <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-orange-100 rounded-2xl -z-10"></div>
          <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&q=80&w=800" alt="Tim Kami" class="rounded-[2.5rem] from-bottom shadow-2xl object-cover h-[400px] w-full">
        </div>
        <div class="md:w-1/2">
          <p class="from-bottom">
            <span class="text-orange-500 font-bold uppercase tracking-wider text-sm">Cerita Kami</span>
          </p>
          <h3 class="from-bottom text-3xl md:text-4xl font-extrabold text-slate-900 mt-2 mb-6">Berawal dari Dapur Rumah, Menuju Meja Anda.</h3>
          <p class="from-bottom text-gray-600 leading-relaxed mb-6">
            <span class="text-orange-600">Toko</span>Saya lahir dari keinginan sederhana untuk berbagi cita rasa autentik nusantara. Kami percaya bahwa setiap menu memiliki cerita, dan kami berkomitmen untuk hanya menggunakan bahan baku lokal terbaik guna mendukung ekosistem UMKM di sekitar kami.
          </p>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h4 class="from-bottom text-2xl font-bold text-orange-600">200+</h4>
              <p class="from-bottom text-gray-500 text-sm">
                Pelanggan Puas
              </p>
            </div>
            <div>
              <h4 class="from-bottom text-2xl font-bold text-orange-600">100%</h4>
              <p class="from-bottom text-gray-500 text-sm">
                Bahan Halal
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php
  $cek_jumlah = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk");
  $total_data = mysqli_fetch_assoc($cek_jumlah)['total'];

  $query_limit = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC LIMIT 6");

  if (mysqli_num_rows($query_limit) > 0):
  ?>
  <section id="menu" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="from-bottom text-center mb-16">
        <h3 class="text-3xl font-bold mb-4">Menu Unggulan Kami</h3>
        <div class="w-20 h-1.5 bg-orange-500 mx-auto rounded-full"></div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php
        $i = 0;
        while ($row = mysqli_fetch_assoc($query_limit)):
        $isOut = ($row['stok'] <= 0);
        ?>
        <div style="transition-delay: <?= $i * 0.2 ?>s" class="from-bottom">
          <article class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col">
              <div class="relative overflow-hidden">
                <img src="<?= (!empty($row['gambar'])) ? 'src/img/' . $row['gambar'] : 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=500' ?>" alt="<?= htmlspecialchars($row['nama']) ?>"
                class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500">
                <?php if ($isOut): ?>
                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center">
                  <span class="bg-red-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">
                    Stok Habis
                  </span>
                </div>
                <?php endif; ?>
             </div>
  
            <div class="p-4 md:p-6 flex flex-col flex-grow">
              <h4 class="text-xl truncate font-bold group-hover:text-orange-600 transition-colors duration-300">
                <?= $row['nama'] ?>
              </h4>

              <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 pt-4 border-t border-gray-50 gap-2">
                <span class="text-sm md:text-xl font-extrabold text-slate-900">
                  Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                </span>

                <span class="text-xs font-semibold px-2 py-1 bg-slate-100 rounded-md text-slate-500">
                  Stok: <?= $row['stok'] ?>
                </span>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <button onclick='openModal(<?= json_encode($row) ?>)' class="bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold text-sm transition shadow-md shadow-orange-100 disabled:opacity-50 disabled:cursor-not-allowed"
                  <?= $isOut ? 'disabled' : '' ?>>
            Pesan
          </button>
    
                <a href="detail.php?id=<?= $row['id'] ?>" class="border border-orange-200 text-orange-600 hover:bg-orange-50 py-3 rounded-xl font-bold text-sm text-center shadow-sm transition">
                    Detail
                </a>
              </div>
            </div>
          </article>
        </div>
        <?php $i++; endwhile; ?>
      </div>

      <?php if ($total_data > 6): ?>
      <div class="from-bottom delay-[1s] text-center mt-12">
        <a href="katalog.php" class="inline-block border-2 border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white px-8 py-3 rounded-xl font-bold transition-all duration-300">
          Lihat Semua Menu
        </a>
      </div>
      <?php endif; ?>

    </div>
  </section>
  <?php endif; ?>

  <?php
  $query_testi = mysqli_query($koneksi, "SELECT * FROM testimoni WHERE tampil = 1 LIMIT 3");

  if (mysqli_num_rows($query_testi) > 0):
  ?>
  <section id="testimoni" class="py-20 bg-orange-50/50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h3 class="from-bottom text-3xl font-bold mb-4">Apa Kata Mereka?</h3>
        <p class="from-bottom text-gray-500">
          Kepuasan pelanggan adalah prioritas utama kami.
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php 
        $in = 0; 
        while ($t = mysqli_fetch_assoc($query_testi)): 
        ?>
        <div style="transition-delay: <?= $in * 0.2 ?>s" class="from-bottom bg-white p-8 rounded-3xl shadow-sm border border-orange-100 flex flex-col">
          <div class="flex text-orange-400 mb-4">
            <?php
            for ($i = 1; $i <= $t['bintang']; $i++):
            ?>
            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-.11.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <?php endfor; ?>
          </div>
          <p class="text-gray-600 italic mb-6 flex-grow">
            "<?= htmlspecialchars($t['isi']) ?>"
          </p>
          <div class="flex items-center gap-4 mt-auto">
            <div class="h-12 aspect-square bg-orange-200 rounded-full flex items-center justify-center font-bold text-orange-600 uppercase">
              <?= substr($t['nama_pelanggan'], 0, 1) ?>
            </div>
            <div>
              <h5 class="font-bold text-slate-900"><?= htmlspecialchars($t['nama_pelanggan']) ?></h5>
              <p class="text-xs text-gray-400">
                <?= htmlspecialchars($t['pekerjaan']) ?>
              </p>
            </div>
          </div>
        </div>
        <?php $in++; endwhile; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  
  <section id="kontak" class="py-24 bg-white relative overflow-hidden">
    <div class="container mx-auto px-4">
      <div class="max-w-6xl mx-auto bg-white rounded-[2.5rem] shadow-2xl shadow-orange-100/50 border border-orange-50">
        <div class="flex flex-col lg:flex-row">
          
          <div class="lg:w-5/12 orange-gradient p-10 md:p-14 text-white flex flex-col justify-between rounded-b rounded-[50px]">
            <div>
              <h3 class="from-bottom text-3xl md:text-4xl font-extrabold mb-6">Ayo Ngobrol!</h3>
              <p class="from-bottom text-orange-50 leading-relaxed mb-10 opacity-90">
                Punya pertanyaan tentang menu kami atau ingin kerja sama? Kami siap mendengarkan Anda kapan saja.
              </p>

              <div class="space-y-8">
                <div class="from-bottom flex items-start gap-5">
                  <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  </div>
                  <div>
                    <h5 class="font-bold text-lg">Lokasi Kami</h5>
                    <p class="text-orange-100 text-sm">Jl. Nasional No. 3, Jawa Timur, Indonesia</p>
                  </div>
                </div>

                <div class="from-bottom flex items-start gap-5">
                  <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                    <h5 class="font-bold text-lg">Email Support</h5>
                    <p class="text-orange-100 text-sm">webToko.com</p>
                  </div>
                </div>

                <div class="from-bottom flex items-start gap-5">
                  <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <div>
                    <h5 class="font-bold text-lg">Jam Kerja</h5>
                    <p class="text-orange-100 text-sm">Senin - Sabtu: 09:00 - 20:00</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="from-bottom mt-12 pt-8 border-t border-white/20 flex gap-4">
              <span class="text-sm font-medium text-orange-100">Ikuti kami:</span>
              <div class="flex gap-3">
                <a href="#" class="rounded-full flex items-center justify-center transition">
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                  </svg>
                </a>
                <a href="#" class="rounded-full flex items-center justify-center transition">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                    <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z" />
                  </svg>
                </a>
                <a href="#" class="rounded-full flex items-center justify-center transition">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                    <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <div class="lg:w-7/12 p-10 md:p-14 bg-white">
            <form action="" method="POST" class="space-y-6">
              <div class="grid md:grid-cols-2 gap-6">
                <div class="from-bottom space-y-2">
                  <label class="text-sm font-bold text-slate-700 ml-1">Nama Lengkap</label>
                  <input type="text" id="nama" placeholder="Masukkan nama..." class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition-all" required>
                </div>
                <div class="from-bottom space-y-2">
                  <label class="text-sm font-bold text-slate-700 ml-1">Alamat Email</label>
                  <input type="email" id="email" placeholder="Contoh: budi@gmail.com" class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition-all" required>
                </div>
              </div>

              <div class="from-bottom space-y-2">
                <label class="text-sm font-bold text-slate-700 ml-1">Subjek Pesan</label>
                <select id="subjek" class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition-all appearance-none">
                  <option value="Tanya Menu">Tanya Menu</option>
                  <option value="Kritik & Saran">Kritik & Saran</option>
                  <option value="Kemitraan UMKM">Kemitraan UMKM</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>

              <div class="from-bottom space-y-2">
                <label class="text-sm font-bold text-slate-700 ml-1">Pesan Anda</label>
                <textarea id="pesan" rows="4" placeholder="Tuliskan pesan Anda di sini..." class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition-all" required></textarea>
              </div>
              
              <div class="from-bottom">
                <button type="button" onclick="sendWA()" class="w-full orange-gradient text-white py-4 rounded-2xl font-bold shadow-xl shadow-orange-200 hover:shadow-orange-300 transform hover:-translate-y-1 transition-all duration-300">
                  Kirim Pesan
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </section>


  <footer class="bg-slate-900 text-white pt-20 pb-10">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
        <div>
          <h4 class="text-2xl font-bold text-orange-500 mb-6">TokoSaya</h4>
          <p class="text-slate-400 leading-relaxed mb-6">
            Membawa kebahagiaan ke rumah Anda melalui hidangan lokal yang autentik dan higienis.
          </p>
          <div class="flex gap-4">
            <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-orange-500 transition">
              <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
              </svg>
            </a>
            <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-orange-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z" />
              </svg>
            </a>
            <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-orange-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
              </svg>
            </a>
          </div>
        </div>
        <div>
          <h5 class="text-lg font-bold mb-6">Hubungi Kami</h5>
          <ul class="space-y-4 text-slate-400">
            <li class="flex items-start gap-3">
              <span class="text-orange-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                  <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                </svg>
              </span>
              Jl. Nasional No. 3, Jawa Timur
            </li>
            <li class="flex items-center gap-3">
              <span class="text-orange-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                  <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                </svg>
              </span>
              +62 85*-****-****
            </li>
            <li class="flex items-center gap-3">
              <span class="text-orange-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                </svg>
              </span>
              webToko@gmail.com
            </li>
          </ul>
        </div>
        <div>
          <h5 class="text-lg font-bold mb-6">Jam Operasional</h5>
          <ul class="space-y-2 text-slate-400 text-sm">
            <li class="flex justify-between"><span>Senin - Jumat</span> <span>09:00 - 20:00</span></li>
            <li class="flex justify-between"><span>Sabtu</span> <span>09:00 - 17:00</span></li>
            <li class="flex justify-between text-orange-500"><span>Minggu</span> <span>Tutup</span></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-800 pt-8 text-center text-slate-500 text-xs">
        <p>
          &copy; 2026 TokoSaya. Hak Cipta Dilindungi.
        </p>
      </div>
    </div>
  </footer>

  <div id="mobileMenu" class="fixed inset-0 z-[70] hidden transition-all duration-500">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>

    <div id="mobileSheet" class="absolute left-0 right-0 bottom-[-100%] bg-white rounded-t-[2.5rem] p-8 transition-all duration-500 ease-in-out">
      <div class="w-12 h-1.5 bg-gray-200 mx-auto rounded-full mb-8"></div>
      <div class="flex flex-col gap-2 text-center">
        <a href="#beranda" onclick="toggleMobileMenu()" class="text-xl font-bold rounded-xl py-2 text-slate-800 transation-colors duration-300 ease hover:text-orange-500 hover:bg-orange-100">Beranda</a>
        <a href="#tentang" onclick="toggleMobileMenu()" class="text-xl font-bold rounded-xl py-2 text-slate-800 transation-colors duration-300 ease hover:text-orange-500 hover:bg-orange-100">Tentang</a>
        <a href="#menu" onclick="toggleMobileMenu()" class="text-xl font-bold rounded-xl py-2 text-slate-800 transation-colors duration-300 ease hover:text-orange-500 hover:bg-orange-100">Menu</a>
        <a href="#kontak" onclick="toggleMobileMenu()" class="text-xl font-bold rounded-xl py-2 text-slate-800 transation-colors duration-300 ease hover:text-orange-500 hover:bg-orange-100">Kontak</a>
        <hr class="border-gray-100 my-2">
        <button onclick="handleContactClick()" class="orange-gradient text-white py-4 rounded-2xl font-bold shadow-lg">
          Hubungi Kami
        </button>
      </div>
    </div>
  </div>

  <div id="orderModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-y-auto transform transition-all scale-[0.65] lg:scale-[0.89]">
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
              <input type="text" name="nama_pembeli" maxlength="50" placeholder="Nama..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
            </div>
            <div>
              <label class="block text-sm font-semibold mb-1 ml-1 text-gray-700">WhatsApp (10-14 Digit)</label>
              <input type="tel" name="whatsapp" placeholder="628..." pattern="[0-9]{10,14}" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition" required>
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

          <div class="g-recaptcha" data-sitekey="6LcBT4wsAAAAAMjKbTsmCm6W-tNGyG-3Ah0FNqOS"></div>

          <input type="hidden" name="harga" id="harga_modal">

          <div class="bg-orange-50 p-4 rounded-2xl flex justify-between items-center border border-orange-100">
            <span class="font-bold text-orange-800 text-lg">Total Pembayaran:</span>
            <span id="total_harga" class="text-2xl font-black text-orange-600">Rp 0</span>
          </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row justify-end gap-3 mt-8">
          <button type="button" onclick="closeModal()" class="w-full md:w-auto px-6 py-3 font-semibold text-gray-500 hover:text-gray-700">Batal</button>
          <button type="submit" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-orange-200 transition">Konfirmasi & Kirim</button>
        </div>
      </form>
    </div>
  </div>

  <script src="src/js/index.js"></script>
  <script>
    function sendWA() {
    // 1. Ambil data dari input
    const nama = document.getElementById('nama').value;
    const email = document.getElementById('email').value;
    const subjek = document.getElementById('subjek').value;
    const pesan = document.getElementById('pesan').value;
    
    // Nomor WA Admin
    const nomorWA = "<?= $nomor_tujuan ?>"; 

    // 2. Validasi sederhana
    if (nama === "" || pesan === "") {
        alert("Mohon isi Nama dan Pesan Anda!");
        return;
    }

    // 3. Susun format pesan rapi (Gunakan %0A untuk baris baru)
    const teksPesan = `*📩 PESAN BARU DARI WEBSITE*
    ------------------------------------------
*Nama:* ${nama}
*Email:* ${email}
*Subjek:* ${subjek}
    ------------------------------------------
*Pesan:* ${pesan}
    
Dikirim dari *Website Restoran Sederhana*`;

    // 4. Arahkan ke WhatsApp API
    const url = "https://api.whatsapp.com/send?phone=" + nomorWA + "&text=" + encodeURIComponent(teksPesan);
    
    // Buka di tab baru
    window.open(url, '_blank');
    }
    
    const handleContactClick = () => {
      const phoneNumber = "<?= $nomor_tujuan ?>";
      const message =
          "Halo Admin, saya melihat website Anda dan ingin bertanya lebih lanjut. Mohon infonya ya, terima kasih!";
  
      // Encode pesan agar format URL valid
      const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(
          message
      )}`;
  
      // Buka WhatsApp di tab baru
      window.open(url, "_blank");
    };
  </script>
</body>
</html>