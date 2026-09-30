# SIMA-SEKOLAH

Sistem Informasi Manajemen Aset Sekolah & BHP. Inventarisasi aset tetap KIB A–E + barang habis pakai + peminjaman, per gedung/ruangan.

## Fitur

- Dashboard: total aset, total nilai, dipinjam, stok menipis, 5 peminjaman terbuka, 5 stok rendah.
- Auth + RBAC (`spatie/laravel-permission`): `superadmin`, `pengelola_aset`, `guru_staf`.
- Gedung (`buildings`) + Ruangan (`locations.building_id`, FK `nullOnDelete`) + PIC (`locations.pic_user_id`). Index pakai DataTables client-side (search/sort/paging).
- Aset Masuk: Tahun Perolehan (simpan `YYYY-01-01`), Jenis KIB A–E, Kode unik, Nama, Harga, Sumber Dana (`BOS,DAK,HIBAH,Komite`), Lokasi opsional tampil `Nama (Gedung)`. Bungkus `DB::transaction`.
- Placeholder: Aset Keluar, BHP Masuk, BHP Keluar.
- Master: `assets` + child `kib_a_lands`, `kib_b_equipments`, `kib_c_buildings`, `kib_d_networks`, `kib_e_others`; `bhp_items` + `bhp_transactions` (stok nambah otomatis di model `booted`); `asset_loans`; `school_profiles`; `users`.
- Users CRUD khusus `role:superadmin`.
- UI: Blade + Tailwind CDN + Font Awesome 6.5.1 + Alpine dropdown. Layout `layouts/app.blade.php` header `bg-blue-900`.

## Stack

- Laravel 12.69.3, PHP 8.2.12, MySQL `asetsekolah` (XAMPP, user `root`, password kosong).
- `spatie/laravel-permission ^6.25`. Frontend CDN: `cdn.tailwindcss.com`, Alpine 3, jQuery 3.7.1 + DataTables 1.13.8 (`i18n/id.json`). Tanpa build npm untuk DataTables.

## Struktur DB inti

- `buildings`: `id, code UNIQUE, name, description, timestamps`.
- `locations`: `id, code UNIQUE, name, building_id FK NULL, pic_user_id FK NULL, timestamps`.
- `assets`: `asset_code UNIQUE, name, kib_type ENUM(A,B,C,D,E), location_id NULL, acquisition_date, acquisition_value, funding_source ENUM(BOS,DAK,HIBAH,Komite), condition, status, qr_code_path NULL, notes NULL`.
- `bhp_items`: `code UNIQUE, name, unit, current_stock, minimum_stock`. `bhp_transactions`: mutasi In/Out.
- `asset_loans`, `school_profiles`, `users` + tabel Spatie (`roles`, `permissions`, ...).

## Akun default (RoleSeeder)

- `admin@sekolah.test` / `password` → `superadmin`.
- `pengelola@sekolah.test` / `password` → `pengelola_aset`.

## Instalasi

Prasyarat: PHP 8.2+, Composer, MySQL 8 / XAMPP, Node tidak wajib (hanya jika ubah Vite/Tailwind 4).

1. Nyalakan MySQL. XAMPP Windows:
   `Start-Process C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini`
   Pastikan DB ada: `C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS asetsekolah;"`
2. Clone / buka folder, install dep:
   `composer install`
3. Salin env, generate key:
   `copy .env.example .env`
   `php artisan key:generate`
4. Sesuaikan `.env`:
   `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=asetsekolah`, `DB_USERNAME=root`, `DB_PASSWORD=` (kosong). `APP_URL=http://localhost:8000`.
5. Migrasi + seed:
   `php artisan migrate --force`
   `php artisan db:seed --force`
6. Jalan:
   `php artisan serve` → buka `http://localhost:8000`. `/preview` selalu landing `welcome`. `/` redirect `dashboard` jika login.
7. Login pakai akun default di atas. Alur uji: tambah Gedung → tambah Ruangan (pilih Gedung + PIC) → catat Aset Masuk.

## Perintah validasi

- `php artisan test` → 9 passed (auth, dashboard, asset masuk, gedung+ruang link).
- `php artisan route:list` → cek `buildings.*`, `locations.*`, `transactions/asset/masuk`.
- `php artisan view:clear` setelah edit Blade.

## Catatan

- DataTables butuh internet (CDN). Tanpa internet tabel tetap render polos, tanpa search/paging.
- `procurement_year` simpan `Jan 1`; upgrade ke datepicker penuh bila perlu (`ponytail:` di `AssetTransactionController`).
- Rencana lanjut: server-side DataTables saat baris > ribuan, QR label, export PDF/Excel, modul KIR/peminjaman penuh. Lihat `PLAN.md`.
