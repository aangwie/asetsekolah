<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Aset Sekolah & Barang Habis Pakai - SIMA Sekolah</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- jQuery CDN (Diperlukan oleh DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-- Alpine.js CDN untuk Interaktivitas UI/Modal -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Custom DataTables Styling Overrides */
        .dataTables_wrapper {
            padding: 1rem 0;
        }
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.35rem 2rem 0.35rem 0.75rem;
            background-color: #ffffff;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            margin-left: 0.5rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        table.dataTable thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 0.75rem 1rem !important;
        }
        table.dataTable tbody td {
            padding: 0.875rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.875rem;
            color: #1e293b;
        }
        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border: none !important;
            border-radius: 0.375rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            border: none !important;
            border-radius: 0.375rem !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800" x-data="{ modalOpen: false, selectedAsset: {} }">

    <!-- Navbar / Header Utama -->
    <header class="bg-blue-900 text-white shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Info -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-md">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-base sm:text-lg leading-tight tracking-wide">SIMA-SEKOLAH</h1>
                        <p class="text-xs text-blue-200 hidden sm:block">Sistem Informasi Manajemen Aset Sekolah & BHP</p>
                    </div>
                </div>

                <!-- Navigation Quick Links -->
                <nav class="flex items-center space-x-2 sm:space-x-3 text-xs sm:text-sm">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg bg-blue-800 text-white font-medium hover:bg-blue-700 transition">
                        <i class="fa-solid fa-house mr-1"></i> Beranda
                    </a>
                    <a href="#" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition">
                        <i class="fa-solid fa-boxes-stacked mr-1"></i> KIB A-E
                    </a>
                    <a href="#" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition">
                        <i class="fa-solid fa-cubes mr-1"></i> BHP
                    </a>
                    <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition shadow-md">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i> Login Petugas
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

        <!-- Header Title & Breadcrumb -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                    <span>Sistem Inventaris Sekolah</span>
                    <span>&bull;</span>
                    <span>Katalog Publik</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Data Aset Sekolah & Barang Habis Pakai
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Rekapitulasi unit Aset Tetap Sekolah (KIB A s/d KIB E) dan Persediaan Barang Habis Pakai (BHP).
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
                    <i class="fa-solid fa-print mr-2 text-slate-500"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Metric Cards Grid: KHUSUS JUMLAH ASET KIB A S/D KIB E (Tanpa BHP & Tanpa Total Nilai Aset) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-blue-600"></i> Ringkasan Jumlah Aset Tetap (KIB A - KIB E)
                </h3>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                
                <!-- Card Total Aset Overall (KIB A - E) -->
                <div class="bg-gradient-to-br from-blue-900 to-blue-800 text-white p-4 rounded-2xl shadow-sm border border-blue-900 flex flex-col justify-between col-span-2 sm:col-span-3 lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-medium uppercase tracking-wider text-blue-200">Total Aset (A-E)</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-700/60 flex items-center justify-center text-xs text-white">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-2xl font-black text-white leading-none">1,428</h4>
                        <p class="text-[11px] text-blue-200 mt-1">Total Seluruh Unit Aset</p>
                    </div>
                </div>

                <!-- Card KIB A: Tanah -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">KIB A (Tanah)</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xl font-bold text-slate-900 leading-none">12 <span class="text-xs font-normal text-slate-500">Bidang</span></h4>
                        <p class="text-[10px] text-emerald-600 mt-1 font-medium"><i class="fa-solid fa-circle-check"></i> Bersertifikat</p>
                    </div>
                </div>

                <!-- Card KIB B: Peralatan & Mesin -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">KIB B (Peralatan)</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-laptop"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xl font-bold text-slate-900 leading-none">850 <span class="text-xs font-normal text-slate-500">Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">Elektronik & Mesin</p>
                    </div>
                </div>

                <!-- Card KIB C: Gedung & Bangunan -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">KIB C (Gedung)</span>
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xl font-bold text-slate-900 leading-none">24 <span class="text-xs font-normal text-slate-500">Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">Gedung & Ruang</p>
                    </div>
                </div>

                <!-- Card KIB D: Jalan, Irigasi & Jaringan -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">KIB D (Jaringan)</span>
                        <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xl font-bold text-slate-900 leading-none">18 <span class="text-xs font-normal text-slate-500">Instalasi</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">Jaringan & Air</p>
                    </div>
                </div>

                <!-- Card KIB E: Aset Tetap Lainnya -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">KIB E (Lainnya)</span>
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-book"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xl font-bold text-slate-900 leading-none">524 <span class="text-xs font-normal text-slate-500">Eks/Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">Buku & Seni</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Interactive Filter Form Container -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <form id="filter-form" method="GET" action="" class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Filter Data Inventaris</h3>
                    </div>
                    <button type="button" id="btn-reset-filter" class="text-xs text-slate-500 hover:text-blue-600 transition font-medium flex items-center gap-1">
                        <i class="fa-solid fa-arrows-rotate"></i> Reset Filter
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    
                    <!-- Filter 1: Tahun Perolehan / Pengadaan -->
                    <div>
                        <label for="filter_year" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            <i class="fa-regular fa-calendar mr-1"></i> Filter Tahun Perolehan
                        </label>
                        <select id="filter_year" name="year" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none transition">
                            <option value="">-- Semua Tahun --</option>
                            <option value="2026">2026 (Tahun Berjalan)</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021 & Sebelum</option>
                        </select>
                    </div>

                    <!-- Filter 2: Jenis Aset (Aset Tetap / KIB A-E vs Barang Habis Pakai / BHP) -->
                    <div>
                        <label for="filter_type" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            <i class="fa-solid fa-layer-group mr-1"></i> Filter Jenis Aset / Barang
                        </label>
                        <select id="filter_type" name="asset_type" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none transition">
                            <option value="">-- Semua Jenis (Aset Tetap & BHP) --</option>
                            <optgroup label="Aset Tetap (KIB)">
                                <option value="KIB_ALL">Semua Aset Tetap (KIB A s/d KIB E)</option>
                                <option value="KIB_A">KIB A - Tanah</option>
                                <option value="KIB_B">KIB B - Peralatan & Mesin</option>
                                <option value="KIB_C">KIB C - Gedung & Bangunan</option>
                                <option value="KIB_D">KIB D - Jalan, Irigasi & Jaringan</option>
                                <option value="KIB_E">KIB E - Aset Tetap Lainnya</option>
                            </optgroup>
                            <optgroup label="Barang Habis Pakai">
                                <option value="BHP">Barang Habis Pakai (BHP)</option>
                            </optgroup>
                        </select>
                    </div>

                </div>
            </form>
        </div>

        <!-- Data Table Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-4 gap-2">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Daftar Inventaris Lengkap</h3>
                    <p class="text-xs text-slate-500">Tabel interaktif dengan pencarian cepat, pencetakan label QR, dan pagination.</p>
                </div>
                <div class="flex items-center space-x-2 text-xs font-medium">
                    <span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                        <i class="fa-solid fa-table mr-1"></i> DataTables Integrated
                    </span>
                </div>
            </div>

            <!-- Table Responsive Wrapper -->
            <div class="overflow-x-auto">
                <table id="asset-data-table" class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Kode Barang / Inventaris</th>
                            <th>Nama Aset / Barang</th>
                            <th>Kategori / Kelompok</th>
                            <th>Lokasi / Ruang</th>
                            <th class="text-center">Tahun</th>
                            <th>Kondisi / Status Stok</th>
                            <th class="w-20 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: KIB B Laptop -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">1</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2026-KIBB-0012</td>
                            <td>
                                <div class="font-semibold text-slate-900">Laptop Asus ExpertBook B1400</div>
                                <div class="text-xs text-slate-400">SN: N2390192384 | Core i5 / 16GB / 512GB</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fa-solid fa-laptop mr-1.5 text-blue-600"></i> KIB B (Peralatan)
                                </span>
                            </td>
                            <td>Laboratorium Komputer 1</td>
                            <td class="text-center font-semibold">2026</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Baik (Tersedia)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2026-KIBB-0012', name: 'Laptop Asus ExpertBook B1400', category: 'KIB B (Peralatan & Mesin)', year: '2026', location: 'Laboratorium Komputer 1', condition: 'Baik (Tersedia)', spec: 'Intel Core i5, RAM 16GB, SSD 512GB, SN: N2390192384' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: KIB A Tanah -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">2</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2022-KIBA-0001</td>
                            <td>
                                <div class="font-semibold text-slate-900">Tanah Bangunan Sekolah Utama</div>
                                <div class="text-xs text-slate-400">Sertifikat Hak Pakai No: 12.04.02.01.1.0023 | Luas: 4,250 m²</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-map-location-dot mr-1.5 text-amber-600"></i> KIB A (Tanah)
                                </span>
                            </td>
                            <td>Area Utama Sekolah</td>
                            <td class="text-center font-semibold">2022</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-shield-halved mr-1"></i> Baik (Sertifikat)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2022-KIBA-0001', name: 'Tanah Bangunan Sekolah Utama', category: 'KIB A (Tanah)', year: '2022', location: 'Area Utama Sekolah', condition: 'Baik (Sertifikat Hak Pakai)', spec: 'Luas Tanah: 4.250 m², Hak Pakai No: 12.04.02.01.1.0023' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: BHP Kertas HVS -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">3</td>
                            <td class="font-mono text-xs font-bold text-teal-600">BHP-ATK-2026-003</td>
                            <td>
                                <div class="font-semibold text-slate-900">Kertas HVS A4 80gr Sidu</div>
                                <div class="text-xs text-slate-400">Kategori: ATK Ujian & TU | Satuan: Rim</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-teal-100 text-teal-800">
                                    <i class="fa-solid fa-box-archive mr-1.5 text-teal-600"></i> Barang Habis Pakai
                                </span>
                            </td>
                            <td>Gudang Utama Inventaris</td>
                            <td class="text-center font-semibold">2026</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Stok: 48 Rim (Tersedia)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'BHP-ATK-2026-003', name: 'Kertas HVS A4 80gr Sidu', category: 'Barang Habis Pakai (BHP)', year: '2026', location: 'Gudang Utama Inventaris', condition: 'Sisa Stok: 48 Rim', spec: 'Consumable Goods - Keperluan Ujian dan Administrasi TU' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: KIB C Gedung -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">4</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2023-KIBC-0004</td>
                            <td>
                                <div class="font-semibold text-slate-900">Gedung Laboratorium & Perpustakaan</div>
                                <div class="text-xs text-slate-400">2 Lantai | Konstruksi Beton Bertulang Permanen</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fa-solid fa-building mr-1.5 text-purple-600"></i> KIB C (Gedung)
                                </span>
                            </td>
                            <td>Gedung B Lantai 1 & 2</td>
                            <td class="text-center font-semibold">2023</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Baik
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2023-KIBC-0004', name: 'Gedung Laboratorium & Perpustakaan', category: 'KIB C (Gedung & Bangunan)', year: '2023', location: 'Gedung B Lantai 1 & 2', condition: 'Baik (Beton Bertulang)', spec: 'Luas Lantai: 680 m², Bangunan Permanen 2 Lantai' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 5: KIB B Proyektor Rusak Ringan -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">5</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2024-KIBB-0089</td>
                            <td>
                                <div class="font-semibold text-slate-900">Proyektor Epson EB-X500 3600 Lumens</div>
                                <div class="text-xs text-slate-400">SN: X92K012938 | HDMI & VGA Port</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fa-solid fa-laptop mr-1.5 text-blue-600"></i> KIB B (Peralatan)
                                </span>
                            </td>
                            <td>Ruang Laboratorium IPA</td>
                            <td class="text-center font-semibold">2024</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-wrench mr-1"></i> Kurang Baik (Perbaikan)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2024-KIBB-0089', name: 'Proyektor Epson EB-X500', category: 'KIB B (Peralatan & Mesin)', year: '2024', location: 'Ruang Laboratorium IPA', condition: 'Kurang Baik (Lampu Perlu Diganti)', spec: '3600 Lumens, 3LCD, HDMI, SN: X92K012938' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 6: KIB D Jaringan -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">6</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2025-KIBD-0002</td>
                            <td>
                                <div class="font-semibold text-slate-900">Jaringan Fiber Optik & Access Point Wi-Fi</div>
                                <div class="text-xs text-slate-400">Mikrotik CCR1009 Router + 8 Unit Access Point Unifi</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-cyan-100 text-cyan-800">
                                    <i class="fa-solid fa-network-wired mr-1.5 text-cyan-600"></i> KIB D (Jaringan)
                                </span>
                            </td>
                            <td>Seluruh Area Sekolah</td>
                            <td class="text-center font-semibold">2025</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Baik (Aktif)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2025-KIBD-0002', name: 'Jaringan Fiber Optik & Access Point Wi-Fi', category: 'KIB D (Jalan, Irigasi & Jaringan)', year: '2025', location: 'Seluruh Area Sekolah', condition: 'Baik (Berfungsi Normal)', spec: 'Kabel FO 1000m, Mikrotik CCR1009, 8x Access Point Unifi' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 7: KIB E Koleksi Perpustakaan -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">7</td>
                            <td class="font-mono text-xs font-bold text-blue-600">AST-2024-KIBE-0112</td>
                            <td>
                                <div class="font-semibold text-slate-900">Buku Paket Pembelajaran Matematika Kurikulum Merdeka</div>
                                <div class="text-xs text-slate-400">Penerbit Erlangga | Jumlah: 350 Eksemplar</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-100 text-rose-800">
                                    <i class="fa-solid fa-book mr-1.5 text-rose-600"></i> KIB E (Lainnya)
                                </span>
                            </td>
                            <td>Perpustakaan Utama</td>
                            <td class="text-center font-semibold">2024</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Baik
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'AST-2024-KIBE-0112', name: 'Buku Paket Matematika Kurikulum Merdeka', category: 'KIB E (Aset Lainnya / Perpustakaan)', year: '2024', location: 'Perpustakaan Utama', condition: 'Baik (350 Eks Lengkap)', spec: 'Penerbit Erlangga, Cetakan 2024, Hardcover' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 8: BHP Toner Printer -->
                        <tr>
                            <td class="text-center font-medium text-slate-400">8</td>
                            <td class="font-mono text-xs font-bold text-teal-600">BHP-ATK-2026-018</td>
                            <td>
                                <div class="font-semibold text-slate-900">Toner Printer HP LaserJet 85A</div>
                                <div class="text-xs text-slate-400">Kategori: Sparepart Printer | Satuan: Cartridge</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-teal-100 text-teal-800">
                                    <i class="fa-solid fa-box-archive mr-1.5 text-teal-600"></i> Barang Habis Pakai
                                </span>
                            </td>
                            <td>Ruang Tata Usaha (TU)</td>
                            <td class="text-center font-semibold">2026</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-100 text-rose-800 animate-pulse">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Stok: 2 Unit (Tipis)
                                </span>
                            </td>
                            <td class="text-center">
                                <button @click="selectedAsset = { code: 'BHP-ATK-2026-018', name: 'Toner Printer HP LaserJet 85A', category: 'Barang Habis Pakai (BHP)', year: '2026', location: 'Ruang Tata Usaha (TU)', condition: 'Stok Kritis: 2 Unit', spec: 'Suku Cadang Printer Operasional Tata Usaha' }; modalOpen = true" 
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail & QR Label">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal Detail Aset & Label QR Code (Alpine.js) -->
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
         style="display: none;">
        
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5">
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider" x-text="selectedAsset.category"></span>
                    <h3 class="text-xl font-extrabold text-slate-900" x-text="selectedAsset.name"></h3>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Content: Dynamic QR Code Preview & Details -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200/70">
                <!-- QR Code Display -->
                <div class="sm:col-span-1 flex flex-col items-center justify-center bg-white p-3 rounded-lg shadow-sm border border-slate-200">
                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(selectedAsset.code)" 
                         alt="QR Code Label" class="w-28 h-28 object-contain">
                    <span class="text-[10px] font-mono font-bold text-slate-700 mt-2 text-center" x-text="selectedAsset.code"></span>
                </div>

                <!-- Asset Attributes -->
                <div class="sm:col-span-2 space-y-2 text-xs text-slate-700">
                    <div>
                        <span class="font-semibold text-slate-500 block">Lokasi Penyimpanan:</span>
                        <span class="font-medium text-slate-900" x-text="selectedAsset.location"></span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-500 block">Tahun Perolehan:</span>
                        <span class="font-medium text-slate-900" x-text="selectedAsset.year"></span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-500 block">Kondisi / Status Stok:</span>
                        <span class="font-medium text-slate-900" x-text="selectedAsset.condition"></span>
                    </div>
                </div>
            </div>

            <!-- Additional Specs -->
            <div>
                <h4 class="text-xs font-semibold uppercase text-slate-500 mb-1">Spesifikasi & Catatan Inventaris:</h4>
                <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200" x-text="selectedAsset.spec"></p>
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                    Tutup
                </button>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition flex items-center">
                    <i class="fa-solid fa-print mr-1.5"></i> Cetak Label QR
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            <p>&copy; 2026 Aplikasi Manajemen Aset Sekolah. Hak Cipta Dilindungi. | Dev By Aang Wirawan</p>
        </div>
    </footer>

    <!-- DataTables & Filter Integration Script -->
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables dengan opsi Bahasa Indonesia
            var table = $('#asset-data-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "Cari Data:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data aset atau barang tidak ditemukan.",
                    info: "Halaman _PAGE_ dari _PAGES_ (_TOTAL_ total barang)",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Sebelumnya"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [0, 7] } // Nonaktifkan sortir untuk No dan Aksi
                ]
            });

            // 1. Filter Berdasarkan Tahun Perolehan (Kolom Index 5)
            $('#filter_year').on('change', function() {
                var selectedYear = $(this).val();
                table.column(5).search(selectedYear).draw();
            });

            // 2. Filter Berdasarkan Jenis Aset / BHP (Kolom Index 3)
            $('#filter_type').on('change', function() {
                var selectedType = $(this).val();
                
                if (selectedType === 'KIB_ALL') {
                    table.column(3).search('KIB').draw();
                } else if (selectedType === 'KIB_A') {
                    table.column(3).search('KIB A').draw();
                } else if (selectedType === 'KIB_B') {
                    table.column(3).search('KIB B').draw();
                } else if (selectedType === 'KIB_C') {
                    table.column(3).search('KIB C').draw();
                } else if (selectedType === 'KIB_D') {
                    table.column(3).search('KIB D').draw();
                } else if (selectedType === 'KIB_E') {
                    table.column(3).search('KIB E').draw();
                } else if (selectedType === 'BHP') {
                    table.column(3).search('Barang Habis Pakai').draw();
                } else {
                    table.column(3).search('').draw();
                }
            });

            // Reset Filter Button
            $('#btn-reset-filter').on('click', function() {
                $('#filter_year').val('');
                $('#filter_type').val('');
                table.search('').columns().search('').draw();
            });
        });
    </script>
</body>
</html>