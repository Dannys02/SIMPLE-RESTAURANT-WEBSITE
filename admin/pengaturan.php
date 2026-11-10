<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$pesan_sukses = "";
$pesan_error = "";

$sql_admin = "SELECT username, password FROM admins WHERE id = '$admin_id'";
$query_admin = mysqli_query($koneksi, $sql_admin);
$data_admin = $query_admin ? mysqli_fetch_assoc($query_admin) : ['username' => $_SESSION['username'] ?? 'admin', 'password' => ''];

$sql_wa = "SELECT * FROM admins_wa WHERE id = 1";
$query_wa = mysqli_query($koneksi, $sql_wa);
$data_wa = $query_wa ? mysqli_fetch_assoc($query_wa) : null;
$wa_sekarang = $data_wa['nomor'] ?? '6285645837298';
$alamat_sekarang = $data_wa['alamat'] ?? 'Jl. Nasional No. 3, Jawa Timur, Indonesia';
$email_sekarang = $data_wa['email'] ?? 'webToko@gmail.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_profile'])) {
        $user_input = mysqli_real_escape_string($koneksi, trim($_POST['username'] ?? ''));

        if ($user_input !== '') {
            $sql_update_user = "UPDATE admins SET username = '$user_input' WHERE id = '$admin_id'";
            if (mysqli_query($koneksi, $sql_update_user)) {
                $pesan_sukses = "Username pengelola berhasil diperbarui.";
                $data_admin['username'] = $user_input;
                $_SESSION['username'] = $user_input;
            } else {
                $pesan_error = "Gagal memperbarui username pengelola.";
            }
        } else {
            $pesan_error = "Username tidak boleh kosong.";
        }
    }

    if (isset($_POST['update_wa'])) {
        $wa_input = mysqli_real_escape_string($koneksi, preg_replace('/[^0-9]/', '', $_POST['nomor_wa'] ?? ''));
        $alamat_input = mysqli_real_escape_string($koneksi, trim($_POST['alamat_dapur'] ?? ''));
        $email_input = mysqli_real_escape_string($koneksi, trim($_POST['email_kontak'] ?? ''));

        if (strlen($wa_input) < 10) {
            $pesan_error = "Nomor WhatsApp harus terdiri dari minimal 10 digit angka.";
        } elseif ($alamat_input === '') {
            $pesan_error = "Alamat dapur tidak boleh kosong.";
        } elseif ($email_input !== '' && !filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
            $pesan_error = "Format email kontak layanan tidak valid.";
        } else {
            $sql_update_wa = "UPDATE admins_wa SET nomor = '$wa_input', alamat = '$alamat_input', email = '$email_input' WHERE id = 1";
            if (mysqli_query($koneksi, $sql_update_wa)) {
                $pesan_sukses = "Informasi kontak & alamat dapur berhasil diperbarui.";
                $wa_sekarang = $wa_input;
                $alamat_sekarang = $alamat_input;
                $email_sekarang = $email_input;
            } else {
                $pesan_error = "Gagal memperbarui informasi kontak & alamat dapur.";
            }
        }
    }

    if (isset($_POST['update_password'])) {
        $pw_lama = $_POST['password_lama'] ?? '';
        $pw_baru = $_POST['password_baru'] ?? '';
        $pw_konfirmasi = $_POST['konfirmasi_password'] ?? '';

        if (password_verify($pw_lama, $data_admin['password'])) {
            if ($pw_baru === $pw_konfirmasi) {
                if (strlen($pw_baru) < 5) {
                    $pesan_error = "Kata sandi baru minimal harus terdiri dari 5 karakter.";
                } else {
                    $pw_hash = password_hash($pw_baru, PASSWORD_DEFAULT);
                    $sql_update_pw = "UPDATE admins SET password = '$pw_hash' WHERE id = '$admin_id'";

                    if (mysqli_query($koneksi, $sql_update_pw)) {
                        $pesan_sukses = "Kata sandi akun berhasil diperbarui.";
                        $data_admin['password'] = $pw_hash;
                    } else {
                        $pesan_error = "Gagal menyimpan kata sandi baru.";
                    }
                }
            } else {
                $pesan_error = "Konfirmasi kata sandi baru tidak cocok.";
            }
        } else {
            $pesan_error = "Kata sandi lama yang Anda masukkan salah.";
        }
    }
}
?>

<!-- Header Halaman Pengaturan -->
<header class="border-b border-stone-200 pb-6 mb-8">
  <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Pengaturan Akun &amp; Kontak</h1>
  <p class="text-sm text-slate-600 mt-1">
    Kelola identitas masuk pengelola, nomor tujuan WhatsApp pelanggan, serta keamanan kata sandi.
  </p>
</header>

<?php if ($pesan_error !== ''): ?>
<div role="alert" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-sm font-semibold rounded-xl">
  <?= htmlspecialchars($pesan_error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?php if ($pesan_sukses !== ''): ?>
<div role="status" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-xl">
  <?= htmlspecialchars($pesan_sukses, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

  <!-- Kolom Kiri (6 Kolom): Profil Admin & Nomor WhatsApp -->
  <section aria-labelledby="profil-kontak-heading" class="lg:col-span-6 bg-white p-6 sm:p-8 rounded-2xl border border-stone-200 space-y-8">
    <div>
      <h2 id="profil-kontak-heading" class="text-lg font-bold text-slate-900 mb-1">Profil &amp; Kontak WhatsApp</h2>
      <p class="text-xs text-slate-600 mb-6">Informasi ini digunakan untuk masuk ke panel admin dan menerima pesan pesanan dari pelanggan.</p>

      <!-- Form Update Username -->
      <form method="POST" class="pb-6 border-b border-stone-200 space-y-4">
        <div>
          <label for="username_admin" class="block text-sm font-bold text-slate-800 mb-1.5">Username Pengelola</label>
          <input
            type="text"
            id="username_admin"
            name="username"
            value="<?= htmlspecialchars($data_admin['username'], ENT_QUOTES, 'UTF-8') ?>"
            class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
            required
          >
        </div>
        <button
          type="submit"
          name="update_profile"
          class="min-h-[44px] bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg font-bold text-xs transition"
        >
          Simpan Username
        </button>
      </form>
    </div>

    <!-- Form Update Kontak & Alamat Dapur -->
    <form method="POST" class="space-y-4">
      <div>
        <label for="nomor_wa_admin" class="block text-sm font-bold text-slate-800 mb-1.5">
          Nomor WhatsApp Penerima Pesanan (Awali dengan 62)
        </label>
        <input
          type="tel"
          id="nomor_wa_admin"
          name="nomor_wa"
          value="<?= htmlspecialchars($wa_sekarang, ENT_QUOTES, 'UTF-8') ?>"
          placeholder="628123456789"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
        <p class="text-xs text-slate-600 mt-1">
          Seluruh tombol pesan dan formulir kontak di halaman pelanggan diarahkan ke nomor ini.
        </p>
      </div>

      <div>
        <label for="alamat_dapur_admin" class="block text-sm font-bold text-slate-800 mb-1.5">
          Alamat Dapur / Lokasi Operasional
        </label>
        <textarea
          id="alamat_dapur_admin"
          name="alamat_dapur"
          rows="2"
          class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        ><?= htmlspecialchars($alamat_sekarang, ENT_QUOTES, 'UTF-8') ?></textarea>
        <p class="text-xs text-slate-600 mt-1">
          Alamat ini akan ditampilkan di bagian Kontak dan Footer beranda.
        </p>
      </div>

      <div>
        <label for="email_kontak_admin" class="block text-sm font-bold text-slate-800 mb-1.5">
          Email Layanan Pelanggan
        </label>
        <input
          type="email"
          id="email_kontak_admin"
          name="email_kontak"
          value="<?= htmlspecialchars($email_sekarang, ENT_QUOTES, 'UTF-8') ?>"
          placeholder="webToko@gmail.com"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
        >
      </div>

      <button
        type="submit"
        name="update_wa"
        class="min-h-[44px] bg-orange-700 hover:bg-orange-800 text-white px-5 py-2.5 rounded-lg font-bold text-xs transition"
      >
        Simpan Informasi Kontak &amp; Alamat
      </button>
    </form>
  </section>

  <!-- Kolom Kanan (6 Kolom): Keamanan Kata Sandi -->
  <section aria-labelledby="keamanan-sandi-heading" class="lg:col-span-6 bg-white p-6 sm:p-8 rounded-2xl border border-stone-200">
    <h2 id="keamanan-sandi-heading" class="text-lg font-bold text-slate-900 mb-1">Ubah Kata Sandi</h2>
    <p class="text-xs text-slate-600 mb-6">Perbarui kata sandi secara berkala untuk menjaga keamanan data operasional RasaNusa.</p>

    <form method="POST" class="space-y-4">
      <div>
        <label for="password_lama" class="block text-sm font-bold text-slate-800 mb-1.5">Kata Sandi Saat Ini</label>
        <input
          type="password"
          id="password_lama"
          name="password_lama"
          placeholder="Masukkan kata sandi lama"
          autocomplete="current-password"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div>
        <label for="password_baru" class="block text-sm font-bold text-slate-800 mb-1.5">Kata Sandi Baru (Min. 5 Karakter)</label>
        <input
          type="password"
          id="password_baru"
          name="password_baru"
          placeholder="Masukkan kata sandi baru"
          autocomplete="new-password"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div>
        <label for="konfirmasi_password" class="block text-sm font-bold text-slate-800 mb-1.5">Ulangi Kata Sandi Baru</label>
        <input
          type="password"
          id="konfirmasi_password"
          name="konfirmasi_password"
          placeholder="Ketik ulang kata sandi baru"
          autocomplete="new-password"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div class="pt-2">
        <button
          type="submit"
          name="update_password"
          class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white py-3 px-6 rounded-lg font-bold text-sm transition-all"
        >
          Simpan Kata Sandi Baru
        </button>
      </div>
    </form>
  </section>

</div>
