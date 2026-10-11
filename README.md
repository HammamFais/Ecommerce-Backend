<div align="center">

# ⚙️ PasarLokal — Backend

**RESTful API untuk Platform E-Commerce UMKM Lokal**

[![Railway](https://img.shields.io/badge/Deploy-Railway-6e4fff?logo=railway&logoColor=white)](https://ecommerce-backend-production-12cc.up.railway.app)
[![Laravel](https://img.shields.io/badge/Laravel_11-FF2D20?logo=laravel&logoColor=white)](.)
[![PHP](https://img.shields.io/badge/PHP_8.2+-777BB4?logo=php&logoColor=white)](.)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?logo=postgresql&logoColor=white)](.)

> Tugas UAS Mata Kuliah **Aplikasi Berbasis Web** — D4 Teknik Informatika  
> Politeknik Elektronika Negeri Surabaya (PENS) — Semester 4

</div>

---

## ✨ Fitur API

| Modul | Endpoint | Deskripsi |
|-------|----------|-----------|
| 🔐 Auth | `/api/auth/*` | Register, login, logout, cek profil, edit profil |
| 📦 Produk | `/api/products` | List produk publik, detail, CRUD seller |
| 🛒 Keranjang | `/api/cart` | Tambah, update quantity, hapus, clear |
| 📋 Order | `/api/orders` | Buat order, list, detail, batalkan |
| 💳 Payment | `/api/payment/*` | Buat Snap token Midtrans, webhook notifikasi |
| 🚚 Ongkir | `/api/shipping/*` | List kota (RajaOngkir V2), hitung ongkir |
| 📊 Dashboard | `/api/dashboard/seller` | Statistik penjualan, grafik, stok menipis |
| 🏪 Seller | `/api/seller/*` | Kelola produk & order milik seller |

---

## 🛠️ Stack Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 11 |
| Language | PHP 8.2+ |
| Database | PostgreSQL (Railway) |
| Auth | JWT (tymon/jwt-auth) |
| ORM | Eloquent |
| Payment | Midtrans Snap (Sandbox) |
| Ongkir | RajaOngkir API V2 (rajaongkir.komerce.id) |
| Storage | Railway Volume |
| Deploy | Railway |

---

## 📁 Struktur Proyek

```
pasarlokal-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/        → AuthController, ProductController,
│   │   │                           CartController, OrderController,
│   │   │                           PaymentController, ShippingController,
│   │   │                           DashboardController
│   │   ├── Middleware/         → RoleMiddleware, CORS
│   │   └── Requests/           → Form request validation
│   └── Models/                 → User, Product, Cart, CartItem,
│                                   Order, OrderItem, Payment
├── database/
│   ├── migrations/             → Schema tabel
│   └── seeders/                → Data awal (demo buyer, seller, produk)
├── routes/
│   └── api.php                 → Semua API routes
├── storage/app/public/         → File upload produk
└── .env                        → Environment variables
```

---

## 🚀 Cara Menjalankan (Development Lokal)

### 1. Clone & Install
```bash
git clone https://github.com/HammamFais/Ecommerce-Backend.git
cd Ecommerce-Backend
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Isi `.env` dengan konfigurasi database dan API key:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pasarlokal
DB_USERNAME=postgres
DB_PASSWORD=your_password

MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false

RAJAONGKIR_API_KEY=your_rajaongkir_key
```

### 3. Migrasi & Seed Database
```bash
php artisan migrate
php artisan db:seed
```

### 4. Jalankan Server
```bash
php artisan serve
# Server berjalan di http://localhost:8000
```

---

## 📡 API Endpoints

### Auth (Public)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/auth/register` | Daftar akun baru |
| POST | `/api/auth/login` | Login, dapat JWT token |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/me` | Data user login |
| PUT | `/api/auth/profile` | Update profil |

### Produk (Public)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/products` | List semua produk (dengan filter & pagination) |
| GET | `/api/products/{id}` | Detail produk |

### Seller (Auth + Role Seller)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/seller/products` | List produk milik seller |
| POST | `/api/seller/products` | Tambah produk baru |
| PUT | `/api/seller/products/{id}` | Edit produk |
| DELETE | `/api/seller/products/{id}` | Hapus produk |
| GET | `/api/seller/orders` | List order masuk |
| PUT | `/api/seller/orders/{id}` | Update status order |
| GET | `/api/dashboard/seller` | Data statistik dashboard |

### Buyer (Auth)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET/POST | `/api/cart` | Lihat / tambah item keranjang |
| PUT | `/api/cart/{id}` | Update quantity |
| DELETE | `/api/cart/{id}` | Hapus item |
| POST | `/api/orders` | Buat order baru |
| GET | `/api/orders` | Riwayat order |
| PUT | `/api/orders/{id}/cancel` | Batalkan order |
| POST | `/api/payment/{order_id}` | Buat Snap token Midtrans |

### Public
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/shipping/cities` | List kota untuk ongkir |
| GET | `/api/shipping/cost` | Hitung ongkir |
| POST | `/api/payment/notification` | Webhook Midtrans (no auth) |

---

## 🔑 Demo Akun

| Role | Email | Password |
|------|-------|----------|
| Buyer | buyer@demo.com | password123 |
| Seller | seller@demo.com | password123 |

---

## 👥 Data Kelompok

**Kelompok 2 — Teknologi Aplikasi Web E-Commerce**

| No | NRP | Nama |
|---:|:---:|------|
| 1 | 3124600120 | Revalina Salsabila (Reva) |
| 2 | 3124600110 | Ashlihatul Sotya Mahendra (Yaya) |
| 3 | 3124600112 | Akmal Zaida Fari (Akmal) |
| 4 | 3124600114 | Bagas Wicaksono (Bagas) |
| 5 | 3124600113 | Andika Zafka Ujfaulzi (Dika) |
| 6 | 3124600109 | Haiqal Zakky Indi Febriansyah (Haiqal) |
| 7 | 3124600102 | Dimas Reza Ardhana (Dimas) |
| 8 | 3124600098 | Gusthi Pangestu (Gusthi) |
| 9 | 3124600099 | Yusuf Habibullah Santoso (Habib) |
| 10 | 3124600096 | Hammam Hidayatullah (Hammam) |
| 11 | 3124600093 | Ardra Razaan Syaikhah (Ardra) |
| 12 | 3124600094 | Erik Triayuda Wijaya (Erik) |
| 13 | 3124600101 | Faris Akmal Soehartono (Faris) |
| 14 | 3124600106 | Efrapaska Vincesius Panjaitan (Efra) |

### Pembagian Tugas

| Handler | Anggota |
|---------|---------|
| 📊 PPT (maks 4 orang) | Akmal, Yaya, Bagas, Ardra |
| 💻 Coding BE / Database / API (min 7 orang) | Dimas, Faris, Hammam, Dika, Habib, Gusthi, Haiqal |
| 📝 Laporan / Reference & Plan Writing (maks 3 orang) | Reva, Efra, Erik |

---

<div align="center">

Made with ❤️ for **Aplikasi Berbasis Web** — PENS 2026

</div>
