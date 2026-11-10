# DESIGN.md - RasaNusa (Katalog & Pemesanan Kuliner UMKM)

## Identity & Brand Voice
- **Brand Name:** RasaNusa (ditampilkan sebagai `Rasa` + `Nusa`)
- **Product Type:** Katalog kuliner UMKM & sistem pemesanan online langsung ke dapur.
- **Personality:** Hangat, jujur, editorial kuliner modern, bersih, dan terpercaya.
- **Tone of Copy:** Lugas, menggugah selera tanpa klaim berlebihan, fokus pada bahan segar, resep nusantara, dan kemudahan pesan langsung.

## Color Palette (Max 3 Core + 1 Accent)
- **Core 1 (Warm Surface Base):** `#FAF7F2` (Latar halaman hangat menyerupai kertas menu bersih) & `#FFFFFF` (Permukaan kartu utama).
- **Core 2 (Deep Charcoal / Ink):** `#0F172A` (Slate-900 untuk judul dan kontras utama, rasio > 15:1) & `#475569` (Slate-600 untuk teks sekunder, rasio > 7:1).
- **Core 3 (Warm Stone Border & Muted Surface):** `#F3EDE4` (Latar pemisah seksi) & `#E2E8F0` (Garis pembatas struktural).
- **Deliberate Accent (Terracotta Orange):** `#C2410C` (Orange-700 untuk CTA utama, harga, dan indikator fokus, rasio > 5.1:1 terhadap putih) dengan hover `#9A3412` (Orange-800).

## Typography
- **Display & Headings:** `'Fraunces'` (atau `'Playfair Display'` / Georgia serif fallback) untuk judul utama seksi agar memberi karakter editorial kuliner yang khas dan berbeda dari template SaaS standar.
- **Body & UI Controls:** `'Plus Jakarta Sans'` (sans-serif) untuk keterbacaan tinggi pada deskripsi menu, harga, stok, tombol, dan formulir pemesanan.
- **Scale:** Fluid typography menggunakan `clamp()` agar proporsional dari layar ponsel 360px hingga desktop lebar.

## Liveliness Dials
- **Dial:** `ENERGY 2 / RHYTHM 3 / MOTION 2`
- **ENERGY 2 (Balanced Editorial):** Kontras tipografi serif-sans yang tegas dengan aksen oranye terfokus pada tindakan utama (Pesan & Kirim).
- **RHYTHM 3 (Varied Section Compositions):**
  1. *Hero Section:* Split asimetris 7:5 dengan tipografi editorial di kiri dan bingkai foto hidangan utama di kanan beserta kartu info jam operasional nyata.
  2. *Tentang / Cerita Dapur:* Layout jurnal editorial 5:7 dengan bingkai foto beraksen offset dan daftar fakta nyata dari database (jumlah menu aktif & proses masak).
  3. *Menu Unggulan:* Header seksi rata kiri-kanan (judul di kiri, tautan katalog penuh di kanan) diikuti grid kartu menu terstruktur dengan indikator stok jelas.
  4. *Testimoni Pelanggan:* Layout kutipan editorial berlatar hangat (`#F3EDE4`) jika data testimoni tersedia di database.
  5. *Kontak & Lokasi:* Tata letak 2 kolom terintegrasi antara kartu informasi operasional dapur (panel gelap `#0F172A`) dan formulir pesan WhatsApp langsung.
- **MOTION 2 (Purposeful Scroll-Reveal & Micro-transitions):** Transisi muncul halus saat elemen masuk layar (`from-bottom`) dan efek angkat ringan pada kartu menu, serta sepenuhnya mematuhi `prefers-reduced-motion: reduce`.

## Identity Motif
- Kombinasi judul serif editorial (`Fraunces`) dengan garis aksen pendek oranye (`#C2410C`) dan penomoran seksi bergaya daftar menu (`01 / Katalog`, `02 / Cerita Dapur`, `03 / Menu Pilihan`, `04 / Ulasan`, `05 / Kontak`).
