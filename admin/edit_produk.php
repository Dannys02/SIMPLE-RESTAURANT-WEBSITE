<?php
/**
 * ============================================
 * HALAMAN EDIT PRODUK (MENU)
 * ============================================
 * Fungsi:
 * - Tampilkan form edit untuk produk yang dipilih
 * - Proses update data produk termasuk gambar
 * - Validasi dan sanitasi input
 */

if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

// ========== PROSES UPDATE PRODUK ==========
if (isset($_POST['update_produk_ini'])) {
    $id_update = (int)$_POST['id'];
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    // Cek apakah ada file baru yang diupload
    if (!empty($_FILES['gambar']['name'])) {
        // Generate nama file unik
        $nama_file = time() . '-' . $_FILES['gambar']['name'];

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "../src/img/" . $nama_file)) {
            // Hapus gambar lama dari folder
            $res_lama = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE id=$id_update");
            $data_lama = mysqli_fetch_assoc($res_lama);

            if ($data_lama['gambar'] && file_exists("../src/img/" . $data_lama['gambar'])) {
                unlink("../src/img/" . $data_lama['gambar']);
            }

            // Update data termasuk gambar baru
            $sql = "UPDATE produk SET nama='$nama', harga='$harga', stok='$stok', deskripsi='$deskripsi', gambar='$nama_file' WHERE id=$id_update";
        }
    } else {
        // Update data tanpa mengganti gambar
        $sql = "UPDATE produk SET nama='$nama', harga='$harga', stok='$stok', deskripsi='$deskripsi' WHERE id=$id_update";
    }

    // Jalankan query update
    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('DATA MENU BERHASIL DIUPDATE!'); window.location.href='index.php?page=menu';</script>";
        exit;
    } else {
        echo "Error SQL: " . mysqli_error($koneksi);
    }
}

// ========== AMBIL DATA PRODUK UNTUK DIEDIT ==========
$id_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$p = null;
if ($id_edit && $id_edit > 0) {
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $id_edit");
    $p = mysqli_fetch_assoc($query);
}

// Cek apakah produk ditemukan
if (!$p) {
    http_response_code(404);
    echo "<div class='max-w-xl mx-auto my-8 p-6 bg-white border border-slate-200 rounded-xl'>
            <span class='inline-block px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded mb-2'>HTTP Error 404</span>
            <h2 class='text-lg font-bold text-slate-900 mb-1'>Menu Tidak Ditemukan</h2>
            <p class='text-sm text-slate-600 mb-4'>Data menu dengan ID #" . htmlspecialchars((string)$id_edit) . " tidak ditemukan atau sudah dihapus.</p>
            <a href='index.php?page=menu' class='inline-block px-4 py-2 bg-orange-600 text-white text-sm font-bold rounded-lg'>Kembali ke Manajemen Menu</a>
          </div>";
    return;
}
?>

<div class="max-w-2xl mx-auto bg-white p-4 rounded-xl shadow-md border border-slate-200">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Edit Produk</h2>
        <a href="index.php?page=menu" class="text-gray-500">Batal</a>
    </div>

    <!-- Form Edit Produk -->
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
        <!-- Hidden: ID Produk -->
        <input type="hidden" name="id" value="<?= $p['id'] ?>">

        <!-- Input: Nama Produk -->
        <div>
            <label class="block text-sm font-bold mb-1">Nama Produk</label>
            <input type="text" name="nama" 
                value="<?= htmlspecialchars($p['nama']) ?>" 
                class="w-full border p-2 rounded shadow-sm" 
                required>
        </div>

        <!-- Input: Harga & Stok -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold mb-1">Harga (Rp)</label>
                <input type="number" name="harga" 
                    value="<?= $p['harga'] ?>" 
                    class="w-full border p-2 rounded shadow-sm" 
                    required>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Stok</label>
                <input type="number" name="stok" 
                    value="<?= $p['stok'] ?>" 
                    class="w-full border p-2 rounded shadow-sm" 
                    required>
            </div>
        </div>

        <!-- Input: Deskripsi -->
        <div>
            <label class="block text-sm font-bold mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="4" 
                class="w-full border p-2 rounded shadow-sm"><?= htmlspecialchars($p['deskripsi']) ?></textarea>
        </div>

        <!-- Input: Gambar -->
        <div>
            <label class="block text-sm font-bold mb-1">Foto (Biarkan jika gak ganti)</label>
            <!-- Tampilkan gambar saat ini -->
            <img src="../src/img/<?= $p['gambar'] ?>" 
                class="w-20 h-20 object-cover rounded mb-2 border">
            <!-- File input untuk gambar baru -->
            <input type="file" name="gambar" accept="image/*" class="text-sm">
        </div>

        <!-- Tombol Submit -->
        <button type="submit" name="update_produk_ini" 
            class="w-full bg-orange-600 text-white font-bold py-3 rounded-lg shadow-lg hover:bg-orange-700">
            Simpan Update
        </button>
    </form>
</div>
