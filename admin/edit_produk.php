<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

if (isset($_POST['update_produk_ini'])) {
    $id_update = (int)$_POST['id'];
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
    $harga     = (int)($_POST['harga'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi'] ?? ''));

    if (!empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $nama_file = time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "../src/img/" . $nama_file)) {
            $res_lama = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id=$id_update");
            $data_lama = $res_lama ? mysqli_fetch_assoc($res_lama) : null;

            if ($data_lama && !empty($data_lama['gambar']) && file_exists("../src/img/" . $data_lama['gambar'])) {
                unlink("../src/img/" . $data_lama['gambar']);
            }

            $sql = "UPDATE produk SET nama='$nama', harga='$harga', stok='$stok', deskripsi='$deskripsi', gambar='$nama_file' WHERE id=$id_update";
        }
    } else {
        $sql = "UPDATE produk SET nama='$nama', harga='$harga', stok='$stok', deskripsi='$deskripsi' WHERE id=$id_update";
    }

    if (isset($sql) && mysqli_query($koneksi, $sql)) {
        echo "<script>window.location.href='index.php?page=menu';</script>";
        exit;
    } else {
        $edit_error = "Gagal memperbarui data menu: " . mysqli_error($koneksi);
    }
}

$id_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$p = null;
if ($id_edit && $id_edit > 0) {
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $id_edit");
    $p = $query ? mysqli_fetch_assoc($query) : null;
}

if (!$p) {
    http_response_code(404);
    echo "<div class='max-w-xl mx-auto my-8 p-6 bg-white border border-stone-200 rounded-2xl'>
            <span class='inline-block px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded mb-2'>HTTP Error 404</span>
            <h1 class='font-serif-editorial text-xl font-bold text-slate-900 mb-1'>Menu Tidak Ditemukan</h1>
            <p class='text-sm text-slate-600 mb-4'>Data menu dengan ID #" . htmlspecialchars((string)$id_edit, ENT_QUOTES, 'UTF-8') . " tidak ditemukan atau sudah dihapus.</p>
            <a href='index.php?page=menu' class='min-h-[44px] inline-flex items-center px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold rounded-lg transition'>Kembali ke Manajemen Menu</a>
          </div>";
    return;
}
?>

<header class="border-b border-stone-200 pb-6 mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
  <div>
    <span class="section-index mb-2">Katalog &amp; Inventaris</span>
    <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Edit Rincian Hidangan</h1>
    <p class="text-sm text-slate-600 mt-1">Perbarui nama, harga, stok porsi, atau foto untuk menu <strong><?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
  </div>
  <a href="index.php?page=menu" class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors w-fit">
    Kembali ke Daftar Menu
  </a>
</header>

<?php if (!empty($edit_error)): ?>
<div role="alert" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
  <?= htmlspecialchars($edit_error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<section aria-label="Formulir Edit Menu" class="max-w-2xl bg-white p-6 sm:p-8 rounded-2xl border border-stone-200">
  <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

    <div>
      <label for="nama_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Hidangan</label>
      <input
        type="text"
        id="nama_edit"
        name="nama"
        value="<?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?>"
        class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        required
      >
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="harga_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Harga per Porsi (Rp)</label>
        <input
          type="number"
          id="harga_edit"
          name="harga"
          min="0"
          value="<?= (int)$p['harga'] ?>"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
          required
        >
      </div>
      <div>
        <label for="stok_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Stok Tersedia (Porsi)</label>
        <input
          type="number"
          id="stok_edit"
          name="stok"
          min="0"
          value="<?= (int)$p['stok'] ?>"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
          required
        >
      </div>
    </div>

    <div>
      <label for="deskripsi_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Deskripsi Hidangan</label>
      <textarea
        id="deskripsi_edit"
        name="deskripsi"
        rows="4"
        class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
      ><?= htmlspecialchars($p['deskripsi'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="p-4 rounded-xl bg-[#FAF7F2] border border-stone-200 flex flex-col sm:flex-row sm:items-center gap-4">
      <img
        src="../src/img/<?= rawurlencode($p['gambar']) ?>"
        alt="Foto saat ini untuk <?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?>"
        width="80"
        height="80"
        class="w-20 h-20 object-cover rounded-lg border border-stone-300 bg-stone-100 shrink-0"
      >
      <div class="flex-1">
        <label for="gambar_edit" class="block text-sm font-bold text-slate-800 mb-1">Ganti Foto Hidangan (Opsional)</label>
        <p class="text-xs text-slate-600 mb-2">Biarkan kosong jika tidak ingin mengganti foto menu saat ini.</p>
        <input
          type="file"
          id="gambar_edit"
          name="gambar"
          accept=".jpg,.jpeg,.png,.webp,image/*"
          class="w-full text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-stone-300 file:font-bold file:bg-white file:text-slate-800"
        >
      </div>
    </div>

    <div class="pt-3 flex flex-col-reverse sm:flex-row justify-end gap-3 border-t border-stone-200">
      <a href="index.php?page=menu" class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-stone-300 text-slate-700 hover:bg-stone-100 font-bold text-sm transition">
        Batal
      </a>
      <button
        type="submit"
        name="update_produk_ini"
        class="min-h-[44px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-2.5 px-6 rounded-lg transition-all"
      >
        Simpan Perubahan Menu
      </button>
    </div>
  </form>
</section>
