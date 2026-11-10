<?php
/**
 * ============================================
 * HALAMAN MANAJEMEN TESTIMONI PELANGGAN
 * ============================================
 * CRUD Operations:
 * CREATE: Form tambah testimoni baru
 * READ:   Tampilkan daftar testimoni
 * UPDATE: Edit testimoni (di halaman terpisah: edit_testimoni.php)
 * DELETE: Hapus testimoni
 * DISPLAY: Pilih max 3 testimoni untuk ditampilkan di halaman utama
 */

if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

// ========== PROSES SIMPAN TESTIMONI BARU ==========
if (isset($_POST['simpan'])) {
    // Sanitasi input text
    $nama = htmlspecialchars($_POST['nama_pelanggan']);
    $kerja = htmlspecialchars($_POST['pekerjaan']);
    $isi = htmlspecialchars($_POST['isi']);
    $bintang = (int)$_POST['bintang'];

    // Gunakan Prepared Statement untuk keamanan
    $stmt = $koneksi->prepare("INSERT INTO testimoni (nama_pelanggan, pekerjaan, isi, bintang) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nama, $kerja, $isi, $bintang);
    $stmt->execute();

    // Redirect kembali ke halaman testimoni
    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

// ========== PROSES HAPUS TESTIMONI ==========
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Gunakan Prepared Statement
    $stmt = $koneksi->prepare("DELETE FROM testimoni WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Redirect kembali ke halaman testimoni
    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

// ========== PROSES UPDATE PILIHAN TESTIMONI TAMPIL ==========
if (isset($_POST['simpan_testi'])) {
    // Reset semua testimonial (set tampil = 0)
    mysqli_query($koneksi, "UPDATE testimoni SET tampil = 0");

    // Aktifkan (tampil = 1) untuk which yang dicentang
    if (!empty($_POST['pilihan'])) {
        foreach ($_POST['pilihan'] as $id) {
            $id = (int)$id;
            mysqli_query($koneksi, "UPDATE testimoni SET tampil = 1 WHERE id = $id");
        }
    }

    // Redirect kembali ke halaman testimoni
    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

// Ambil semua testimoni dari database
$res = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC");
?>

<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-slate-800">Manajemen Testimoni</h2>

    <!-- FORM TAMBAH TESTIMONI BARU -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-10">
        <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Kolom Kiri: Input Nama & Pekerjaan & Bintang -->
            <div class="space-y-4">
                <!-- Input Nama Pelanggan -->
                <input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan" 
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-orange-500 transition" 
                    required>

                <!-- Input Pekerjaan -->
                <input type="text" name="pekerjaan" placeholder="Pekerjaan" 
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-orange-500 transition" 
                    required>

                <!-- Input Rating (Bintang) -->
                <select name="bintang" 
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-orange-500 transition">
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>
            </div>

            <!-- Kolom Kanan: Input Testimoni & Tombol -->
            <div class="space-y-4">
                <!-- Textarea Isi Testimoni -->
                <textarea name="isi" placeholder="Isi Testimoni" rows="4" 
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-orange-500 transition" 
                    required></textarea>

                <!-- Tombol Simpan -->
                <button type="submit" name="simpan" 
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-orange-100">
                    Simpan Testimoni
                </button>
            </div>
        </form>
    </div>

    <!-- TABEL DAFTAR TESTIMONI -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <form action="" method="POST">
            <table id="tabelPesanan" class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 font-bold text-slate-700">Pelanggan</th>
                        <th class="p-4 font-bold text-slate-700">Testimoni</th>
                        <th class="p-4 font-bold text-slate-700">Rating</th>
                        <th class="p-4 font-bold text-slate-700">Aksi</th>
                        <th class="p-4 font-bold text-slate-700">Tampilkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr class="hover:bg-gray-50">
                            <!-- Kolom Pelanggan (Nama & Pekerjaan) -->
                            <td class="p-4 text-sm">
                                <div class="font-bold">
                                    <?= htmlspecialchars($row['nama_pelanggan']) ?>
                                </div>
                                <div class="text-gray-500 text-xs">
                                    <?= htmlspecialchars($row['pekerjaan']) ?>
                                </div>
                            </td>

                            <!-- Kolom Isi Testimoni -->
                            <td class="p-4 text-sm text-gray-600 italic">"<?= htmlspecialchars($row['isi']) ?>"</td>

                            <!-- Kolom Rating (Bintang) -->
                            <td class="p-4 text-orange-500">
                                <?= str_repeat('⭐', $row['bintang']) ?>
                            </td>

                            <!-- Kolom Aksi (Edit & Hapus) -->
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <!-- Tombol Edit -->
                                    <a href="index.php?page=testimoni_edit&id=<?= $row['id'] ?>" 
                                        class="text-yellow-600 text-sm font-bold">
                                        Edit
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <a href="index.php?page=testimoni&hapus=<?= $row['id'] ?>" 
                                        onclick="return confirm('Hapus testimoni ini?')" 
                                        class="text-red-500 hover:text-red-700 font-bold text-sm">
                                        Hapus
                                    </a>
                                </div>
                            </td>

                            <!-- Kolom Checkbox Tampilkan (Max 3) -->
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                    class="testi-checkbox w-5 h-5 accent-orange-500" 
                                    name="pilihan[]" 
                                    value="<?= $row['id'] ?>" 
                                    <?= $row['tampil'] == 1 ? 'checked' : '' ?> 
                                    onchange="limitCheckbox(this)" />
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Tombol Konfirmasi Tampilkan -->
            <div class="p-4 flex justify-center md:justify-end">
                <button type="submit" name="simpan_testi" 
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-xl transition shadow-lg">
                    Konfirmasi Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Fungsi untuk membatasi jumlah checkbox yang bisa dicentang (Max 3)
     * Mencegah lebih dari 3 testimoni ditampilkan di halaman utama
     */
    function limitCheckbox(el) {
        // Cari semua checkbox yang sedang dicentang
        let checkboxes = document.querySelectorAll('.testi-checkbox:checked');

        if (checkboxes.length > 3) {
            alert("Maksimal hanya 3 testimoni yang boleh ditampilkan!");
            el.checked = false; // Batalkan centangan yang ke-4
        }
    }
</script>