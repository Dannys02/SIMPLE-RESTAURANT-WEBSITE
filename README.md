# RasaNusa - Indonesian Restaurant Catalog & Online Ordering System

A lightweight, production-ready web application designed for Indonesian culinary micro, small, and medium enterprises (MSMEs). RasaNusa provides an online menu catalog, a direct-to-WhatsApp and database-backed ordering pipeline, and an administrative control panel for business operations.

Read this document in another language:
* [Bahasa Indonesia](README.id.md)

---

## 1. Problem Statement & Solution

### The Problem
Traditional Indonesian food stalls and home-kitchen businesses often rely entirely on manual messaging via social media or chat applications. This workflow leads to:
* High friction in sharing updated menus, prices, and stock levels.
* Miscalculated totals and manual transaction errors.
* Order clutter, missed incoming requests, and slow response times.
* Exposure to automated web spam, spam submissions, and bot floods.

### The Solution
RasaNusa solves these challenges through a centralized digital storefront:
* **Real-time Catalog**: Customers view active dishes, descriptions, prices, and instant stock indicators.
* **Transactional Ordering**: In-modal order form with inline validation, automatic subtotal calculation, server-enforced pricing, and stock decrement inside atomic transactions.
* **Dual Notification Channel**: Immediate customer forwarding to the business WhatsApp account, paired with non-blocking Telegram notifications to administrators.
* **Security & Bot Defense**: Google reCAPTCHA v2 verification, hidden honeypots, session-based rate limiting, input control sanitization, and parameterized prepared statements.
* **Responsive Admin Panel**: Clean sidebar navigation for managing menu items, tracking order statuses, curating testimonials, and updating business contact details without editing code.

---

## 2. Core Features

### Public Customer Interface
* **Landing Page (`index.php`)**: Editorial brand presentation, operational schedule, featured menus, customer testimonials, and quick inquiry form.
* **Full Catalog (`katalog.php`)**: Complete menu listing with real-time text search and stock badges.
* **Menu Detail (`detail.php`)**: Dedicated item breakdown with direct order trigger.
* **Order Modal (`orderModal`)**: Consistent across all customer pages with field-specific inline error notices (`<span>`), client-side input masking, and reCAPTCHA.
* **Quick Contact (`#contactForm`)**: Fast inquiry form routing cleanly to WhatsApp with built-in client debounce and bot traps.

### Administrative Management Panel (`/admin`)
* **Secure Authentication (`admin/login.php`)**: Password-hashed session access with brute-force delay.
* **Overview Dashboard (`admin/index.php`)**: Real-time sales statistics, active menu counts, and operational summaries.
* **Menu Management (`admin/menu.php`)**: Full CRUD for dishes including image upload and stock control.
* **Order Tracking (`admin/orders.php`)**: Searchable data table with status lifecycle toggles (`pending`, `setuju`, `selesai`, `tolak`, `dibatalkan`).
* **Testimonials (`admin/testimoni.php`)**: Customer review curation and display toggle.
* **Store Settings (`admin/pengaturan.php`)**: Live configuration for recipient WhatsApp number, physical kitchen address, contact email, and admin credentials.

---

## 3. Technology Stack

* **Backend**: PHP 7.4+ (Native with MySQLi, prepared statements, and transactional queries)
* **Database**: MySQL 5.7+ / MariaDB 10.3+
* **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript (ES6+), jQuery, DataTables
* **Fonts**: Fraunces (Editorial Serif) and Plus Jakarta Sans (Geometric Sans)
* **Security & Integrasi**: Google reCAPTCHA, WhatsApp Click-to-Chat API, Telegram Bot API

---

## 4. Installation & Local Setup

### Prerequisites
* PHP 7.4 or higher
* MySQL / MariaDB Server
* Apache Web Server (or Laragon / XAMPP / Native PHP CLI)
* Composer (optional, for environments using dependency packages)

### Step 1: Clone Repository
```bash
git clone https://github.com/dannysmarthaf/SIMPLE-RESTAURANT-WEBSITE.git
cd SIMPLE-RESTAURANT-WEBSITE
```

### Step 2: Database Setup
1. Start your MySQL server.
2. Open phpMyAdmin or your MySQL CLI client.
3. Import the provided schema:
```sql
SOURCE database.sql;
```
This initializes the `penjualan_umkm` database with pre-configured tables, admin credentials, default menus, and contact configurations.

### Step 3: Environment Configuration
Configuration is located in `config/db.php`. It automatically adapts to your local host path via `base_url()`. You can customize credentials directly or define environment variables:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `DB_HOST` | `127.0.0.1` | Database server address |
| `DB_USER` | `root` | Database username |
| `DB_PASS` | `""` (empty) | Database password |
| `DB_NAME` | `penjualan_umkm` | Target database name |
| `RECAPTCHA_SITE_KEY` | *(Built-in Test Key)* | Google reCAPTCHA v2 Site Key |
| `RECAPTCHA_SECRET_KEY`| *(Built-in Test Key)* | Google reCAPTCHA v2 Secret Key |
| `TELEGRAM_BOT_TOKEN` | *(Pre-configured)* | Telegram Bot Token for order alerts |
| `TELEGRAM_CHAT_ID` | *(Pre-configured)* | Telegram destination Chat ID |

### Step 4: Run the Application

#### Option A: Using Laragon / XAMPP
1. Move the project folder into your server document directory (`C:\laragon\www\` or `C:\xampp\htdocs\`).
2. Start Apache and MySQL services.
3. Access the application in your browser:
   * Public Storefront: `http://localhost/SIMPLE-RESTAURANT-WEBSITE/`
   * Admin Control Panel: `http://localhost/SIMPLE-RESTAURANT-WEBSITE/admin/`

#### Option B: Using PHP Built-in Server
Run the following command from the project root:
```bash
php -S localhost:8000
```
Open `http://localhost:8000` in your web browser.

---

## 5. Usage Guide

### Customer Flow (Ordering Food)
1. Navigate to the storefront and explore available culinary items on the home page or catalog (`/katalog.php`).
2. Click **Pesan** on any item with available stock.
3. Complete the modal form: enter buyer name, WhatsApp number, requested portion count, and full shipping address.
4. Solve the Google reCAPTCHA challenge.
5. Click **Konfirmasi & Kirim Pesanan**.
6. The system records the transaction, decrements inventory, and redirects the buyer to WhatsApp with a structured pre-filled order draft.

### Admin Flow (Managing Store & Orders)
1. Open `/admin/` and authenticate using the default credentials:
   * **Username**: `admin`
   * **Password**: `password`
2. **Reviewing Orders**: Navigate to **Kelola Pesanan** to verify incoming requests, change delivery statuses, or filter history.
3. **Menu Maintenance**: Go to **Kelola Menu** to adjust pricing, re-stock portions, or upload new dish images.
4. **Customizing Store Information**: Access **Pengaturan** to update your business WhatsApp number, kitchen address, or change your administrator password.

---

## 6. Directory Structure

```text
SIMPLE-RESTAURANT-WEBSITE/
├── admin/                 # Admin panel modules (dashboard, menu, orders, settings)
├── api/                   # Server endpoints (create_order, check_orders, send_notif)
├── config/                # Database connection & global system helpers (db.php)
├── src/
│   ├── css/               # Custom stylesheets & Tailwind builds
│   ├── img/               # Stored dish photography and assets
│   └── js/                # Client-side scripts (index.js for forms, animations, modals)
├── database.sql           # Complete relational database dump
├── detail.php             # Individual dish detail view
├── error.php              # Centralized user-friendly HTTP error page
├── index.php              # Brand landing page & public presentation
├── katalog.php            # Searchable product catalog
├── README.md              # Documentation (English)
└── README.id.md           # Documentation (Bahasa Indonesia)
```

---

## 7. License

Distributed for educational, personal, and small enterprise deployment purposes. Developed by **Dannys Martha Favrillia**.
