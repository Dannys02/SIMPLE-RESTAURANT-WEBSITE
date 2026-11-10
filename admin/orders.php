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
  $data = $res_cek ? mysqli_fetch_assoc($res_cek) : null;

  if ($data) {
    $qty_pesan = (int)$data['qty_pesan'];
    $stok_sekarang = (int)$data['stok_sekarang'];
    $pid = (int)$data['produk_id'];
    $status_saat_ini = $data['status'];

    if ($action === 'setuju' && $status_saat_ini === 'pending') {
      if ($stok_sekarang >= $qty_pesan) {
        mysqli_begin_transaction($koneksi);
        $ok1 = mysqli_query($koneksi, "UPDATE produk SET stok = stok - $qty_pesan WHERE id = $pid AND stok >= $qty_pesan");
        $affected = mysqli_affected_rows($koneksi);
        $ok2 = ($ok1 && $affected > 0) ? mysqli_query($koneksi, "UPDATE pesanan SET status = 'setuju' WHERE id = $id AND status = 'pending'") : false;

        if ($ok1 && $ok2 && $affected > 0) {
          mysqli_commit($koneksi);
        } else {
          mysqli_rollback($koneksi);
          echo "<script>alert('Gagal! Stok menu tidak mencukupi atau pesanan sudah diproses.'); window.location.href='index.php?page=orders';</script>";
          exit;
        }
      } else {
        echo "<script>alert('Gagal! Stok menu tidak mencukupi.'); window.location.href='index.php?page=orders';</script>";
        exit;
      }
    } elseif ($action === 'selesai' && $status_saat_ini === 'setuju') {
      mysqli_query($koneksi, "UPDATE pesanan SET status = 'selesai' WHERE id = $id AND status = 'setuju'");
    } elseif ($action === 'tolak' && $status_saat_ini === 'pending') {
      mysqli_query($koneksi, "UPDATE pesanan SET status = 'tolak' WHERE id = $id AND status = 'pending'");
    } elseif ($action === 'dibatalkan' && $status_saat_ini === 'setuju') {
      mysqli_begin_transaction($koneksi);
      $ok1 = mysqli_query($koneksi, "UPDATE pesanan SET status = 'dibatalkan' WHERE id = $id AND status = 'setuju'");
      $affected = mysqli_affected_rows($koneksi);
      $ok2 = ($ok1 && $affected > 0) ? mysqli_query($koneksi, "UPDATE produk SET stok = stok + $qty_pesan WHERE id = $pid") : false;

      if ($ok1 && $ok2) {
        mysqli_commit($koneksi);
      } else {
        mysqli_rollback($koneksi);
      }
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

<!-- Header Halaman Pesanan -->
<header class="flex flex-col sm:flex-row sm:justify-between sm:items-end border-b border-stone-200 pb-6 mb-8 gap-4">
  <div>
    <h1 class="font-serif-editorial text-2xl sm:text-3xl font-bold text-slate-900">Daftar Pesanan Masuk</h1>
    <p class="text-sm text-slate-600 mt-1">
      Verifikasi pesanan baru, kurangi stok secara otomatis saat disetujui, dan kirim konfirmasi via WhatsApp.
    </p>
  </div>

  <a
    href="cetak_laporan.php"
    target="_blank"
    class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shrink-0 w-fit"
  >
    Cetak Laporan Penjualan
  </a>
</header>

<section aria-label="Tabel Pesanan Pelanggan" class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table id="tabelPesanan" class="w-full text-left border-collapse">
      <thead class="bg-[#F3EDE4] border-b border-stone-200">
        <tr>
          <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Pembeli &amp; Status</th>
          <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Hidangan</th>
          <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700">Alamat Pengiriman</th>
          <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700 text-center">Proses Status</th>
          <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-700 text-center">Tindakan</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-200">
        <?php while ($row = mysqli_fetch_assoc($all_orders)): ?>
          <?php
          $clean_phone = preg_replace('/[^0-9]/', '', $row['whatsapp']);
          if (substr($clean_phone, 0, 1) === '0') {
            $clean_phone = '62' . substr($clean_phone, 1);
          }

          $subtotal_harga = (int)$row['harga'] * (int)$row['stok'];
          $format_harga = "Rp " . number_format($subtotal_harga, 0, ',', '.');

          $msg_pending = urlencode(
            "*KONFIRMASI PESANAN - MENUNGGU PROSES*\n"
              . "------------------------------------------\n"
              . "Halo *{$row['nama_pembeli']}*,\n\n"
              . "Terima kasih telah memesan di RasaNusa. Pesanan Anda telah kami terima dan sedang dalam tahap verifikasi:\n\n"
              . "*Menu:* {$row['nama_produk']}\n"
              . "*Jumlah:* {$row['stok']} porsi\n"
              . "*Total:* {$format_harga}\n"
              . "------------------------------------------\n"
              . "Kami akan segera mengabarkan status pesanan Anda. Terima kasih!"
          );

          $msg_setuju = urlencode(
            "*PESANAN DIKONFIRMASI - SEDANG DISIAPKAN*\n"
              . "------------------------------------------\n"
              . "Halo *{$row['nama_pembeli']}*,\n\n"
              . "Pesanan Anda telah kami setujui dan sedang disiapkan oleh dapur RasaNusa:\n\n"
              . "*Menu:* {$row['nama_produk']}\n"
              . "*Jumlah:* {$row['stok']} porsi\n"
              . "*Total:* {$format_harga}\n"
              . "------------------------------------------\n"
              . "Mohon ditunggu ya. Terima kasih!"
          );

          $msg_tolak = urlencode(
            "*INFORMASI PESANAN RASANUSA*\n"
              . "------------------------------------------\n"
              . "Halo *{$row['nama_pembeli']}*,\n\n"
              . "Mohon maaf, pesanan Anda untuk menu *{$row['nama_produk']}* saat ini belum dapat kami proses karena keterbatasan stok porsi.\n"
              . "------------------------------------------\n"
              . "Silakan melihat pilihan menu lainnya di katalog kami. Terima kasih atas pengertiannya."
          );

          $msg_selesai = urlencode(
            "*PESANAN SELESAI*\n"
              . "------------------------------------------\n"
              . "Halo *{$row['nama_pembeli']}*,\n\n"
              . "Pesanan Anda untuk menu *{$row['nama_produk']}* ({$row['stok']} porsi) telah selesai. Selamat menikmati hidangan RasaNusa dan terima kasih telah memesan!"
          );

          $msg_batal = urlencode(
            "*PEMBATALAN PESANAN RASANUSA*\n"
              . "------------------------------------------\n"
              . "Halo *{$row['nama_pembeli']}*,\n\n"
              . "Pesanan Anda untuk menu *{$row['nama_produk']}* telah dibatalkan. Jika ada pertanyaan lebih lanjut, silakan balas pesan ini. Terima kasih."
          );

          $status_badge_class = 'bg-amber-50 text-amber-800 border-amber-200';
          if ($row['status'] === 'setuju') {
            $status_badge_class = 'bg-emerald-50 text-emerald-800 border-emerald-200';
          } elseif ($row['status'] === 'selesai') {
            $status_badge_class = 'bg-blue-50 text-blue-800 border-blue-200';
          } elseif ($row['status'] === 'tolak') {
            $status_badge_class = 'bg-red-50 text-red-800 border-red-200';
          } elseif ($row['status'] === 'dibatalkan') {
            $status_badge_class = 'bg-stone-100 text-slate-700 border-stone-300';
          }
          ?>
          <tr class="hover:bg-[#FAF7F2] transition-colors align-top">
            <td class="p-4 text-sm">
              <div class="font-bold text-slate-900">
                <?= htmlspecialchars($row['nama_pembeli'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div class="text-xs font-semibold text-slate-600 mt-1">
                WA: <?= htmlspecialchars($row['whatsapp'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <span class="inline-block mt-2 px-2.5 py-0.5 rounded border text-[11px] font-bold uppercase tracking-wider <?= $status_badge_class ?>">
                Status: <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            </td>

            <td class="p-4 text-sm">
              <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_produk'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-xs font-semibold text-orange-700 mt-1">
                <?= (int)$row['stok'] ?> Porsi &bull; <?= $format_harga ?>
              </div>
              <div class="text-xs text-slate-600 mt-1">
                Sisa stok dapur: <strong><?= (int)$row['stok_saat_ini'] ?> porsi</strong>
              </div>
            </td>

            <td class="p-4 text-sm text-slate-700 max-w-xs leading-relaxed">
              <?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8') ?>
            </td>

            <td class="p-4 text-center whitespace-nowrap">
              <div class="flex flex-col gap-2 max-w-[155px] mx-auto">
                <?php if ($row['status'] === 'pending'): ?>
                  <button
                    type="button"
                    onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_pending ?>', '_blank')"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-800 text-xs font-bold border border-stone-300 transition-colors"
                  >
                    Hubungi WA
                  </button>
                  <button
                    type="button"
                    onclick="ubahStatus('setuju', <?= (int)$row['id'] ?>, <?= (int)$row['stok'] ?>, <?= (int)$row['stok_saat_ini'] ?>, <?= htmlspecialchars(json_encode($row['nama_produk']), ENT_QUOTES, 'UTF-8') ?>)"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition-colors"
                  >
                    Setujui Pesanan
                  </button>
                  <button
                    type="button"
                    onclick="ubahStatus('tolak', <?= (int)$row['id'] ?>)"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-red-700 border border-red-300 text-xs font-bold transition-colors"
                  >
                    Tolak
                  </button>

                <?php elseif ($row['status'] === 'setuju'): ?>
                  <button
                    type="button"
                    onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_setuju ?>', '_blank')"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-800 text-xs font-bold border border-stone-300 transition-colors"
                  >
                    Hubungi WA
                  </button>
                  <button
                    type="button"
                    onclick="confirmSelesai(<?= (int)$row['id'] ?>)"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-orange-700 hover:bg-orange-800 text-white text-xs font-bold transition-colors"
                  >
                    Tandai Selesai
                  </button>
                  <button
                    type="button"
                    onclick="confirmBatal(<?= (int)$row['id'] ?>)"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-red-700 border border-red-300 text-xs font-bold transition-colors"
                  >
                    Batalkan
                  </button>

                <?php elseif ($row['status'] === 'selesai'): ?>
                  <button
                    type="button"
                    onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $msg_selesai ?>', '_blank')"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-800 text-xs font-bold border border-stone-300 transition-colors"
                  >
                    Hubungi WA
                  </button>

                <?php else: ?>
                  <button
                    type="button"
                    onclick="window.open('https://api.whatsapp.com/send?phone=<?= $clean_phone ?>&text=<?= $row['status'] === 'tolak' ? $msg_tolak : $msg_batal ?>', '_blank')"
                    class="min-h-[38px] px-3 py-1.5 rounded-lg bg-[#FAF7F2] hover:bg-stone-200/70 text-slate-800 text-xs font-bold border border-stone-300 transition-colors"
                  >
                    Hubungi WA
                  </button>
                <?php endif; ?>
              </div>
            </td>

            <td class="p-4 text-center whitespace-nowrap">
              <div class="inline-flex items-center gap-2">
                <a
                  href="index.php?page=edit_order&id=<?= (int)$row['id'] ?>"
                  class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-slate-800 text-xs font-bold transition-colors"
                >
                  Edit
                </a>
                <a
                  href="index.php?page=orders&hapus=<?= (int)$row['id'] ?>"
                  onclick="return confirm('Hapus riwayat pesanan ini?')"
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
