<?php
session_start();
include '../config/db.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

$username_val = '';
if (isset($_POST['login'])) {
    $username_val = trim($_POST['username'] ?? '');
    $username = mysqli_real_escape_string($koneksi, $username_val);
    $password = $_POST['password'] ?? '';

    $query = mysqli_query($koneksi, "SELECT * FROM admins WHERE username = '$username'");

    if ($query && mysqli_num_rows($query) === 1) {
        $admin = mysqli_fetch_assoc($query);

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Kata sandi yang Anda masukkan tidak sesuai.";
        }
    } else {
        $error = "Username tidak ditemukan di dalam sistem.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Halaman masuk panel manajemen dapur dan pesanan RasaNusa.">
  <title>Masuk Panel Admin | RasaNusa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('src/css/style.css') ?>" />
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) sebagai latar halaman, Putih (#FFFFFF) untuk kartu autentikasi,
     *   Deep Slate (#0F172A) untuk teks utama (>15:1), dan Terracotta Orange (#C2410C) untuk tombol aksi utama (5.18:1).
     * - Typography: 'Fraunces' untuk judul halaman login dan 'Plus Jakarta Sans' untuk label serta input form.
     * - Layout: Satu titik fokus di tengah layar (kartu login 28rem) dengan header kembali ke katalog publik.
     */
    :root {
      --bg-warm: #FAF7F2;
      --ink-primary: #0F172A;
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

  <header class="border-b border-stone-200/80 bg-[#FAF7F2]">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between" aria-label="Navigasi Halaman Login">
      <a href="<?= base_url() ?>" class="flex items-baseline gap-0.5 text-xl font-extrabold tracking-tight text-slate-900">
        <span class="font-serif-editorial text-orange-700">Rasa</span><span>Nusa</span>
      </a>
      <a href="<?= base_url() ?>" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:text-orange-700 hover:bg-stone-200/50 transition-colors">
        Kembali ke Beranda
      </a>
    </nav>
  </header>

  <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
    <section class="w-full max-w-md bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 md:p-10" aria-labelledby="login-heading">
      <div class="mb-6">
        <span class="section-index mb-2">Panel Pengelola</span>
        <h1 id="login-heading" class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">
          Masuk Admin RasaNusa
        </h1>
        <p class="text-sm text-slate-600 mt-1.5">
          Masukkan akun pengelola untuk mengatur menu, stok porsi, dan pesanan pelanggan.
        </p>
      </div>

      <?php if (isset($error)): ?>
      <div role="alert" class="mb-6 p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" class="space-y-5">
        <div>
          <label for="username" class="block text-sm font-bold text-slate-800 mb-1.5">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            value="<?= htmlspecialchars($username_val, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Masukkan username admin"
            autocomplete="username"
            class="w-full min-h-[48px] bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
            required
          >
        </div>

        <div>
          <label for="password" class="block text-sm font-bold text-slate-800 mb-1.5">Kata Sandi</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan kata sandi"
            autocomplete="current-password"
            class="w-full min-h-[48px] bg-[#FAF7F2] border border-stone-300 px-4 py-3 rounded-lg text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
            required
          >
        </div>

        <button
          type="submit"
          name="login"
          class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-3.5 px-6 rounded-lg transition-all"
        >
          Masuk ke Dashboard Admin
        </button>
      </form>
    </section>
  </main>

  <footer class="py-6 text-center text-xs text-slate-600 border-t border-stone-200/80">
    &copy; 2026 RasaNusa. Sistem Manajemen Katalog &amp; Pesanan UMKM.
  </footer>

</body>
</html>
