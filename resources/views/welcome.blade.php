<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Aset Sekolah & Barang Habis Pakai - SIMA Sekolah</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-sima.png') }}">

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
                    <!--a href="{{ route('home') }}" class="px-3 py-2 rounded-lg bg-blue-800 text-white font-medium hover:bg-blue-700 transition">
                        <i class="fa-solid fa-house mr-1"></i> Beranda
                    </a>
                    <a href="#" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition">
                        <i class="fa-solid fa-boxes-stacked mr-1"></i> KIB A-E
                    </a>
                    <a href="#" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition">
                        <i class="fa-solid fa-cubes mr-1"></i> BHP
                    </a-->
                    <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition shadow-md">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i> Login
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
                    Rekapitulasi {{ number_format($totalUnits ?? 0) }} unit Aset Tetap Sekolah (KIB A s/d KIB E) dan {{ number_format($totalBhp ?? 0) }} stok Barang Habis Pakai (BHP).
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <!--button onclick="window.print()" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
                    <i class="fa-solid fa-print mr-2 text-slate-500"></i> Cetak Laporan
                </button-->
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
                        <h4 class="text-2xl font-black text-white leading-none">{{ number_format($totalUnits ?? 0, 0, ',', '.') }}</h4>
                        <p class="text-[11px] text-blue-200 mt-1">{{ $totalTypes ?? 0 }} Jenis • Total Seluruh Unit</p>
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
                        <h4 class="text-xl font-bold text-slate-900 leading-none">{{ number_format($perKib['A']->units ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Bidang</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ $perKib['A']->rows ?? 0 }} Jenis</p>
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
                        <h4 class="text-xl font-bold text-slate-900 leading-none">{{ number_format($perKib['B']->units ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">{{ $perKib['B']->rows ?? 0 }} Jenis</p>
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
                        <h4 class="text-xl font-bold text-slate-900 leading-none">{{ number_format($perKib['C']->units ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">{{ $perKib['C']->rows ?? 0 }} Jenis</p>
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
                        <h4 class="text-xl font-bold text-slate-900 leading-none">{{ number_format($perKib['D']->units ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Instalasi</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">{{ $perKib['D']->rows ?? 0 }} Jenis</p>
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
                        <h4 class="text-xl font-bold text-slate-900 leading-none">{{ number_format($perKib['E']->units ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Eks/Unit</span></h4>
                        <p class="text-[10px] text-slate-500 mt-1">{{ $perKib['E']->rows ?? 0 }} Jenis</p>
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                    
                    <!-- Filter 1: Tahun Perolehan / Pengadaan -->
                    <div>
                        <label for="filter_year" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            <i class="fa-regular fa-calendar mr-1"></i> Filter Tahun Perolehan
                        </label>
                        <select id="filter_year" name="year" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none transition">
                            <option value="">-- Semua Tahun --</option>
                            @foreach(($years ?? []) as $y)
                                <option value="{{ $y }}">{{ $y }}{{ $y == now()->year ? ' (Tahun Berjalan)' : '' }}</option>
                            @endforeach
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

                    <!-- Filter 3: Lokasi / Ruang -->
                    <div>
                        <label for="filter_location" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            <i class="fa-solid fa-location-dot mr-1"></i> Filter Lokasi / Ruang
                        </label>
                        <select id="filter_location" name="location" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none transition">
                            <option value="">-- Semua Ruangan --</option>
                            @if(!collect($locations ?? [])->contains('name', 'Gudang BHP'))
                            <option value="Gudang BHP">Gudang BHP</option>
                            @endif
                            @foreach(($locations ?? []) as $loc)
                                <option value="{{ $loc->name }}">{{ $loc->name }}</option>
                            @endforeach
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
@php $kibLabel=['A'=>'KIB A (Tanah)','B'=>'KIB B (Peralatan & Mesin)','C'=>'KIB C (Gedung & Bangunan)','D'=>'KIB D (Jalan, Irigasi & Jaringan)','E'=>'KIB E (Aset Tetap Lainnya)']; $kibBadge=['A'=>'bg-amber-100 text-amber-800','B'=>'bg-blue-100 text-blue-800','C'=>'bg-purple-100 text-purple-800','D'=>'bg-cyan-100 text-cyan-800','E'=>'bg-rose-100 text-rose-800']; $condBadge=['baik'=>'bg-emerald-100 text-emerald-800','kurang_baik'=>'bg-amber-100 text-amber-800','rusak_berat'=>'bg-rose-100 text-rose-800']; @endphp
@foreach(($assets ?? collect()) as $a)
<tr><td class="text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
<td class="font-mono text-xs font-bold text-blue-600">{{ $a->asset_code }}</td>
<td><div class="font-semibold text-slate-900">{{ $a->name }}</div><div class="text-xs text-slate-400">{{ \Str::limit($a->notes ?? ('Qty: '.($a->quantity ?? 1)),80) }}</div></td>
<td><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $kibBadge[$a->kib_type] ?? 'bg-slate-100 text-slate-700' }}">{{ $kibLabel[$a->kib_type] ?? $a->kib_type }}</span></td>
<td>{{ $a->location?->name ?? '-' }}</td>
<td class="text-center font-semibold">{{ $a->procurement_year ?? $a->acquisition_date?->format('Y') }}</td>
<td><span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $condBadge[$a->condition] ?? 'bg-slate-100 text-slate-700' }}">{{ ucwords(str_replace('_',' ',$a->condition)) }} ({{ ucfirst($a->status) }})</span></td>
<td class="text-center"><button @click='selectedAsset = @js(['code'=>$a->asset_code,'name'=>$a->name,'category'=>($kibLabel[$a->kib_type] ?? $a->kib_type),'year'=>(string)($a->procurement_year ?? $a->acquisition_date?->format('Y')),'location'=>($a->location?->name ?? '-'),'condition'=>($a->condition.' ('.$a->status.')'),'spec'=>($a->notes ?? '-')]); modalOpen = true' class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail"><i class="fa-solid fa-qrcode text-sm"></i></button></td></tr>
@endforeach
@foreach(($bhpItems ?? collect()) as $b)
<tr><td class="text-center font-medium text-slate-400">{{ ($assets ?? collect())->count()+$loop->iteration }}</td>
<td class="font-mono text-xs font-bold text-teal-600">{{ $b->code }}</td>
<td><div class="font-semibold text-slate-900">{{ $b->name }}</div><div class="text-xs text-slate-400">Kategori: {{ $b->category }} | Satuan: {{ $b->unit }}</div></td>
<td><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-teal-100 text-teal-800">Barang Habis Pakai</span></td>
<td>Gudang BHP</td><td class="text-center font-semibold">-</td>
<td>@if($b->current_stock<=$b->minimum_stock)<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-100 text-rose-800">Stok: {{ $b->current_stock }} {{ $b->unit }} (Tipis)</span>@else<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">Stok: {{ $b->current_stock }} {{ $b->unit }}</span>@endif</td>
<td class="text-center"><button @click='selectedAsset = @js(['code'=>$b->code,'name'=>$b->name,'category'=>'Barang Habis Pakai (BHP)','year'=>'-','location'=>'Gudang BHP','condition'=>('Stok: '.$b->current_stock.' '.$b->unit),'spec'=>('Kategori '.$b->category.', minimum '.$b->minimum_stock.' '.$b->unit)]); modalOpen = true' class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Lihat Detail"><i class="fa-solid fa-qrcode text-sm"></i></button></td></tr>
@endforeach
@if(($assets ?? collect())->isEmpty() && ($bhpItems ?? collect())->isEmpty())
<tr><td colspan="8" class="text-center py-8 text-sm text-slate-500">Belum ada data inventaris.</td></tr>
@endif
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
                if(selectedYear){table.column(5).search('^'+selectedYear+'$',true,false).draw();}else{table.column(5).search('').draw();}
            });

            // 2. Filter Berdasarkan Jenis Aset / BHP (Kolom Index 3)
            // ponytail: regex exact, smart=false cegah "KIB A" cocok ke "KIB B"; add server-side filter when dataset besar
            $('#filter_type').on('change', function() {
                var selectedType = $(this).val();

                if (selectedType === 'KIB_ALL') {
                    table.column(3).search('KIB [A-E] \\(', true, false).draw();
                } else if (selectedType === 'KIB_A') {
                    table.column(3).search('KIB A \\(', true, false).draw();
                } else if (selectedType === 'KIB_B') {
                    table.column(3).search('KIB B \\(', true, false).draw();
                } else if (selectedType === 'KIB_C') {
                    table.column(3).search('KIB C \\(', true, false).draw();
                } else if (selectedType === 'KIB_D') {
                    table.column(3).search('KIB D \\(', true, false).draw();
                } else if (selectedType === 'KIB_E') {
                    table.column(3).search('KIB E \\(', true, false).draw();
                } else if (selectedType === 'BHP') {
                    table.column(3).search('Barang Habis Pakai', true, false).draw();
                } else {
                    table.column(3).search('').draw();
                }
            });


            // 3. Filter Lokasi (Kolom Index 4)
            $('#filter_location').on('change', function() {
                var v=$(this).val();table.column(4).search(v?('^'+v.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+'$'):'',true,false).draw();
            });

            // Reset
            $('#btn-reset-filter').on('click', function() {
                $('#filter_year').val('');
                $('#filter_type').val('');
                $('#filter_location').val('');
                table.search('').columns().search('').draw();
            });
        });
    </script>
</body>
</html>