<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - SIMA Sekolah</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-sima.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Samakan welcome.blade.php: DataTables + tabel biasa */
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
        table.dataTable,
        main table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        table.dataTable thead th,
        main table thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: none !important;
            letter-spacing: normal;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 0.75rem 1rem !important;
        }
        table.dataTable tbody td,
        main table tbody td {
            padding: 0.875rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.875rem;
            color: #1e293b;
        }
        table.dataTable tbody tr:hover,
        main table tbody tr:hover {
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

<body class="min-h-screen flex flex-col text-slate-800">
    <header class="bg-blue-900 text-white shadow-lg sticky top-0 z-30" x-data="{ mobile: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-md"><i class="fa-solid fa-school"></i></div>
                    <div>
                        <h1 class="font-bold text-base sm:text-lg leading-tight tracking-wide">SIMA-SEKOLAH</h1>
                        <p class="text-xs text-blue-200 hidden sm:block">Sistem Informasi Manajemen Aset Sekolah & BHP</p>
                    </div>
                </a>
                <nav class="hidden lg:flex items-center space-x-2 sm:space-x-3 text-xs sm:text-sm">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg bg-blue-800 text-white font-medium hover:bg-blue-700 transition"><i class="fa-solid fa-chart-line mr-1"></i> Dashboard</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-right-left mr-1"></i> Transaksi <i class="fa-solid fa-caret-down ml-1"></i></button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 text-slate-700 z-50">
                            <p class="px-4 py-1 text-[11px] font-bold uppercase text-slate-400">Aset</p>
                            <a href="{{ route('transactions.asset.masuk') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-right-to-bracket mr-2 text-emerald-600"></i>Masuk</a>
                            <a href="{{ route('transactions.asset.keluar') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-right-from-bracket mr-2 text-red-500"></i>Keluar</a>
                            <p class="px-4 py-1 text-[11px] font-bold uppercase text-slate-400 border-t border-slate-100 mt-1 pt-2">BHP</p>
                            <a href="{{ route('transactions.bhp.masuk') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-right-to-bracket mr-2 text-emerald-600"></i>Masuk</a>
                            <a href="{{ route('transactions.bhp.keluar') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-right-from-bracket mr-2 text-red-500"></i>Keluar</a>
                        </div>
                    </div>
                    <div class="relative" x-data="{ open: false, sub: null }">
                        <button @click="open = !open; sub = null" @click.away="open = false; sub = null" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-file-lines mr-1"></i> Laporan <i class="fa-solid fa-caret-down ml-1"></i></button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-2 text-slate-700 z-50">
                            <div class="relative">
                                <button @click.stop="sub = sub === 'aset' ? null : 'aset'" class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50"><span><i class="fa-solid fa-boxes-stacked mr-2 text-blue-600"></i>Aset</span><i class="fa-solid fa-caret-right text-slate-400"></i></button>
                                <div x-show="sub === 'aset'" x-cloak class="md:absolute md:left-full md:top-0 md:ml-1 static ml-4 md:ml-1 mt-1 md:mt-0 w-52 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                                    <a href="{{ route('reports.asset.data') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-boxes-stacked mr-2 text-blue-600"></i>Data Aset</a>
                                    <a href="{{ route('reports.asset.rekap') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-chart-pie mr-2 text-emerald-600"></i>Rekap Aset</a>
                                    @foreach(['a','b','c','d','e'] as $k)
                                    <a href="{{ route('reports.asset.kib', ['kib' => $k]) }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-table-list mr-2 text-amber-600"></i>KIB {{ strtoupper($k) }}</a>
                                    @endforeach
                                    <a href="{{ route('reports.asset.mutasi') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-arrow-right-arrow-left mr-2 text-purple-600"></i>Mutasi Aset</a>
                                </div>
                            </div>
                            <div class="relative">
                                <button @click.stop="sub = sub === 'bhp' ? null : 'bhp'" class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50"><span><i class="fa-solid fa-box-open mr-2 text-rose-600"></i>BHP</span><i class="fa-solid fa-caret-right text-slate-400"></i></button>
                                <div x-show="sub === 'bhp'" x-cloak class="md:absolute md:left-full md:top-0 md:ml-1 static ml-4 md:ml-1 mt-1 md:mt-0 w-52 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                                    <a href="{{ route('reports.bhp.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-box-open mr-2 text-rose-600"></i>Laporan BHP</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @role('superadmin|pengelola_aset')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-location-dot mr-1"></i> Lokasi <i class="fa-solid fa-caret-down ml-1"></i></button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 text-slate-700 z-50">
                            <a href="{{ route('buildings.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-building mr-2 text-blue-600"></i>Gedung</a>
                            <a href="{{ route('locations.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-door-open mr-2 text-emerald-600"></i>Ruangan</a>
                        </div>
                    </div>
                    @endrole
                    @role('superadmin|pengelola_aset')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-gear mr-1"></i> Manajemen <i class="fa-solid fa-caret-down ml-1"></i></button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 text-slate-700 z-50">
                            <a href="{{ route('school-profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-school mr-2 text-blue-600"></i>Data Sekolah</a>
                            @role('superadmin')
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-users mr-2 text-emerald-600"></i>Users</a>
                            <a href="{{ route('web-settings.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-globe mr-2 text-purple-600"></i>Web</a>
                            @endrole
                        </div>
                    </div>
                    @endrole
                    <!--a href="{{ route('preview') }}" target="_blank" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-globe mr-1"></i> Lihat Website</a-->
                    <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="px-3.5 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium transition shadow-md"><i class="fa-solid fa-right-from-bracket mr-1"></i> Logout</button></form>
                </nav>
                <button type="button" @click="mobile = !mobile" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-800 hover:bg-blue-700 transition" aria-label="Menu navigasi">
                    <i x-show="!mobile" class="fa-solid fa-bars"></i>
                    <i x-show="mobile" x-cloak class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div x-show="mobile" x-cloak class="lg:hidden pb-4 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-lg bg-blue-800 font-medium"><i class="fa-solid fa-chart-line mr-2"></i>Dashboard</a>
                <details class="bg-blue-800/60 rounded-lg">
                    <summary class="px-3 py-2.5 cursor-pointer list-none flex items-center justify-between"><span><i class="fa-solid fa-right-left mr-2"></i>Transaksi</span><i class="fa-solid fa-caret-down text-blue-300"></i></summary>
                    <div class="px-3 pb-3 pt-1 space-y-1">
                        <p class="px-2 pt-1 text-[11px] font-bold uppercase text-blue-300">Aset</p>
                        <a href="{{ route('transactions.asset.masuk') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Aset Masuk</a>
                        <a href="{{ route('transactions.asset.keluar') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Aset Keluar</a>
                        <p class="px-2 pt-1 text-[11px] font-bold uppercase text-blue-300 border-t border-blue-700/60 mt-1">BHP</p>
                        <a href="{{ route('transactions.bhp.masuk') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">BHP Masuk</a>
                        <a href="{{ route('transactions.bhp.keluar') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">BHP Keluar</a>
                    </div>
                </details>
                <details class="bg-blue-800/60 rounded-lg">
                    <summary class="px-3 py-2.5 cursor-pointer list-none flex items-center justify-between"><span><i class="fa-solid fa-file-lines mr-2"></i>Laporan</span><i class="fa-solid fa-caret-down text-blue-300"></i></summary>
                    <div class="px-3 pb-3 pt-1 space-y-1">
                        <a href="{{ route('reports.asset.data') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Data Aset</a>
                        <a href="{{ route('reports.asset.rekap') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Rekap Aset</a>
                        <a href="{{ route('reports.asset.mutasi') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Mutasi Aset</a>
                        <a href="{{ route('reports.bhp.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Laporan BHP</a>
                        @foreach(['a','b','c','d','e'] as $k)
                        <a href="{{ route('reports.asset.kib', ['kib' => $k]) }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">KIB {{ strtoupper($k) }}</a>
                        @endforeach
                    </div>
                </details>
                @role('superadmin|pengelola_aset')
                <details class="bg-blue-800/60 rounded-lg">
                    <summary class="px-3 py-2.5 cursor-pointer list-none flex items-center justify-between"><span><i class="fa-solid fa-location-dot mr-2"></i>Lokasi</span><i class="fa-solid fa-caret-down text-blue-300"></i></summary>
                    <div class="px-3 pb-3 pt-1 space-y-1">
                        <a href="{{ route('buildings.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Gedung</a>
                        <a href="{{ route('locations.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Ruangan</a>
                    </div>
                </details>
                @endrole
                @role('superadmin|pengelola_aset')
                <details class="bg-blue-800/60 rounded-lg">
                    <summary class="px-3 py-2.5 cursor-pointer list-none flex items-center justify-between"><span><i class="fa-solid fa-gear mr-2"></i>Manajemen</span><i class="fa-solid fa-caret-down text-blue-300"></i></summary>
                    <div class="px-3 pb-3 pt-1 space-y-1">
                        <a href="{{ route('school-profile.edit') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Data Sekolah</a>
                        @role('superadmin')
                        <a href="{{ route('users.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Users</a>
                        <a href="{{ route('web-settings.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-800">Web</a>
                        @endrole
                    </div>
                </details>
                @endrole
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full px-3 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 font-medium text-left"><i class="fa-solid fa-right-from-bracket mr-2"></i>Logout</button></form>
            </div>
        </div>
    </header>
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
        @if(session('ok'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm"><i class="fa-solid fa-circle-check mr-1"></i> {{ session('ok') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>@endif
        @yield('content')
    </main>
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            <p>&copy; 2026 Aplikasi Manajemen Aset Sekolah. Hak Cipta Dilindungi. | Dev By Aang Wirawan</p>
        </div>
    </footer>
    @yield('scripts')
</body>

</html>