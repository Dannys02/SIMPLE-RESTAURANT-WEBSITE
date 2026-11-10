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
let currentMaxStok = 100;

function setFieldError(inputEl, errSpanId, message) {
    const span = document.getElementById(errSpanId);
    if (span) {
        span.textContent = message;
        span.classList.remove("hidden");
    }
    if (inputEl) {
        inputEl.classList.add("border-red-600", "focus:border-red-600");
        inputEl.classList.remove("border-stone-200", "focus:border-amber-700");
        inputEl.setAttribute("aria-invalid", "true");
    }
}

function clearFieldError(inputEl, errSpanId) {
    const span = document.getElementById(errSpanId);
    if (span) {
        span.textContent = "";
        span.classList.add("hidden");
    }
    if (inputEl) {
        inputEl.classList.remove("border-red-600", "focus:border-red-600");
        inputEl.classList.add("border-stone-200", "focus:border-amber-700");
        inputEl.removeAttribute("aria-invalid");
    }
}

function clearAllOrderErrors() {
    clearFieldError(document.getElementById("nama_pembeli"), "err_nama_pembeli");
    clearFieldError(document.getElementById("whatsapp_pembeli"), "err_whatsapp_pembeli");
    clearFieldError(document.getElementById("stok_input"), "err_stok_input");
    clearFieldError(document.getElementById("alamat_pembeli"), "err_alamat_pembeli");
    clearFieldError(null, "err_recaptcha");
}

function validateNamaPembeli() {
    const input = document.getElementById("nama_pembeli");
    if (!input) return true;
    const val = input.value.trim();
    if (!val) {
        setFieldError(input, "err_nama_pembeli", "Nama lengkap wajib diisi.");
        return false;
    }
    if (val.length < 3 || val.length > 50) {
        setFieldError(input, "err_nama_pembeli", "Nama pembeli harus terdiri dari 3 hingga 50 karakter.");
        return false;
    }
    clearFieldError(input, "err_nama_pembeli");
    return true;
}

function validateWhatsappPembeli() {
    const input = document.getElementById("whatsapp_pembeli");
    if (!input) return true;
    const val = input.value.trim();
    if (!val) {
        setFieldError(input, "err_whatsapp_pembeli", "Nomor WhatsApp wajib diisi.");
        return false;
    }
    if (!/^[0-9]{10,14}$/.test(val)) {
        setFieldError(input, "err_whatsapp_pembeli", "Nomor WhatsApp harus berupa 10 hingga 14 digit angka (contoh: 081234567890).");
        return false;
    }
    clearFieldError(input, "err_whatsapp_pembeli");
    return true;
}

function validateStokInput() {
    const input = document.getElementById("stok_input");
    if (!input) return true;
    const rawVal = input.value.trim();
    const val = Number(rawVal);
    if (!rawVal || !Number.isInteger(val) || val < 1) {
        setFieldError(input, "err_stok_input", "Jumlah porsi minimal 1.");
        return false;
    }
    if (val > currentMaxStok) {
        setFieldError(input, "err_stok_input", "Jumlah melebihi sisa stok tersedia (" + currentMaxStok + " porsi).");
        return false;
    }
    if (val > 100) {
        setFieldError(input, "err_stok_input", "Jumlah pesanan maksimal 100 porsi per transaksi.");
        return false;
    }
    clearFieldError(input, "err_stok_input");
    return true;
}

function validateAlamatPembeli() {
    const input = document.getElementById("alamat_pembeli");
    if (!input) return true;
    const val = input.value.trim();
    if (!val) {
        setFieldError(input, "err_alamat_pembeli", "Alamat lengkap pengiriman wajib diisi.");
        return false;
    }
    if (val.length < 10 || val.length > 255) {
        setFieldError(input, "err_alamat_pembeli", "Alamat harus terdiri dari 10 hingga 255 karakter beserta patokan lokasi.");
        return false;
    }
    clearFieldError(input, "err_alamat_pembeli");
    return true;
}

function validateRecaptcha() {
    const span = document.getElementById("err_recaptcha");
    if (!span) return true;
    if (typeof grecaptcha !== "undefined" && typeof grecaptcha.getResponse === "function") {
        const response = grecaptcha.getResponse();
        if (!response || response.length === 0) {
            setFieldError(null, "err_recaptcha", "Silakan centang verifikasi keamanan reCAPTCHA terlebih dahulu.");
            return false;
        }
    }
    clearFieldError(null, "err_recaptcha");
    return true;
}

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

    // 3. Simpan batas stok produk
    currentMaxStok = parseInt(product.stok, 10) || 100;
    const stokInput = document.getElementById("stok_input");
    if (stokInput) {
        stokInput.max = currentMaxStok;
        stokInput.value = 1;
    }

    // 4. Update Subtitle di modal (tampilkan nama produk)
    const subTitle = document.getElementById("modalSubTitle");
    if (subTitle) {
        subTitle.innerText = product.nama;
    }

    // 5. Bersihkan pesan error sebelumnya
    clearAllOrderErrors();

    // 6. Munculkan Modal (hapus class hidden)
    orderModal.classList.remove("hidden");
    body.style.overflow = "hidden";  // Lock scroll saat modal terbuka

    // 7. Jalankan hitung total awal
    hitungTotal();
}

/**
 * Fungsi: closeModal()
 * Tujuan: Menutup modal form pemesanan
 */
function closeModal() {
    clearAllOrderErrors();
    orderModal.classList.add("hidden");
    body.style.overflow = "auto";  // Unlock scroll
}

document.addEventListener("DOMContentLoaded", function () {
    const orderForm = document.getElementById("orderForm");
    if (!orderForm) return;

    const namaInput = document.getElementById("nama_pembeli");
    const waInput = document.getElementById("whatsapp_pembeli");
    const stokInput = document.getElementById("stok_input");
    const alamatInput = document.getElementById("alamat_pembeli");

    if (namaInput) namaInput.addEventListener("input", validateNamaPembeli);
    if (waInput) waInput.addEventListener("input", validateWhatsappPembeli);
    if (stokInput) stokInput.addEventListener("input", validateStokInput);
    if (alamatInput) alamatInput.addEventListener("input", validateAlamatPembeli);

    orderForm.addEventListener("submit", function (e) {
        const isNamaValid = validateNamaPembeli();
        const isWaValid = validateWhatsappPembeli();
        const isStokValid = validateStokInput();
        const isAlamatValid = validateAlamatPembeli();
        const isRecaptchaValid = validateRecaptcha();

        if (!isNamaValid || !isWaValid || !isStokValid || !isAlamatValid || !isRecaptchaValid) {
            e.preventDefault();
            if (!isNamaValid && namaInput) {
                namaInput.focus();
            } else if (!isWaValid && waInput) {
                waInput.focus();
            } else if (!isStokValid && stokInput) {
                stokInput.focus();
            } else if (!isAlamatValid && alamatInput) {
                alamatInput.focus();
            }
        }
    });
});

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