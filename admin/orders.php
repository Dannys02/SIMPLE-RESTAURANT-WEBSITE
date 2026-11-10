<?php
if (!defined('AKSES_AMAN')) {
  require_once __DIR__ . '/../config/db.php';
  abort_error(403, 'Akses Langsung Ditolak', 'File komponen admin tidak dapat diakses secara langsung.', base_url('admin/index.php'), 'Ke Dashboard Admin');
}

if (!isset($_SESSION['admin_logged_in'])) {
  echo "<script>window.location.href='login.php';</script>";
  exit;
}

$action = $_GET['action'] ?? null;
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($action && $id > 0) {
  $query_cek = "SELECT p.stok as qty_pesan, p.produk_id, p.status, pr.stok as stok_sekarang
                FROM pesanan p
                JOIN produk pr ON p.produk_id = pr.id
                WHERE p.id = $id";
  $res_cek = mysqli_query($koneksi, $query_cek);
  $data = mysqli_fetch_assoc($res_cek);

  if ($data) {
    $qty_pesan = (int)$data['qty_pesan'];
    $stok_sekarang = (int)$data['stok_sekarang'];
    $pid = (int)$data['produk_id'];
    $status_saat_ini = $data['status'];

    if ($action === 'setuju' && $status_saat_ini === 'pending') {
      if ($stok_sekarang >= $qty_pesan) {
        mysqli_query($koneksi, "UPDATE pesanan SET status = 'setuju' WHERE id = $id");
        mysqli_query($koneksi, "UPDATE produk SET stok = stok - $qty_pesan WHERE id = $pid");
      } else {
        echo "<script>alert('Gagal! Stok tidak mencukupi.'); window.location.href='index.php?page=orders';</script>";
        exit;
      }
    } elseif ($action === 'selesai' && $status_saat_ini === 'setuju') {
      mysqli_query($koneksi, "UPDATE pesanan SET status = 'selesai' WHERE id = $id");
    } elseif ($action === 'tolak' && $status_saat_ini === 'pending') {
      mysqli_query($koneksi, "UPDATE pesanan SET status = 'tolak' WHERE id = $id");
    } elseif ($action === 'dibatalkan' && $status_saat_ini === 'setuju') {
      mysqli_query($koneksi, "UPDATE produk SET stok = stok + $qty_pesan WHERE id = $pid");
      mysqli_query($koneksi, "UPDATE pesanan SET status = 'dibatalkan' WHERE id = $id");
    }

    echo "<script>window.location.href='index.php?page=orders';</script>";
    exit;
  }
}

if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $stmt = $koneksi->prepare("DELETE FROM pesanan WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  echo "<script>window.location.href='index.php?page=orders';</script>";
  exit;
}

$query = "SELECT p.*, pr.nama as nama_produk, pr.harga, pr.stok as stok_saat_ini FROM pesanan p
          JOIN produk pr ON p.produk_id = pr.id
          ORDER BY p.id DESC";
$all_orders = mysqli_query($koneksi, $query);
?>

<div class="mb-6 w-full flex flex-col md:flex-row md:justify-between">
  <h1 class="text-2xl font-bold text-slate-800">Daftar Pesanan Masuk</h1>
  <a href="cetak_laporan.php" target="_blank" class="bg-orange-600 mt-2 md:mt-0 w-fit text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-orange-700 shadow-md">
    print Laporan Penjualan
  </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden overflow-x-auto">
  <table id="tabelPesanan" class="w-full text-left border-collapse">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        <th class="p-4 text-xs font-bold uppercase text-slate-500">Pembeli</th>
        <th class="p-4 text-xs font-bold uppercase text-slate-500">Menu</th>
        <th class="p-4 text-xs font-bold uppercase text-slate-500">Alamat</th>
        <th class="p-4 text-xs font-bold uppercase text-slate-500 text-center">Status</th>
        <th class="p-4 text-xs font-bold uppercase text-slate-500 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php while ($row = mysqli_fetch_assoc($all_orders)): ?>
        <?php
        $clean_phone = preg_replace('/[^0-9]/', '', $row['whatsapp']);
        if (substr($clean_phone, 0, 1) === '0') $clean_phone = '62' . substr($clean_phone, 1);

        $format_harga = "Rp " . number_format($row['harga'] * $row['stok'], 0, ',', '.');

        $msg_pending = urlencode(
          "🟡 *KONFIRMASI PESANAN - MENUNGGU PROSES*\n"
            . "------------------------------------------\n"
            . "Halo *{$row['nama_pembeli']}*,\n\n"
            . "Terima kasih telah mempercayai kami! Pesanan Anda telah berhasil kami terima dan saat ini sedang dalam tahap verifikasi. 📦\n\n"
            . "📑 *DETAIL PESANAN ANDA*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🍱 *Menu:* {$row['nama_produk']}\n"
            . "🔢 *Jumlah:* {$row['stok']} porsi\n"
            . "💰 *Total:* {$format_harga}\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"
            . "Tim kami akan segera menghubungi Anda melalui WhatsApp untuk konfirmasi lebih lanjut. Mohon kesabaran Anda ya. Terima kasih! 🙏"
        );

        $msg_setuju = urlencode(
          "✅ *PESANAN DIKONFIRMASI*\n"
            . "------------------------------------------\n"
            . "Halo *{$row['nama_pembeli']}*,\n\n"
            . "Pesanan Anda telah kami konfirmasi dan sedang kami siapkan dengan sepenuh hati. 📦\n\n"
            . "📑 *DETAIL PESANAN*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🍱 *Menu:* {$row['nama_produk']}\n"
            . "🔢 *Jumlah:* {$row['stok']} porsi\n"
            . "💰 *Total:* {$format_harga}\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"
            . "Kami akan segera menginformasikan perkembangan pesanan Anda. Terima kasih atas kepercayaan Anda! 🙏"
        );

        $msg_tolak = urlencode(
          "❌ *PESANAN TIDAK DAPAT DIPROSES*\n"
            . "------------------------------------------\n"
            . "Halo *{$row['nama_pembeli']}*,\n\n"
            . "Kami mohon maaf atas ketidaknyamanan ini. Pesanan Anda untuk:\n"
            . "🍱 *{$row['nama_produk']}*\n\n"
            . "Saat ini *tidak dapat kami proses* dikarenakan stok sedang tidak tersedia. 😔\n"
            . "------------------------------------------\n"
            . "Kami mengundang Anda untuk mengeksplorasi pilihan menu lainnya di katalog kami. Semoga ada yang cocok untuk Anda!"
        );

        $msg_selesai = urlencode(
          "🔵 *PESANAN TELAH SELESAI*\n"
            . "------------------------------------------\n"
            . "Halo *{$row['nama_pembeli']}*,\n\n"
            . "Pesanan Anda untuk:\n"
            . "🍱 *{$row['nama_produk']}*\n\n"
            . "Telah *SELESAI* dan kami harap semuanya berjalan sesuai harapan Anda. 🎉\n"
            . "------------------------------------------\n"
            . "Terima kasih telah berbelanja bersama kami. Kami tunggu kunjungan Anda berikutnya!"
        );

        $msg_batal = urlencode(
          "⚠️ *PESANAN DIBATALKAN*\n"
            . "------------------------------------------\n"
            . "Halo *{$row['nama_pembeli']}*,\n\n"
            . "Kami mengonfirmasi bahwa pesanan Anda untuk:\n"
            . "🍱 *{$row['nama_produk']}*\n\n"
            . "Telah resmi *DIBATALKAN*. 🚫\n"
            . "------------------------------------------\n"
            . "Apabila pembatalan ini bukan atas permintaan Anda, silakan segera hubungi tim kami untuk mendapatkan bantuan. Terima kasih."
        );

        ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="p-4 text-sm">
            <div class="font-bold">
              <?= htmlspecialchars($row['nama_pembeli']) ?>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tighter
            <?= $row['status'] == 'setuju' ? 'bg-green-100 text-green-700'
              : ($row['status'] == 'tolak' ? 'bg-red-100 text-red-700'
                : ($row['status'] == 'selesai' ? 'bg-blue-100 text-blue-500'
                  : ($row['status'] == 'dibatalkan' ? 'bg-gray-100 text-gray-500'
                    : 'bg-amber-100 text-amber-700'))) ?>
            ">
              <?= $row['status'] ?>
            </span>
            <p class="mt-2 text-gray-600 font-bold"><?= $row['whatsapp'] ?></p>
          </td>
          <td class="p-4 text-sm">
            <span class="font-medium"><?= $row['nama_produk'] ?></span> <br>
            <small class="text-slate-400">Order: <?= $row['stok'] ?> | Sisa: <?= $row['stok_saat_ini'] ?></small>
          </td>
          <td class="p-4 text-sm text-slate-600 italic"><?= htmlspecialchars($row['alamat']) ?></td>
          <td class="p-4 text-center whitespace-nowrap">
            <div class="flex flex-col gap-2 max-w-[130px] mx-auto">
              <?php if ($row['status'] == 'pending'): ?>
                <button type="button" onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_pending ?>', '_blank')"
                  class="bg-gray-100 text-gray-500 py-2 rounded-lg text-[10px] font-bold border border-gray-200 hover:bg-emerald-50 hover:text-emerald-600">
                  🟢 HUBUNGI WA</span>
                </button>
                <button type="button" onclick="ubahStatus('setuju', <?= $row['id'] ?>, <?= $row['stok'] ?>, <?= $row['stok_saat_ini'] ?>, '<?= $row['nama_produk'] ?>')"
                  class="bg-emerald-500 text-white py-2 rounded-lg text-[10px] font-black shadow-sm hover:bg-emerald-600">
                  SETUJUI
                </button>
                <button type="button" onclick="ubahStatus('tolak', <?= $row['id'] ?>)"
                  class="bg-white border-2 border-red-500 text-red-500 py-2 rounded-lg text-[10px] font-black hover:bg-red-50">
                  TOLAK
                </button>

              <?php elseif ($row['status'] == 'setuju'): ?>
                <div class="flex flex-col  gap-1">
                  <button type="button" onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_setuju ?>', '_blank')"
                    class="bg-gray-100 text-gray-500 py-2 rounded-lg text-[10px] font-bold border border-gray-200 hover:bg-emerald-50 hover:text-emerald-600">
                    🟢 HUBUNGI WA
                    </span>
                  </button>
                  <button type="button" onclick="confirmSelesai(<?= $row['id'] ?>)"
                    class="bg-emerald-500 text-white py-2 rounded-lg text-[10px] font-black shadow-sm hover:bg-emerald-600">
                    Selesaikan
                  </button>
                  <button type="button" onclick="confirmBatal(<?= $row['id'] ?>)"
                    class="bg-slate-100 text-slate-400 p-2 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm">
                    ✕
                  </button>
                </div>
                <span class="text-[9px] text-green-500 font-bold italic mt-1 uppercase">Setuju</span>

              <?php elseif ($row['status'] == 'selesai'): ?>
                <div class="flex flex-col  gap-1">
                  <button type="button" onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_selesai ?>', '_blank')"
                    class="bg-gray-100 text-gray-500 py-2 rounded-lg text-[10px] font-bold border border-gray-200 hover:bg-emerald-50 hover:text-emerald-600">
                    🟢 HUBUNGI WA
                    </span>
                  </button>
                </div>
                <span class="text-[9px] text-blue-500 font-bold italic mt-1 uppercase">Selesai</span>

              <?php else : ?>
                <button type="button" onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $row['status'] == 'tolak' ? $msg_tolak : $msg_batal ?>', '_blank')"
                  class="bg-gray-100 text-gray-500 py-2 rounded-lg text-[10px] font-bold border border-gray-200 hover:bg-emerald-50 hover:text-emerald-600">
                  🟢 HUBUNGI WA
                </button>
                <span class="text-[9px] text-red-500 font-bold italic mt-1 uppercase"><?= $row['status'] ?></span>
              <?php endif; ?>
            </div>
          </td>
          <td class="p-4 text-center">
            <?php if ($row['status'] == 'selesai'): ?>
              <div class="flex justify-center gap-3">
                <a href="index.php?page=edit_order&id=<?= $row['id'] ?>" class="text-yellow-600 text-sm font-bold">Edit</a>
                <a href="index.php?page=orders&hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus pesanan ini?')" class="text-red-500 hover:text-red-700 font-bold text-sm">Hapus</a>
              </div>
              <!-- <p>
                ———
              </p>
            <php else : ?>
              <div class="flex justify-center gap-3">
                <a href="index.php?page=edit_order&id=<?= $row['id'] ?>" class="text-yellow-600 text-sm font-bold">Edit</a>
                <a href="index.php?page=orders&hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus pesanan ini?')" class="text-red-500 hover:text-red-700 font-bold text-sm">Hapus</a>
              </div> -->

            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>