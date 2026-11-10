<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

if (isset($_POST['update_order_ini'])) {
    $id_update = (int)$_POST['id'];
    $nama    = mysqli_real_escape_string($koneksi, trim($_POST['nama_pembeli'] ?? ''));
    $wa      = mysqli_real_escape_string($koneksi, trim($_POST['whatsapp'] ?? ''));
    $alamat  = mysqli_real_escape_string($koneksi, trim($_POST['alamat'] ?? ''));
    $stok    = (int)($_POST['stok'] ?? 1);

    $sql = "UPDATE pesanan SET 
                nama_pembeli = '$nama', 
                whatsapp = '$wa', 
                alamat = '$alamat', 
                stok = $stok 
            WHERE id = $id_update";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>window.location.href='index.php?page=orders';</script>";
        exit;
    } else {
        $order_error = "Gagal memperbarui pesanan: " . mysqli_error($koneksi);
    }
}

$id_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$o = null;
if ($id_edit && $id_edit > 0) {
    $query = mysqli_query($koneksi, "SELECT p.*, pr.nama as nama_produk FROM pesanan p 
                                     JOIN produk pr ON p.produk_id = pr.id 
                                     WHERE p.id = $id_edit");
    $o = $query ? mysqli_fetch_assoc($query) : null;
}

if (!$o) {
    http_response_code(404);
    echo "<div class='max-w-xl mx-auto my-8 p-6 bg-white border border-stone-200 rounded-2xl'>
            <span class='inline-block px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded mb-2'>HTTP Error 404</span>
            <h1 class='font-serif-editorial text-xl font-bold text-slate-900 mb-1'>Data Pesanan Tidak Ditemukan</h1>
            <p class='text-sm text-slate-600 mb-4'>Pesanan dengan ID #" . htmlspecialchars((string)$id_edit, ENT_QUOTES, 'UTF-8') . " tidak tersedia.</p>
            <a href='index.php?page=orders' class='min-h-[44px] inline-flex items-center px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold rounded-lg transition'>Kembali ke Daftar Pesanan</a>
          </div>";
    return;
}
?>

<header class="border-b border-stone-200 pb-6 mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
  <div>
    <span class="section-index mb-2">Transaksi Pelanggan</span>
    <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Edit Data Pesanan</h1>
    <p class="text-sm text-slate-600 mt-1">
      Hidangan dipesan: <strong><?= htmlspecialchars($o['nama_produk'], ENT_QUOTES, 'UTF-8') ?></strong>
    </p>
  </div>
  <a href="index.php?page=orders" class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors w-fit">
    Kembali ke Daftar Pesanan
  </a>
</header>

<?php if (!empty($order_error)): ?>
<div role="alert" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
  <?= htmlspecialchars($order_error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<section aria-label="Formulir Edit Pesanan" class="max-w-2xl bg-white p-6 sm:p-8 rounded-2xl border border-stone-200">
  <form action="" method="POST" class="space-y-5">
    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">

    <div>
      <label for="nama_pembeli_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Pembeli</label>
      <input
        type="text"
        id="nama_pembeli_edit"
        name="nama_pembeli"
        value="<?= htmlspecialchars($o['nama_pembeli'], ENT_QUOTES, 'UTF-8') ?>"
        class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        required
      >
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="wa_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Nomor WhatsApp</label>
        <input
          type="tel"
          id="wa_edit"
          name="whatsapp"
          value="<?= htmlspecialchars($o['whatsapp'], ENT_QUOTES, 'UTF-8') ?>"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
          required
        >
      </div>
      <div>
        <label for="stok_order_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Jumlah Porsi Dipesan</label>
        <input
          type="number"
          id="stok_order_edit"
          name="stok"
          min="1"
          value="<?= (int)$o['stok'] ?>"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
          required
        >
      </div>
    </div>

    <div>
      <label for="alamat_order_edit" class="block text-sm font-bold text-slate-800 mb-1.5">Alamat Lengkap Pengiriman</label>
      <textarea
        id="alamat_order_edit"
        name="alamat"
        rows="4"
        class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        required
      ><?= htmlspecialchars($o['alamat'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="pt-3 flex flex-col-reverse sm:flex-row justify-end gap-3 border-t border-stone-200">
      <a href="index.php?page=orders" class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-stone-300 text-slate-700 hover:bg-stone-100 font-bold text-sm transition">
        Batal
      </a>
      <button
        type="submit"
        name="update_order_ini"
        class="min-h-[44px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-2.5 px-6 rounded-lg transition-all"
      >
        Simpan Perubahan Pesanan
      </button>
    </div>
  </form>
</section>
