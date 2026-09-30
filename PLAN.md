# Rencana Pengembangan Aplikasi Manajemen Aset Sekolah

Dokumen ini berisi rencana arsitektur, desain database, modul fitur, dan tahapan pengembangan untuk **Aplikasi Manajemen Aset Sekolah** menggunakan **Laravel 12**, **Tailwind CSS 4**, dan **MySQL**.

---

## 1. Ringkasan Proyek

Aplikasi ini dirancang untuk mengelola inventarisasi aset sekolah secara digital dan terstruktur. Pengelolaan aset dibagi secara mendasar menjadi 2 kelompok utama:

1. **Aset Tetap (KIB A – KIB E)**: Barang bernilai tinggi/jangka panjang yang dikategorikan sesuai standar Permendagri/Kartu Inventaris Barang (KIB).
2. **Barang Habis Pakai (BHP)**: Consumable goods/material yang berfokus pada manajemen kuantitas stok masuk, stok keluar, dan peringatan batas minimum stok.

---

## 2. Tech Stack & Dependensi Utama

* **Framework Backend**: Laravel 12 (PHP 8.2+)
* **Frontend**: Blade Templating + Tailwind CSS 4 + Alpine.js (atau Laravel Livewire 3)
* **Database Engine**: MySQL 8.0+
* **Build Tool**: Vite (terintegrasi dengan `@tailwindcss/vite` v4)
* **Package Pendukung**:
  * `simplesoftwareio/simple-qrcode` — Generasi QR Code label inventaris unit.
  * `barryvdh/laravel-dompdf` — Cetak dokumen PDF (Laporan KIB A-E, KIR, Surat Peminjaman).
  * `maatwebsite/excel` — Import/Export data master & laporan format Excel (`.xlsx`).
  * `spatie/laravel-permission` — Role-Based Access Control (RBAC).

---

## 3. Arsitektur Database (MySQL Schema)

### 3.1 Diagram Hubungan Data (High-Level)

```
                    ┌────────────────────────┐
                    │       locations        │
                    │  (Ruang/Gedung/Seksi)  │
                    └───────────┬────────────┘
                                │
        ┌───────────────────────┴───────────────────────┐
        ▼                                               ▼
┌───────────────┐                             ┌───────────────────┐
│    assets     │ (Master KIB A-E)            │     bhp_items     │ (Barang Habis Pakai)
└───────┬───────┘                             └─────────┬─────────┘
        │                                               │
        ├─► kib_a_lands                                 └─► bhp_transactions (In/Out)
        ├─► kib_b_equipments
        ├─► kib_c_buildings
        ├─► kib_d_networks
        └─► kib_e_others
```

### 3.2 Detail Skema Tabel

#### A. Tabel Core & Autentikasi
* `users`: `id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`.
* `roles` & `model_has_roles`: Di-manage oleh `spatie/laravel-permission` (Role: `superadmin`, `pengelola_aset`, `guru_staf`).
* `locations`:
  * `id` (PK)
  * `code` (VARCHAR, Unique, e.g., `LAB-IPA-01`)
  * `name` (VARCHAR, e.g., `Laboratorium IPA`)
  * `building_name` (VARCHAR, e.g., `Gedung B`)
  * `pic_user_id` (FK to `users`, Nullable)
  * `timestamps`

#### B. Tabel Master Aset Tetap (Parent Table)
* `assets`:
  * `id` (PK)
  * `asset_code` (VARCHAR, Unique, e.g., `AST-2026-KIBB-0012`)
  * `name` (VARCHAR)
  * `kib_type` (`enum('A', 'B', 'C', 'D', 'E')`)
  * `location_id` (FK to `locations`, Nullable)
  * `acquisition_date` (DATE)
  * `acquisition_value` (DECIMAL(15,2))
  * `condition` (`enum('baik', 'kurang_baik', 'rusak_berat')`)
  * `status` (`enum('tersedia', 'dipinjam', 'dipelihara', 'dihapus')`)
  * `qr_code_path` (VARCHAR, Nullable)
  * `notes` (TEXT, Nullable)
  * `timestamps`

#### C. Tabel Detail KIB (Child Tables - 1-to-1 dengan `assets`)
1. **`kib_a_lands` (KIB A - Tanah)**:
   * `id` (PK), `asset_id` (FK to `assets`, Unique)
   * `surface_area` (FLOAT) — Luas m²
   * `certificate_number` (VARCHAR)
   * `certificate_date` (DATE, Nullable)
   * `address` (TEXT)
   * `land_use` (VARCHAR) — Hak pakai/peruntukan

2. **`kib_b_equipments` (KIB B - Peralatan & Mesin)**:
   * `id` (PK), `asset_id` (FK to `assets`, Unique)
   * `brand` (VARCHAR) — Merk/Type
   * `size_material` (VARCHAR) — Ukuran/Bahan
   * `chassis_number` (VARCHAR, Nullable) — No. Rangka
   * `engine_number` (VARCHAR, Nullable) — No. Mesin
   * `serial_number` (VARCHAR, Nullable) — No. Pabrik/Seri

3. **`kib_c_buildings` (KIB C - Gedung & Bangunan)**:
   * `id` (PK), `asset_id` (FK to `assets`, Unique)
   * `building_condition` (VARCHAR) — Bertingkat/Tidak
   * `is_concrete` (BOOLEAN) — Beton/Tidak
   * `floor_area` (FLOAT) — Luas lantai m²
   * `address` (TEXT)
   * `document_number` (VARCHAR, Nullable) — No. Dokumen Gedung

4. **`kib_d_networks` (KIB D - Jalan, Irigasi & Jaringan)**:
   * `id` (PK), `asset_id` (FK to `assets`, Unique)
   * `construction_type` (VARCHAR) — Tipe Konstruksi
   * `length` (FLOAT, Nullable) — Panjang (m)
   * `width` (FLOAT, Nullable) — Lebar (m)
   * `address` (TEXT)
   * `document_number` (VARCHAR, Nullable)

5. **`kib_e_others` (KIB E - Aset Tetap Lainnya)**:
   * `id` (PK), `asset_id` (FK to `assets`, Unique)
   * `book_title_author` (VARCHAR, Nullable) — Judul Buku/Pencipta
   * `art_spec` (VARCHAR, Nullable) — Spesifikasi Barang Seni
   * `animal_type_size` (VARCHAR, Nullable) — Hewan/Tumbuhan
   * `quantity` (INT)

#### D. Tabel Barang Habis Pakai (BHP)
1. **`bhp_items`**:
   * `id` (PK)
   * `code` (VARCHAR, Unique, e.g., `BHP-ATK-001`)
   * `name` (VARCHAR, e.g., `Kertas A4 80gr`)
   * `category` (VARCHAR, e.g., `ATK`, `Kebersihan`, `Bahan Lab`)
   * `unit` (VARCHAR, e.g., `rim`, `pcs`, `box`, `pack`)
   * `initial_stock` (INT)
   * `current_stock` (INT)
   * `minimum_stock` (INT) — Batas threshold alert stok tipis
   * `unit_price` (DECIMAL(15,2), Nullable)
   * `timestamps`

2. **`bhp_transactions`**:
   * `id` (PK)
   * `bhp_item_id` (FK to `bhp_items`)
   * `user_id` (FK to `users`) — Petugas pencatat
   * `type` (`enum('in', 'out')`)
   * `quantity` (INT)
   * `reference_number` (VARCHAR, Nullable) — No. Nota / No. Permintaan
   * `recipient_or_supplier` (VARCHAR, Nullable) — Diambil oleh / Pemasok
   * `transaction_date` (DATE)
   * `notes` (TEXT, Nullable)
   * `timestamps`

#### E. Tabel Modul Peminjaman (Aset KIB)
* `asset_loans`:
  * `id` (PK)
  * `asset_id` (FK to `assets`)
  * `borrower_id` (FK to `users` / Nama Peminjam)
  * `loan_date` (DATE)
  * `expected_return_date` (DATE)
  * `actual_return_date` (DATE, Nullable)
  * `status` (`enum('pending', 'approved', 'borrowed', 'returned', 'overdue')`)
  * `condition_before` (`enum('baik', 'kurang_baik')`)
  * `condition_after` (`enum('baik', 'kurang_baik', 'rusak_berat')`, Nullable)
  * `notes` (TEXT, Nullable)
  * `timestamps`

---

## 4. Struktur Modul & Navigasi Aplikasi

```
Dashboard Utama
├── Modul KIB (Aset Tetap)
│   ├── KIB A (Tanah)
│   ├── KIB B (Peralatan & Mesin)
│   ├── KIB C (Gedung & Bangunan)
│   ├── KIB D (Jalan, Irigasi & Jaringan)
│   └── KIB E (Aset Tetap Lainnya)
├── Modul Barang Habis Pakai (BHP)
│   ├── Master Item BHP
│   ├── Transaksi Stok Masuk (Procurement)
│   └── Transaksi Stok Keluar (Distribution)
├── Modul Operasional & Servis
│   ├── Kartu Inventaris Ruangan (KIR)
│   ├── Transaksi Peminjaman Aset
│   └── Cetak QR Code Label Barcode
├── Modul Pelaporan
│   ├── Ekspor Laporan Resmi KIB A–E (PDF/Excel)
│   ├── Laporan Mutasi Stok BHP
│   └── Rekap Kondisi & Nilai Depresiasi Aset
└── Pengaturan System
    ├── Data Ruangan & Lokasi
    ├── Manajemen Pengguna & Hak Akses
    └── Profil Sekolah
```

---

## 5. Panduan Konfigurasi UI (Tailwind CSS 4 di Laravel 12)

Dalam Laravel 12 dengan Tailwind CSS 4, tidak lagi memerlukan file `tailwind.config.js`. Konfigurasi dilakukan langsung di dalam stylesheet.

### 5.1 Instalasi via NPM
```bash
npm install tailwindcss @tailwindcss/vite
```

### 5.2 Konfigurasi `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### 5.3 Konfigurasi `resources/css/app.css`
```css
@import "tailwindcss";

@theme {
  --color-school-blue-50: #eff6ff;
  --color-school-blue-600: #2563eb;
  --color-school-blue-900: #1e3a8a;
  --color-school-teal-500: #14b8a6;
  --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
}
```

---

## 6. Milestones & Roadmap Pengembangan

| Fase | Durasi Estimated | Output Utama |
| :--- | :--- | :--- |
| **Fase 1: Setup Fondasi & Master Data** | Minggu 1 | Init Laravel 12, Vite + Tailwind 4, Auth, Migrations Master User, Roles, Lokasi Ruangan. |
| **Fase 2: Modul Aset Tetap (KIB A-E)** | Minggu 2 | CRUD Complete KIB A, B, C, D, E; Relasi `assets` ke KIB Child; Generator QR Code per Unit. |
| **Fase 3: Modul Barang Habis Pakai (BHP)** | Minggu 3 | CRUD Master BHP; Transaksi Stock In/Out; Alert Stok Threshold Minimum; History Mutasi. |
| **Fase 4: Peminjaman & KIR** | Minggu 4 | Workflow Peminjaman Aset KIB, Pencatatan Kartu Inventaris Ruangan (KIR), Print Label QR Code. |
| **Fase 5: Laporan, Dashboard & Release** | Minggu 5 | Export Laporan KIB & BHP ke PDF/Excel, Dashboard Widget Analytics, UI Polish, UAT & Deployment. |

---

## 7. Strategi Validasi Data & Keamanan

1. **Database Transactions**: Penggunaan `DB::transaction()` pada setiap mutasi stok BHP dan pendaftaran Aset KIB (yang mengisi tabel `assets` + tabel `kib_x_*`) untuk menjamin konsistensi data.
2. **Form Request Validation**: Validasi ketat pada nomor sertifikat, kode barang, dan input nominal angka/harga.
3. **Role & Permission Check**: Menggunakan middleware policy Laravel agar hanya `superadmin` & `pengelola_aset` yang dapat melakukan tindakan mutasi atau penghapusan aset.
```

eof
```