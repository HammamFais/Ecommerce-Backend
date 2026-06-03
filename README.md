# PasarLokal — Backend (Proyek E‑Commerce)

Deskripsi singkat proyek backend PasarLokal untuk tugas Mata Kuliah Aplikasi Berbasis Web. Ini adalah RESTful API backend yang dibangun dengan Laravel 11, menyediakan endpoint untuk manajemen produk, user authentication, cart, order, payment, dan seller dashboard.

**Teknologi:**
- **Framework:** Laravel 11
- **Language:** PHP 8.2+
- **Database:** MySQL/MariaDB
- **Authentication:** JWT (JSON Web Tokens)
- **API Documentation:** REST API
- **ORM:** Eloquent
- **Testing:** PHPUnit
- **Containerization:** Docker & Docker Compose
- **Payment Gateway:** Midtrans

**Cara Menjalankan (Development):**

1. **Clone dan Setup:**
   ```bash
   # Install dependencies
   composer install
   npm install

   # Copy env file
   cp .env.example .env

   # Generate app key
   php artisan key:generate
   ```

2. **Database Setup:**
   ```bash
   # Run migrations
   php artisan migrate

   # (Optional) Seed database
   php artisan db:seed
   ```

3. **Jalankan Server:**
   ```bash
   # Menggunakan artisan (local)
   php artisan serve

   # ATAU menggunakan Docker Compose
   docker-compose up -d
   ```
   Server akan berjalan di `http://localhost:8000`

4. **Generate JWT Secret:**
   ```bash
   php artisan jwt:secret
   ```

**Deployment ke Railway:**

Repo ini disiapkan untuk deploy ke host PHP seperti Railway, bukan Netlify.

1. Buat service baru dari repo ini di Railway dengan build memakai `Dockerfile`.
2. Set environment variables produksi, minimal:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://domain-anda`
   - `APP_KEY=base64:...`
   - `DB_CONNECTION=mysql` atau `pgsql`
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
3. Jika ingin migrasi otomatis saat container start, set `RUN_MIGRATIONS_ON_STARTUP=true`.
4. Seeder tidak dijalankan otomatis. Aktifkan hanya jika memang diperlukan dengan `RUN_SEED_ON_STARTUP=true`.

**Struktur Proyek (ringkasan):**
- **`app/Http/Controllers/`**: Semua API controller (AuthController, ProductController, CartController, OrderController, dll)
- **`app/Http/Requests/`**: Form request validation
- **`app/Http/Middleware/`**: Custom middleware (auth check, CORS, dll)
- **`app/Models/`**: Database models (User, Product, Cart, Order, OrderItem, Payment)
- **`database/migrations/`**: Database schema migrations
- **`database/seeders/`**: Database seeder untuk testing
- **`routes/api.php`**: Semua API routes dan endpoints
- **`config/`**: Konfigurasi aplikasi (auth, database, cors, jwt, dll)
- **`storage/`**: File upload storage dan logs
- **`.env`**: Environment variables (database, API keys, dll)

**API Endpoints (Overview):**
- **Auth:** POST `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`
- **Products:** GET `/api/products`, POST `/api/products` (seller)
- **Cart:** GET/POST/DELETE `/api/cart`
- **Orders:** GET/POST `/api/orders`, GET `/api/orders/{id}`
- **Payments:** POST `/api/payments`, webhook untuk Midtrans

**Data Kelompok**
- **Kelompok 2 — Teknologi Aplikasi Web E-Commerce**

|   No |    NRP     | Nama                                   |
| ---: | :--------: | :------------------------------------- |
|    1 | 3124600120 | Revalina Salsabila (Reva)              |
|    2 | 3124600110 | Ashlihatul Sotya Mahendra (Yaya)       |
|    3 | 3124600112 | Akmal Zaida Fari (Akmal)               |
|    4 | 3124600114 | Bagas Wicaksono (Bagas)                |
|    5 | 3124600113 | Andika Zafka Ujfaulzi (Dika)           |
|    6 | 3124600109 | Haiqal Zakky Indi Febriansyah (Haiqal) |
|    7 | 3124600102 | Dimas Reza Ardhana (Dimas)             |
|    8 | 3124600098 | Gusthi Pangestu (Gusthi)               |
|    9 | 3124600099 | Yusuf Habibullah Santoso (Habib)       |
|   10 | 3124600096 | Hammam Hidayatullah (Hammam)           |
|   11 | 3124600093 | Ardra Razaan Syaikhah (Ardra)          |
|   12 | 3124600094 | Erik Triayuda Wijaya (Erik)            |
|   13 | 3124600101 | Faris Akmal Soehartono (Faris)         |
|   14 | 3124600106 | Efrapaska Vincesius Panjaitan (Efra)   |

**Pembagian Handler Tugas**

- **Handler PPT (max 4 orang)**
	- Akmal, Yaya, Bagas, Ardra

- **Handler Coding (BE, Database, API) (min 7 orang)**
	- Dimas, Faris, Hammam, Dika, Habib, Gusthi, Haiqal

- **Handler Laporan (Reference & Plan Writing) (max 3 orang)**
	- Reva, Efra, Erik