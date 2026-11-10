/**
 * ============================================
 * SCRIPT JAVASCRIPT - HALAMAN UTAMA & KATALOG
 * ============================================
 * Fungsi-fungsi untuk interaksi UI di halaman depan (index.php, katalog.php, detail.php)
 * Termasuk: Animasi scroll, modal order, perhitungan harga, menu mobile
 */

/**
 * ========== 1. INTERSECTION OBSERVER - SCROLL ANIMATION ==========
 * Fungsi: Menampilkan animasi fade-in ketika elemen masuk viewport
 * Digunakan untuk: Elemen dengan class "from-bottom"
 */
document.addEventListener("DOMContentLoaded", function () {
    const boxes = document.querySelectorAll(".from-bottom");

    const observerOptions = {
        root: null,          // Mengacu pada viewport
        threshold: 0.1       // Elemen terlihat 10% langsung trigger animasi
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Jika terlihat di layar, tambahkan class active (trigger animasi)
                entry.target.classList.add("active");
            } else {
                // Jika tidak terlihat (scrolled away), hapus class active
                // Tujuan: animasi bisa berulang saat scroll kembali
                entry.target.classList.remove("active");
            }
        });
    }, observerOptions);

    // Observer setiap elemen dengan class from-bottom
    boxes.forEach(box => {
        observer.observe(box);
    });
});

/**
 * ========== 2. MODAL ORDER - PEMESANAN PRODUK ==========
 * Fungsi: Membuka & menutup modal form pemesanan
 * Interaksi: Click tombol "Pesan" -> buka modal -> isi form -> submit
 */

const orderModal = document.getElementById("orderModal");
const body = document.body;

/**
 * Fungsi: openModal()
 * Tujuan: Membuka modal form pemesanan dengan data produk
 * 
 * Parameter:
 * - product: Object produk (id, nama, harga, stok)
 */
function openModal(product) {
    // 1. Masukkan ID Produk ke hidden input
    document.getElementById("produk_id").value = product.id;

    // 2. Masukkan Harga ke input hidden (untuk perhitungan harga total)
    document.getElementById("harga_modal").value = product.harga;

    // 3. Update Subtitle di modal (tampilkan nama produk)
    const subTitle = document.getElementById("modalSubTitle");
    if (subTitle) {
        subTitle.innerText = product.nama;
    }

    // 4. RESET Jumlah Beli ke 1 setiap kali membuka modal
    document.getElementById("stok_input").value = 1;

    // 5. Munculkan Modal (hapus class hidden)
    orderModal.classList.remove("hidden");
    body.style.overflow = "hidden";  // Lock scroll saat modal terbuka

    // 6. Jalankan hitung total awal
    hitungTotal();
}

/**
 * Fungsi: closeModal()
 * Tujuan: Menutup modal form pemesanan
 */
function closeModal() {
    orderModal.classList.add("hidden");
    body.style.overflow = "auto";  // Unlock scroll
}

/**
 * Fungsi: hitungTotal()
 * Tujuan: Menghitung total harga berdasarkan harga & jumlah
 * Trigger: Setiap kali jumlah berubah atau modal dibuka
 */
function hitungTotal() {
    const harga = parseFloat(document.getElementById("harga_modal").value) || 0;
    const jumlah = parseInt(document.getElementById("stok_input").value) || 0;
    const total = harga * jumlah;

    // Format harga dengan Intl API (format Indonesia)
    const formattedTotal = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).format(total);

    // Tampilkan hasil di elemen #total_harga
    document.getElementById("total_harga").innerText = formattedTotal;
}

/**
 * ========== 3. KEYBOARD EVENT - CLOSE MODAL ==========
 * Fungsi: Menutup modal saat user menekan tombol Escape
 */
window.addEventListener("keydown", e => {
    if (e.key === "Escape") {
        if (orderModal && !orderModal.classList.contains("hidden")) {
            closeModal();
        }
        const mobileMenu = document.getElementById("mobileMenu");
        if (mobileMenu && !mobileMenu.classList.contains("hidden")) {
            toggleMobileMenu();
        }
    }
});

/**
 * ========== 4. MOBILE MENU TOGGLE ==========
 * Fungsi: Menampilkan/menyembunyikan menu mobile
 * Trigger: Click tombol hamburger (≡)
 */
function toggleMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    const sheet = document.getElementById("mobileSheet");
    const btn = document.getElementById("mobileMenuBtn");
    const body = document.body;

    if (menu.classList.contains("hidden")) {
        // ===== TAMPILKAN MENU =====
        menu.classList.remove("hidden");
        if (btn) btn.setAttribute("aria-expanded", "true");

        // Slide menu dari bawah dengan delay kecil
        setTimeout(() => {
            sheet.style.bottom = "0";
        }, 10);

        body.style.overflow = "hidden";  // Lock scroll saat menu terbuka
    } else {
        // ===== SEMBUNYIKAN MENU =====
        sheet.style.bottom = "-100%";
        if (btn) btn.setAttribute("aria-expanded", "false");

        // Tunggu animasi slide selesai sebelum remove element
        setTimeout(() => {
            menu.classList.add("hidden");
        }, 400);

        body.style.overflow = "";  // Unlock scroll
    }
}