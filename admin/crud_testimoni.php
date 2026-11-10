<?php
if (!defined('AKSES_AMAN')) {
    require_once __DIR__ . '/../config/db.php';
    abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

// Proses simpan testimoni baru
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $kerja = trim($_POST['pekerjaan'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $bintang = max(1, min(5, (int)($_POST['bintang'] ?? 5)));

    $stmt = $koneksi->prepare("INSERT INTO testimoni (nama_pelanggan, pekerjaan, isi, bintang) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nama, $kerja, $isi, $bintang);
    $stmt->execute();

    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

// Proses hapus testimoni
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = $koneksi->prepare("DELETE FROM testimoni WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

// Proses update pilihan testimoni tampil (maksimal 3)
if (isset($_POST['simpan_testi'])) {
    mysqli_query($koneksi, "UPDATE testimoni SET tampil = 0");

    if (!empty($_POST['pilihan']) && is_array($_POST['pilihan'])) {
        $count = 0;
        foreach ($_POST['pilihan'] as $id) {
            if ($count >= 3) break;
            $id = (int)$id;
            mysqli_query($koneksi, "UPDATE testimoni SET tampil = 1 WHERE id = $id");
            $count++;
        }
    }

    echo "<script>window.location.href='index.php?page=testimoni';</script>";
    exit;
}

$res = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC");
?>

<!-- Header Halaman -->
<header class="border-b border-stone-200 pb-6 mb-8">
  <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Manajemen Ulasan Pelanggan</h1>
  <p class="text-sm text-slate-600 mt-1">
    Catat ulasan pelanggan nyata dan pilih maksimal 3 ulasan terbaik untuk ditampilkan di halaman utama RasaNusa.
  </p>
</header>

<div id="checkboxAlert" role="alert" class="hidden mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm font-semibold">
  Maksimal hanya 3 ulasan pelanggan yang dapat ditampilkan sekaligus di beranda.
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

  <!-- Kolom Kiri (4 Kolom): Form Tambah Testimoni -->
  <section aria-labelledby="tambah-ulasan-heading" class="lg:col-span-4 bg-white p-6 sm:p-7 rounded-2xl border border-stone-200">
    <h2 id="tambah-ulasan-heading" class="text-lg font-bold text-slate-900 mb-1">Tambah Ulasan Baru</h2>
    <p class="text-xs text-slate-600 mb-6">Masukkan kesan dan penilaian nyata dari pelanggan.</p>

    <form action="" method="POST" class="space-y-4">
      <div>
        <label for="nama_pelanggan" class="block text-sm font-bold text-slate-800 mb-1.5">Nama Pelanggan</label>
        <input
          type="text"
          id="nama_pelanggan"
          name="nama_pelanggan"
          placeholder="Nama pelanggan"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div>
        <label for="pekerjaan_pelanggan" class="block text-sm font-bold text-slate-800 mb-1.5">Profesi / Keterangan</label>
        <input
          type="text"
          id="pekerjaan_pelanggan"
          name="pekerjaan"
          placeholder="Pelanggan Tetap / Karyawan / Mahasiswa"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        >
      </div>

      <div>
        <label for="bintang_ulasan" class="block text-sm font-bold text-slate-800 mb-1.5">Penilaian (1 - 5 Bintang)</label>
        <select
          id="bintang_ulasan"
          name="bintang"
          class="w-full min-h-[44px] bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 focus:bg-white transition"
        >
          <option value="5">5 Bintang (Sangat Memuaskan)</option>
          <option value="4">4 Bintang (Memuaskan)</option>
          <option value="3">3 Bintang (Cukup Baik)</option>
          <option value="2">2 Bintang (Kurang)</option>
          <option value="1">1 Bintang (Perlu Perbaikan)</option>
        </select>
      </div>

      <div>
        <label for="isi_ulasan" class="block text-sm font-bold text-slate-800 mb-1.5">Isi Ulasan</label>
        <textarea
          id="isi_ulasan"
          name="isi"
          rows="4"
          placeholder="Tuliskan kutipan ulasan pelanggan..."
          class="w-full bg-[#FAF7F2] border border-stone-300 px-3.5 py-2.5 rounded-lg text-sm text-slate-900 placeholder:text-slate-500 focus:bg-white transition"
          required
        ></textarea>
      </div>

      <div class="pt-2">
        <button
          type="submit"
          name="simpan"
          class="w-full min-h-[48px] bg-orange-700 hover:bg-orange-800 active:translate-y-px text-white font-bold text-sm py-3 px-6 rounded-lg transition-all"
        >
          Simpan Ulasan
        </button>
      </div>
    </form>
  </section>

  <!-- Kolom Kanan (8 Kolom): Tabel Daftar Testimoni -->
  <section aria-labelledby="daftar-ulasan-heading" class="lg:col-span-8 bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <form action="" method="POST">
      <div class="p-6 border-b border-stone-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 id="daftar-ulasan-heading" class="text-lg font-bold text-slate-900">Daftar Ulasan Tersimpan</h2>
          <p class="text-xs text-slate-600">Centang kolom <strong>Tampil</strong> (maks. 3), lalu klik tombol Simpan Pilihan Tampil.</p>
        </div>
        <button
          type="submit"
          name="simpan_testi"
          class="min-h-[44px] bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-5 rounded-lg text-xs transition shrink-0"
        >
          Simpan Pilihan Tampil
        </button>
      </div>

      <div class="overflow-x-auto">
        <table id="tabelPesanan" class="w-full text-left border-collapse">
          <thead class="bg-[#F3EDE4] border-b border-stone-200">
            <tr>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Pelanggan</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Kutipan Ulasan</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Rating</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700 text-center">Tampil</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700 text-center">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-200">
            <?php while ($row = mysqli_fetch_assoc($res)):
              $id_t = (int)$row['id'];
            ?>
              <tr class="hover:bg-[#FAF7F2] transition-colors">
                <td class="p-4 text-sm whitespace-nowrap">
                  <div class="font-bold text-slate-900">
                    <?= htmlspecialchars($row['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="text-slate-600 text-xs mt-0.5">
                    <?= htmlspecialchars($row['pekerjaan'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </td>

                <td class="p-4 text-sm text-slate-700 leading-relaxed max-w-xs">
                  "<?= htmlspecialchars($row['isi'], ENT_QUOTES, 'UTF-8') ?>"
                </td>

                <td class="p-4 whitespace-nowrap">
                  <span class="inline-block px-2.5 py-1 rounded bg-orange-50 border border-orange-200 text-orange-800 text-xs font-bold">
                    <?= (int)$row['bintang'] ?> / 5
                  </span>
                </td>

                <td class="p-4 text-center">
                  <label for="tampil_<?= $id_t ?>" class="inline-flex items-center justify-center min-h-[38px] min-w-[38px] cursor-pointer">
                    <span class="sr-only">Tampilkan ulasan <?= htmlspecialchars($row['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?> di beranda</span>
                    <input
                      type="checkbox"
                      id="tampil_<?= $id_t ?>"
                      class="testi-checkbox w-5 h-5 accent-orange-700 rounded cursor-pointer"
                      name="pilihan[]"
                      value="<?= $id_t ?>"
                      <?= ((int)$row['tampil'] === 1) ? 'checked' : '' ?>
                      onchange="limitCheckbox(this)"
                    />
                  </label>
                </td>

                <td class="p-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-2">
                    <a
                      href="index.php?page=testimoni_edit&id=<?= $id_t ?>"
                      class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors"
                    >
                      Edit
                    </a>
                    <a
                      href="index.php?page=testimoni&hapus=<?= $id_t ?>"
                      onclick="return confirm('Hapus ulasan pelanggan ini?')"
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
    </form>
  </section>

</div>

<script>
  function limitCheckbox(el) {
    let checkboxes = document.querySelectorAll('.testi-checkbox:checked');
    const alertBox = document.getElementById('checkboxAlert');

    if (checkboxes.length > 3) {
      el.checked = false;
      if (alertBox) {
        alertBox.classList.remove('hidden');
        setTimeout(() => alertBox.classList.add('hidden'), 4000);
      }
    } else if (alertBox) {
      alertBox.classList.add('hidden');
    }
  }
</script>
