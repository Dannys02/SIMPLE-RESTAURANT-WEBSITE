<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

if (isset($_POST['update_testimoni_ini'])) {
    $id_update = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_pelanggan'] ?? ''));
    $kerja = mysqli_real_escape_string($koneksi, trim($_POST['pekerjaan'] ?? ''));
    $isi = mysqli_real_escape_string($koneksi, trim($_POST['isi'] ?? ''));
    $bintang = max(1, min(5, (int)($_POST['bintang'] ?? 5)));

    $sql = "UPDATE testimoni SET
                nama_pelanggan = '$nama',
                pekerjaan = '$kerja',
                isi = '$isi',
                bintang = '$bintang'
            WHERE id = $id_update";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>window.location.href='index.php?page=testimoni';</script>";
        exit;
    } else {
        $testi_error = "Gagal memperbarui ulasan: " . mysqli_error($koneksi);
    }
}

$id_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$t = null;
if ($id_edit && $id_edit > 0) {
    $query = mysqli_query($koneksi, "SELECT * FROM testimoni WHERE id = $id_edit");
    $t = $query ? mysqli_fetch_assoc($query) : null;
}

if (!$t) {
    http_response_code(404);
    echo "<div class='max-w-xl mx-auto my-8 p-6 bg-white border border-stone-200 rounded-2xl'>
            <span class='inline-block px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded mb-2'>HTTP Error 404</span>
            <h1 class='font-serif-editorial text-xl font-bold text-slate-900 mb-1'>Ulasan Tidak Ditemukan</h1>
            <p class='text-sm text-slate-600 mb-4'>Data ulasan dengan ID #" . htmlspecialchars((string)$id_edit, ENT_QUOTES, 'UTF-8') . " tidak tersedia.</p>
            <a href='index.php?page=testimoni' class='min-h-[44px] inline-flex items-center px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold rounded-lg transition'>Kembali ke Manajemen Ulasan</a>
          </div>";
    return;
}
?>

<header class="border-b border-stone-200 pb-6 mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
  <div>
    <span class="section-index mb-2">Bukti Sosial &amp; Ulasan</span>
    <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Edit Ulasan Pelanggan</h1>
    <p class="text-sm text-slate-600 mt-1">Perbarui kutipan ulasan dari <strong><?= htmlspecialchars($t['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
  </div>
  <a href="index.php?page=testimoni" class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors w-fit">
    Kembali ke Daftar Ulasan
  </a>
</header>

<?php if (!empty($testi_error)): ?>
<div role="alert" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
  <?= htmlspecialchars($testi_error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<section aria-label="Formulir Edit Ulasan" class="max-w-2xl bg-white p-6 sm:p-8 rounded-2xl border border-stone-200">
  <form action="" method="POST" class="space-y-5">
    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">

    <div>
      <label for="nama_pelanggan_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Pelanggan</label>
      <input
        type="text"
        id="nama_pelanggan_edit"
        name="nama_pelanggan"
        value="<?= htmlspecialchars($t['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?>"
        class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        required
      >
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="pekerjaan_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Profesi / Keterangan</label>
        <input
          type="text"
          id="pekerjaan_edit"
          name="pekerjaan"
          value="<?= htmlspecialchars($t['pekerjaan'], ENT_QUOTES, 'UTF-8') ?>"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
          required
        >
      </div>
      <div>
        <label for="bintang_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Penilaian (1 - 5 Bintang)</label>
        <select
          id="bintang_edit"
          name="bintang"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        >
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>" <?= ((int)$t['bintang'] === $i) ? 'selected' : '' ?>>
              <?= $i ?> Bintang
            </option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div>
      <label for="isi_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Isi Ulasan</label>
      <textarea
        id="isi_edit"
        name="isi"
        rows="4"
        class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        required
      ><?= htmlspecialchars($t['isi'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="pt-3 flex flex-col-reverse sm:flex-row justify-end gap-3 border-t border-stone-200">
      <a href="index.php?page=testimoni" class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-stone-300 text-slate-700 hover:bg-stone-100 font-bold text-sm transition">
        Batal
      </a>
      <button
        type="submit"
        name="update_testimoni_ini"
        class="min-h-[44px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-2.5 px-6 rounded-lg transition-all"
      >
        Simpan Perubahan Ulasan
      </button>
    </div>
  </form>
</section>
