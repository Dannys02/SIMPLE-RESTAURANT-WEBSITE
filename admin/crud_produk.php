<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

$form_error = '';

// Proses tambah produk baru
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
    $harga = (int)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi'] ?? ''));

    $filename = $_FILES['gambar']['name'] ?? '';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed, true)) {
        $new_name = time() . "-" . bin2hex(random_bytes(4)) . "." . $ext;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "../src/img/" . $new_name)) {
            mysqli_query($koneksi, "INSERT INTO produk (nama, harga, deskripsi, gambar, stok) 
                                    VALUES ('$nama', '$harga', '$deskripsi', '$new_name', '$stok')");
            echo "<script>window.location.href='index.php?page=menu';</script>";
            exit;
        } else {
            $form_error = 'Gagal mengunggah file foto ke folder penyimpanan.';
        }
    } else {
        $form_error = 'Format file foto harus berupa JPG, JPEG, PNG, atau WEBP.';
    }
}

// Proses hapus produk
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $res = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id=$id");
    $data = $res ? mysqli_fetch_assoc($res) : null;

    if ($data && !empty($data['gambar']) && file_exists("../src/img/" . $data['gambar'])) {
        unlink("../src/img/" . $data['gambar']);
    }

    mysqli_query($koneksi, "DELETE FROM produk WHERE id=$id");
    echo "<script>window.location.href='index.php?page=menu';</script>";
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id DESC");
?>

<!-- Header Halaman -->
<header class="border-b border-stone-200 pb-6 mb-8">
  <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Manajemen Menu &amp; Stok</h1>
  <p class="text-sm text-slate-600 mt-1">
    Tambahkan hidangan baru, perbarui harga per porsi, atau sesuaikan jumlah stok harian dapur.
  </p>
</header>

<?php if ($form_error !== ''): ?>
<div role="alert" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
  <?= htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

  <!-- Kolom Kiri (5 Kolom): Form Tambah Menu Baru -->
  <section aria-labelledby="tambah-menu-heading" class="lg:col-span-5 bg-white p-6 sm:p-7 rounded-2xl border border-stone-200">
    <h2 id="tambah-menu-heading" class="text-lg font-bold text-slate-900 mb-1">Tambah Hidangan Baru</h2>
    <p class="text-xs text-slate-600 mb-6">Isi rincian menu di bawah ini untuk menampilkannya di katalog pelanggan.</p>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label for="nama_menu" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Hidangan</label>
        <input
          type="text"
          id="nama_menu"
          name="nama"
          placeholder="Masukkan nama menu"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="harga_menu" class="block text-sm font-bold text-slate-800 mb-1.5">Harga per Porsi (Rp)</label>
          <input
            type="number"
            id="harga_menu"
            name="harga"
            min="0"
            placeholder="25000"
            class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
            required
          >
        </div>
        <div>
          <label for="stok_menu" class="block text-sm font-bold text-slate-800 mb-1.5">Stok Tersedia (Porsi)</label>
          <input
            type="number"
            id="stok_menu"
            name="stok"
            min="0"
            placeholder="20"
            class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
            required
          >
        </div>
      </div>

      <div>
        <label for="deskripsi_menu" class="block text-sm font-bold text-slate-800 mb-1.5">Deskripsi Menu</label>
        <textarea
          id="deskripsi_menu"
          name="deskripsi"
          rows="3"
          placeholder="Jelaskan bahan utama, tingkat kepedasan, atau porsi sajian..."
          class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
        ></textarea>
      </div>

      <div>
        <label for="gambar_menu" class="block text-sm font-bold text-slate-800 mb-1.5">Foto Hidangan (JPG, PNG, WEBP)</label>
        <input
          type="file"
          id="gambar_menu"
          name="gambar"
          accept=".jpg,.jpeg,.png,.webp,image/*"
          class="w-full text-sm text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border file:border-stone-300 file:text-xs file:font-bold file:bg-[#FAF7F2] file:text-slate-800 hover:file:bg-stone-200/60"
          required
        >
      </div>

      <div class="pt-2">
        <button
          type="submit"
          name="submit"
          class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-3 px-6 rounded-lg transition-all"
        >
          Simpan Menu ke Katalog
        </button>
      </div>
    </form>
  </section>

  <!-- Kolom Kanan (7 Kolom): Tabel Daftar Produk -->
  <section aria-labelledby="daftar-menu-heading" class="lg:col-span-7 bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <div class="p-6 border-b border-stone-200 flex items-center justify-between">
      <div>
        <h2 id="daftar-menu-heading" class="text-lg font-bold text-slate-900">Daftar Menu Aktif</h2>
        <p class="text-xs text-slate-600">Pilih Edit untuk memperbarui stok/harga atau Hapus untuk menarik menu.</p>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table id="tabelPesanan" class="w-full text-left border-collapse">
        <thead class="bg-[#F3EDE4] border-b border-stone-200">
          <tr>
            <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Hidangan</th>
            <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Harga</th>
            <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Stok</th>
            <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700 text-center">Tindakan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
          <?php while ($row = mysqli_fetch_assoc($query)):
            $stok_val = (int)$row['stok'];
          ?>
            <tr class="hover:bg-[#FAF7F2] transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-3.5">
                  <img
                    src="../src/img/<?= rawurlencode($row['gambar']) ?>"
                    alt="Foto <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>"
                    width="56"
                    height="56"
                    loading="lazy"
                    class="w-14 h-14 object-cover rounded-lg border border-stone-200 bg-stone-100 shrink-0"
                  >
                  <div>
                    <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($row['deskripsi'])): ?>
                    <p class="text-xs text-slate-600 line-clamp-1 max-w-[220px] mt-0.5"><?= htmlspecialchars($row['deskripsi'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td class="p-4 text-sm font-bold text-slate-900 whitespace-nowrap">
                Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?>
              </td>

              <td class="p-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-1 rounded text-xs font-bold border <?= $stok_val <= 0 ? 'bg-red-50 text-red-800 border-red-200' : ($stok_val < 5 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-stone-100 text-slate-800 border-stone-200') ?>">
                  <?= $stok_val <= 0 ? 'Habis (0)' : $stok_val . ' Porsi' ?>
                </span>
              </td>

              <td class="p-4 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-2">
                  <a
                    href="index.php?page=menu_edit&id=<?= (int)$row['id'] ?>"
                    class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors"
                  >
                    Edit
                  </a>
                  <a
                    href="index.php?page=menu&delete=<?= (int)$row['id'] ?>"
                    onclick="return confirm('Hapus menu ini dari katalog?')"
                    class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-800 text-xs font-bold transition-colors"
                  >
                    Hapus
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>

</div>
