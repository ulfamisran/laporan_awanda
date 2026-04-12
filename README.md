# Sistem Pengelolaan Dapur MBG

Aplikasi berbasis **Laravel 11** untuk mengelola dapur MBG: data master, stok barang, keuangan, penggajian, laporan limbah, dan rekap. Fondasi ini mencakup autentikasi ringan, peran (Spatie Permission), layout admin berbahasa Indonesia dengan tema hijau MBG, serta pustaka front-end melalui CDN (Tailwind CSS, Alpine.js, DataTables, SweetAlert2, Notyf, Select2, Flatpickr, Chart.js).

## Persyaratan

- **PHP** 8.2 atau lebih baru (ekstensi: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` untuk Intervention Image)
- **Composer** 2.x
- **MySQL** 8.x (atau MariaDB kompatibel)
- **Node.js** 20+ dan **npm** (untuk aset Vite bawaan Laravel jika Anda menggunakannya bersama CDN)

## Instalasi

1. **Clone / salin proyek** ke folder lokal Anda.

2. **Instal dependensi PHP**

   ```bash
   composer install
   ```

3. **Salin environment**

   ```bash
   copy .env.example .env
   ```

   Pada Linux/macOS: `cp .env.example .env`

4. **Atur kunci aplikasi**

   ```bash
   php artisan key:generate
   ```

5. **Sesuaikan `.env`**

   - `APP_NAME`, `APP_URL`, `APP_TIMEZONE` (default `Asia/Jakarta`)
   - Kredensial database: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

6. **Migrasi & seeder**

   ```bash
   php artisan migrate --seed
   ```

7. **Tautan penyimpanan publik (upload file)**

   ```bash
   php artisan storage:link
   ```

   Perintah ini membuat symlink `public/storage` → `storage/app/public` sesuai konfigurasi bawaan `config/filesystems.php`.

8. **Autoload helper** (setelah `composer install` biasanya sudah otomatis)

   ```bash
   composer dump-autoload
   ```

9. **Jalankan aplikasi**

   ```bash
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000` lalu masuk dengan akun bawaan di bawah.

10. **(Opsional) Aset Vite**

    ```bash
    npm install
    npm run build
    ```

    Layout utama saat ini memuat **Tailwind & skrip lain via CDN**; Vite tetap tersedia untuk modul yang akan Anda tambahkan.

## Paket Composer yang dipasang

| Paket | Kegunaan singkat |
| --- | --- |
| `spatie/laravel-permission` | Peran & izin |
| `yajra/laravel-datatables-oracle` | DataTables server-side |
| `barryvdh/laravel-dompdf` | Ekspor PDF |
| `maatwebsite/excel` | Impor/ekspor Excel |
| `intervention/image` | Olah gambar (terdaftar `ImageManager` GD di `AppServiceProvider`) |

Setelah `composer install`, Anda dapat mempublikasikan konfigurasi paket bila perlu, misalnya:

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

Konfigurasi **Spatie Permission** sudah disediakan di `config/permission.php` bersama migrasi tabel peran.

## Akun bawaan (setelah `migrate --seed`)

| Peran | Email | Kata sandi | Catatan |
| --- | --- | --- | --- |
| Super Admin | `superadmin@mbg.local` | `password` | Akses penuh termasuk menu **Pengaturan** |
| Admin Pusat | `adminpusat@mbg.local` | `password` | Semua modul dapur kecuali pengaturan sistem |
| Admin Dapur | `admin@mbg.local` | `password` | Peran `admin`; `profil_mbg_id` = `1` untuk uji filter dapur |

**Peringatan:** ganti kata sandi dan hapus akun contoh sebelum produksi.

## Middleware peran (`App\Http\Middleware\RoleMiddleware`)

- Terdaftar di `bootstrap/app.php` dengan alias **`dapur.role`** agar tidak bentrok dengan middleware bawaan paket Spatie bernama `role`.
- Penggunaan di rute: `middleware('dapur.role:super_admin,admin_pusat,admin')`.
- Untuk admin dapur murni (bukan super admin / admin pusat), middleware menyematkan `request()->attributes->get('scoped_profil_mbg_id')` agar kueri dapat dibatasi per dapur.

## Helper format

Berkas `app/Helpers/FormatHelper.php` (autoload `files` di `composer.json`):

- `formatRupiah($angka)` → contoh: `Rp 1.000.000`
- `formatTanggal($date)` → `DD/MM/YYYY`
- `generateKode($prefix)` → kode unik transaksi

## Struktur folder (ringkas)

```
app/
  Helpers/FormatHelper.php
  Http/
    Controllers/Auth/LoginController.php
    Middleware/RoleMiddleware.php
  Models/User.php
  Support/SidebarMenu.php
bootstrap/
  app.php
config/
  permission.php
database/
  migrations/...
  seeders/MbgFoundationSeeder.php
resources/
  views/
    auth/login.blade.php
    dashboard.blade.php
    layouts/app.blade.php
    layouts/guest.blade.php
    pages/shell.blade.php
routes/web.php
```

## UI & bahasa

- Teks antarmuka **Bahasa Indonesia**.
- Contoh format uang & tanggal ada di halaman dasbor.
- Notifikasi sesi: `success`, `error`, `warning`, `info` (Notyf).
- Konfirmasi hapus disarankan memakai `window.mbgConfirmDelete({...})` (SweetAlert2).

---

Proyek ini adalah fondasi; modul bisnis (CRUD, laporan, API DataTables, PDF/Excel) dapat ditambahkan bertahap di atas struktur rute dan layout yang sudah disiapkan.
