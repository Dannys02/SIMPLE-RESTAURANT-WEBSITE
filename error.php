<?php
// Hitung base URL mandiri bila error.php dipanggil langsung oleh Apache ErrorDocument tanpa db.php
if (!function_exists('base_url')) {
    $projectRoot = str_replace('\\', '/', __DIR__);
    $docRoot     = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
    $basePath    = ($docRoot && strpos($projectRoot, $docRoot) === 0) ? substr($projectRoot, strlen($docRoot)) : '';
    $basePath    = rtrim($basePath, '/');
    if (!defined('BASE_URL')) {
        define('BASE_URL', $basePath);
    }
    function base_url(string $path = ''): string {
        $path = ltrim($path, '/');
        return (BASE_URL === '' ? '' : BASE_URL) . '/' . $path;
    }
}

$code = isset($error_code) ? (int)$error_code : (isset($_GET['code']) ? (int)$_GET['code'] : 404);
if (!in_array($code, [400, 403, 404, 429, 500], true)) {
    $code = 404;
}

http_response_code($code);

$defaults = [
    400 => [
        'title'   => 'Permintaan Tidak Valid',
        'message' => 'Data yang dikirim belum lengkap atau formatnya tidak sesuai. Silakan periksa kembali isian Anda.'
    ],
    403 => [
        'title'   => 'Akses Dibatasi',
        'message' => 'Anda tidak memiliki izin untuk membuka halaman atau direktori ini secara langsung.'
    ],
    404 => [
        'title'   => 'Halaman Tidak Ditemukan',
        'message' => 'Alamat URL atau menu yang Anda cari tidak ditemukan. Mungkin tautan salah ketik atau menu sudah dihapus.'
    ],
    429 => [
        'title'   => 'Terlalu Banyak Permintaan',
        'message' => 'Anda mengirim pesanan terlalu cepat. Silakan tunggu satu menit sebelum mencoba memesan kembali.'
    ],
    500 => [
        'title'   => 'Gangguan Pada Server',
        'message' => 'Terjadi kendala teknis saat memproses permintaan Anda. Silakan coba beberapa saat lagi.'
    ],
];

$title     = $error_title ?? ($defaults[$code]['title'] ?? 'Terjadi Kesalahan');
$message   = $error_message ?? ($defaults[$code]['message'] ?? 'Halaman tidak dapat ditampilkan saat ini.');
$backUrl   = $error_back_url ?? base_url();
$backLabel = $error_back_label ?? 'Kembali ke Beranda';
$reqUri    = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $code ?> - <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | RasaNusa</title>
  <script src="https://cdn.tailwindcss.com" defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /*
     * DESIGN READ & REASONS (R-31):
     * - Palette: Warm Cream (#FAF7F2) sebagai latar, Putih (#FFFFFF) untuk kartu error, Deep Slate (#0F172A) untuk teks utama (>15:1),
     *   dan Terracotta Orange (#C2410C) sebagai satu aksen CTA (5.18:1).
     * - Typography: 'Fraunces' untuk judul error & brand 'RasaNusa', serta 'Plus Jakarta Sans' untuk penjelasan dan tombol.
     * - Layout: Satu fokus kartu error di tengah layar dengan CSS mandiri agar tetap bekerja meskipun koneksi CDN terputus.
     */
    :root {
      --bg-page: #FAF7F2;
      --bg-card: #ffffff;
      --text-main: #0f172a;
      --text-muted: #475569;
      --accent: #c2410c;
      --accent-hover: #9a3412;
      --border-color: #e7e5e4;
    }
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: var(--bg-page);
      color: var(--text-main);
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
    }
    .font-serif-editorial {
      font-family: 'Fraunces', Georgia, serif;
    }
    a, button {
      cursor: pointer;
    }
    a:focus-visible, button:focus-visible {
      outline: 3px solid var(--accent);
      outline-offset: 2px;
    }
    .fallback-header {
      background: var(--bg-page);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 1.5rem;
    }
    .fallback-container {
      max-width: 72rem;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .brand-link {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--text-main);
      text-decoration: none;
      letter-spacing: -0.025em;
    }
    .brand-link .serif-part {
      font-family: 'Fraunces', Georgia, serif;
      color: var(--accent);
    }
    .main-area {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .error-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 1rem;
      max-width: 34rem;
      width: 100%;
      padding: 2.5rem 2rem;
    }
    .status-code {
      display: inline-block;
      font-size: 0.8125rem;
      font-weight: 700;
      color: var(--accent);
      background: #ffedd5;
      border: 1px solid #fed7aa;
      padding: 0.35rem 0.75rem;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }
    h1 {
      font-family: 'Fraunces', Georgia, serif;
      font-size: clamp(1.5rem, 4vw, 2rem);
      font-weight: 700;
      color: var(--text-main);
      margin: 0 0 0.75rem 0;
      line-height: 1.25;
    }
    .desc {
      font-size: 1rem;
      line-height: 1.6;
      color: var(--text-muted);
      margin: 0 0 1.5rem 0;
    }
    .path-box {
      background: #FAF7F2;
      border: 1px solid #d6d3d1;
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      color: #334155;
      margin-bottom: 1.75rem;
      word-break: break-all;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
    }
    .btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0.75rem 1.25rem;
      background-color: var(--accent);
      color: #ffffff;
      font-weight: 700;
      font-size: 0.9375rem;
      border-radius: 0.5rem;
      text-decoration: none;
      border: 2px solid var(--accent);
      transition: background-color 0.15s ease;
    }
    .btn-primary:hover {
      background-color: var(--accent-hover);
      border-color: var(--accent-hover);
    }
    .btn-primary:active {
      transform: translateY(1px);
    }
    .btn-secondary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0.75rem 1.25rem;
      background-color: #ffffff;
      color: var(--text-main);
      font-weight: 700;
      font-size: 0.9375rem;
      border-radius: 0.5rem;
      text-decoration: none;
      border: 1px solid #a8a29e;
      transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .btn-secondary:hover {
      background-color: #f5f5f4;
      border-color: #57534e;
    }
    .btn-secondary:active {
      transform: translateY(1px);
    }
    .fallback-footer {
      text-align: center;
      padding: 1.25rem 1rem;
      font-size: 0.8125rem;
      color: var(--text-muted);
      border-top: 1px solid var(--border-color);
      background: var(--bg-page);
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        transition: none !important;
        animation: none !important;
      }
    }
  </style>
</head>
<body>
  <header class="fallback-header">
    <nav class="fallback-container" aria-label="Navigasi Utama">
      <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" class="brand-link">
        <span class="serif-part">Rasa</span><span>Nusa</span>
      </a>
      <a href="<?= htmlspecialchars(base_url('katalog.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">
        Lihat Katalog Menu
      </a>
    </nav>
  </header>

  <main class="main-area">
    <section class="error-card" aria-labelledby="error-heading">
      <span class="status-code">HTTP Error <?= $code ?></span>
      <h1 id="error-heading"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="desc"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

      <div class="path-box" role="status">
        <strong>URL yang diakses:</strong> <?= $reqUri ?>
      </div>

      <div class="actions">
        <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
          <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php if ($backUrl !== base_url('katalog.php')): ?>
        <a href="<?= htmlspecialchars(base_url('katalog.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">
          Buka Katalog Menu
        </a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="fallback-footer">
    &copy; 2026 RasaNusa. Hak Cipta Dilindungi.
  </footer>
</body>
</html>
