# CLAUDE.md — PasarLokal Backend

## Tentang Project
PasarLokal adalah platform e-commerce marketplace berbasis web yang menghubungkan UMKM lokal dengan pembeli. Project ini merupakan tugas kuliah Aplikasi Berbasis Web di Politeknik Elektronika Negeri Surabaya (PENS), Semester 4, D4 Teknik Informatika.

---

## Stack Teknologi

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel 11 |
| Database | PostgreSQL (via pgAdmin) |
| Auth | JWT (tymon/jwt-auth) |
| Payment | Midtrans Sandbox |
| Logistik | RajaOngkir API |
| Hosting | Railway (gratis) |
| Frontend (repo terpisah) | HTML + Tailwind CSS + Vanilla JS |

---

## Struktur Repo
Project ini terdiri dari 2 repo terpisah:
- `pasarlokal-backend` → repo ini (Laravel)
- `pasarlokal-frontend` → HTML/Tailwind/JS (repo terpisah)

---

## Struktur Folder Laravel

```
pasarlokal-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── PaymentController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── Payment.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php   ← semua route ada di sini
└── .env
```

---

## Role User
Ada 3 role dalam sistem:
- `buyer` → pembeli, bisa browse produk, beli, lihat riwayat order
- `seller` → penjual/UMKM, bisa kelola produk & lihat order masuk
- `admin` → kelola semua user, produk, dan transaksi

---

## Database Schema

### users
```
id, name, email, password, role (buyer/seller/admin),
phone, address, city, province, created_at, updated_at
```

### products
```
id, seller_id (FK users), name, description, price,
stock, category, image_url, is_active, created_at, updated_at
```

### carts
```
id, buyer_id (FK users), product_id (FK products),
quantity, created_at, updated_at
```

### orders
```
id, buyer_id (FK users), seller_id (FK users),
total_price, status (pending/paid/shipped/done/cancelled),
shipping_address, shipping_city, shipping_cost,
courier, created_at, updated_at
```

### order_items
```
id, order_id (FK orders), product_id (FK products),
quantity, price, created_at, updated_at
```

### payments
```
id, order_id (FK orders), midtrans_order_id,
midtrans_token, status (pending/success/failed),
payment_method, paid_at, created_at, updated_at
```

---

## API Endpoints

### Auth
```
POST /api/auth/register     → daftar akun baru
POST /api/auth/login        → login, return JWT token
POST /api/auth/logout       → logout (invalidate token)
GET  /api/auth/me           → get data user yang sedang login
```

### Products
```
GET    /api/products              → list semua produk aktif (public)
GET    /api/products/{id}         → detail produk (public)
GET    /api/products?category=x   → filter by kategori (public)
POST   /api/seller/products       → tambah produk (seller only)
PUT    /api/seller/products/{id}  → edit produk (seller only)
DELETE /api/seller/products/{id}  → hapus produk (seller only)
GET    /api/seller/products       → list produk milik seller ini
```

### Cart
```
GET    /api/cart              → lihat isi keranjang
POST   /api/cart              → tambah produk ke keranjang
PUT    /api/cart/{id}         → update quantity
DELETE /api/cart/{id}         → hapus item dari keranjang
DELETE /api/cart              → kosongkan keranjang
```

### Orders
```
POST /api/orders              → buat order dari cart
GET  /api/orders              → list order milik buyer
GET  /api/orders/{id}         → detail order
GET  /api/seller/orders       → list order masuk ke seller
PUT  /api/seller/orders/{id}  → update status order (seller)
```

### Payment
```
POST /api/payment/{order_id}         → generate Midtrans token
POST /api/payment/notification        → webhook Midtrans (public, no auth)
GET  /api/payment/status/{order_id}  → cek status pembayaran
```

### Logistik
```
GET /api/shipping/cost   → hitung ongkir via RajaOngkir
                           params: origin, destination, weight, courier
```

### Dashboard Seller
```
GET /api/dashboard/seller   → total penjualan, order masuk, stok menipis
```

---

## Aturan Coding

### Umum
- Semua response API wajib format JSON
- Gunakan format response yang konsisten:
```json
{
  "success": true,
  "message": "Berhasil",
  "data": { ... }
}
```
- Untuk error:
```json
{
  "success": false,
  "message": "Pesan error",
  "errors": { ... }
}
```

### Auth
- Gunakan package `tymon/jwt-auth` untuk JWT
- Semua endpoint kecuali login, register, list produk public, dan webhook Midtrans wajib pakai middleware `auth:api`
- Role check menggunakan middleware custom `CheckRole`

### Database
- Selalu gunakan migration, jangan edit tabel manual di pgAdmin
- Gunakan Eloquent ORM, hindari raw query kecuali terpaksa
- Setiap relasi wajib didefinisikan di Model

### Validasi
- Semua input wajib divalidasi menggunakan Laravel FormRequest
- Pesan error validasi dalam Bahasa Indonesia

### Keamanan
- Jangan pernah expose API key Midtrans atau RajaOngkir di response
- Simpan semua API key di `.env`
- Password wajib di-hash dengan bcrypt (sudah default Laravel)

---

## Environment Variables (.env)

```env
APP_NAME=PasarLokal
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pasarlokal
DB_USERNAME=postgres
DB_PASSWORD=your_password

JWT_SECRET=   ← generate dengan: php artisan jwt:secret

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

RAJAONGKIR_API_KEY=
RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter
```

---

## Cara Jalankan Project

```bash
# Install dependencies
composer install

# Copy env
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Jalankan migrasi + seeder
php artisan migrate --seed

# Jalankan server
php artisan serve
```

---

## Catatan Penting
- Project ini untuk keperluan akademik / demo presentasi
- Midtrans menggunakan mode **sandbox** (bukan production)
- RajaOngkir menggunakan tier **starter** (gratis)
- Frontend ada di repo terpisah `pasarlokal-frontend`, berkomunikasi dengan backend via REST API
- CORS sudah dikonfigurasi agar frontend bisa akses backend
