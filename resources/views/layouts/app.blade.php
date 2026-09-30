<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - SIMA Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-slate-800">
    <header class="bg-blue-900 text-white shadow-lg sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-md"><i class="fa-solid fa-school"></i></div>
                    <div>
                        <h1 class="font-bold text-base sm:text-lg leading-tight tracking-wide">SIMA-SEKOLAH</h1>
                        <p class="text-xs text-blue-200 hidden sm:block">Sistem Informasi Manajemen Aset Sekolah & BHP</p>
                    </div>
                </a>
                <nav class="flex items-center space-x-2 sm:space-x-3 text-xs sm:text-sm">
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
                    @role('superadmin|pengelola_aset')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-location-dot mr-1"></i> Lokasi <i class="fa-solid fa-caret-down ml-1"></i></button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 text-slate-700 z-50">
                            <a href="{{ route('buildings.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-building mr-2 text-blue-600"></i>Gedung</a>
                            <a href="{{ route('locations.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50"><i class="fa-solid fa-door-open mr-2 text-emerald-600"></i>Ruangan</a>
                        </div>
                    </div>
                    @endrole
                    @role('superadmin')
                    <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-users mr-1"></i> Users</a>
                    @endrole
                    <a href="{{ route('preview') }}" target="_blank" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition"><i class="fa-solid fa-globe mr-1"></i> Lihat Website</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="px-3.5 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium transition shadow-md"><i class="fa-solid fa-right-from-bracket mr-1"></i> Logout</button></form>
                </nav>
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
            <p>&copy; 2026 Aplikasi Manajemen Aset Sekolah (Laravel 12 & Tailwind CSS 4). Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    @yield('scripts')
</body>

</html>