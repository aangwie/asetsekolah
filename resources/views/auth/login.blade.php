<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Petugas - SIMA Sekolah</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-sima.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-slate-800">
    <header class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-md"><i class="fa-solid fa-school"></i></div>
                    <div>
                        <h1 class="font-bold text-base sm:text-lg leading-tight tracking-wide">SIMA-SEKOLAH</h1>
                        <p class="text-xs text-blue-200 hidden sm:block">Sistem Informasi Manajemen Aset Sekolah & BHP</p>
                    </div>
                </a>
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-800 transition text-xs sm:text-sm"><i class="fa-solid fa-house mr-1"></i> Beranda</a>
            </div>
        </div>
    </header>
    <main class="flex-grow grid place-items-center px-4 py-10">
        <form method="POST" action="{{ route('login') }}" class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80 w-full max-w-sm space-y-4">
            @csrf
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md"><i class="fa-solid fa-right-to-bracket"></i></div>
                <h2 class="mt-3 text-xl font-extrabold text-slate-900">Login Petugas</h2>
                <p class="text-xs text-slate-500">Masuk untuk kelola aset & BHP</p>
            </div>
            @if($errors->any())<p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">{{ $errors->first() }}</p>@endif
            <label class="block text-xs font-bold text-slate-700 uppercase">Email<input name="email" type="email" value="{{ old('email') }}" placeholder="petugas@sekolah.test" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
            <div><span class="block text-xs font-bold text-slate-700 uppercase">Password</span><div class="relative mt-1"><input id="password" name="password" type="password" placeholder="••••••••" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 pr-10 text-sm outline-none focus:ring-2 focus:ring-blue-500"><button type="button" onclick="const i=document.getElementById('password'),e=document.getElementById('pwIcon');i.type=i.type==='password'?'text':'password';e.className=i.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash'" class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-blue-600" title="Lihat password"><i id="pwIcon" class="fa-solid fa-eye"></i></button></div></div>
            <label class="text-sm text-slate-600 flex gap-2 items-center"><input type="checkbox" name="remember" class="rounded"> Ingat saya</label>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 text-sm font-semibold transition"><i class="fa-solid fa-right-to-bracket mr-1"></i> Masuk Dashboard</button>
        </form>
    </main>
    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            <p>&copy; 2026 Aplikasi Manajemen Aset Sekolah. Hak Cipta Dilindungi. | Dev By Aang Wirawan</p>
        </div>
    </footer>
</body>

</html>