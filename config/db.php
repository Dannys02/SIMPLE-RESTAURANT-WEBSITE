<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST') ?: "127.0.0.1";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$db   = getenv('DB_NAME') ?: "penjualan_umkm";

$siteKey       = getenv('RECAPTCHA_SITE_KEY') ?: "6LcBT4wsAAAAAMjKbTsmCm6W-tNGyG-3Ah0FNqOS";
$secretKey     = getenv('RECAPTCHA_SECRET_KEY') ?: "6LcBT4wsAAAAAL_pBRW0tYfRCa9PQLIhQcb7kbm8";
$telegramToken = getenv('TELEGRAM_BOT_TOKEN') ?: "8150467230:AAHpPlZWlVng8wHy7Vgk8wmKipMLFUh1dQg";
$telegramChat  = getenv('TELEGRAM_CHAT_ID') ?: "6894989857";

// Hitung base URL dinamis berdasarkan posisi folder project terhadap DOCUMENT_ROOT
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$docRoot     = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$basePath    = ($docRoot && strpos($projectRoot, $docRoot) === 0) ? substr($projectRoot, strlen($docRoot)) : '';
$basePath    = rtrim($basePath, '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $basePath);
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $path = ltrim($path, '/');
        return (BASE_URL === '' ? '' : BASE_URL) . '/' . $path;
    }
}

if (!function_exists('abort_error')) {
    function abort_error(
        int $code = 404,
        string $title = 'Halaman Tidak Ditemukan',
        string $message = 'Halaman atau data yang Anda cari tidak tersedia atau sudah dipindahkan.',
        ?string $backUrl = null,
        string $backLabel = 'Kembali ke Beranda'
    ): void {
        $error_code       = $code;
        $error_title      = $title;
        $error_message    = $message;
        $error_back_url   = $backUrl ?? base_url();
        $error_back_label = $backLabel;
        require __DIR__ . '/../error.php';
        exit;
    }
}

$koneksi = @mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    // Jika dipanggil dari endpoint API JSON, kirim respons JSON 500
    if (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false && $_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed', 'total_pending' => 0]);
        exit;
    }

    abort_error(
        500,
        'Layanan Database Tidak Tersedia',
        'Sistem belum dapat terhubung ke database penjualan_umkm. Pastikan MySQL/Laragon sudah aktif.',
        base_url(),
        'Muat Ulang Halaman'
    );
}
?>