@extends('layouts.app')
@section('title', 'Web')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Pengaturan</span><span>&bull;</span><span>Users</span><span>&bull;</span><span>Web</span></div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Web</h2>
        <p class="text-sm text-slate-500 mt-1">Update GitHub, storage, migrasi, database, cache. Semua aksi tercatat di log terminal.</p>
    </div>
    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-mono">{{ $branch }}</span>
</div>
@if(session('web'))<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm"><i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('web') }}</div>@endif
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
        <div class="flex items-center space-x-2 border-b border-slate-100 pb-3"><i class="fa-brands fa-github text-slate-800"></i><h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Update dari GitHub</h3></div>
        <dl class="text-xs text-slate-600 space-y-1 font-mono break-all"><div><dt class="inline font-bold">Remote: </dt><dd class="inline">{{ $remote }}</dd></div><div><dt class="inline font-bold">Commit: </dt><dd class="inline whitespace-pre-line">{{ $commits }}</dd></div><div><dt class="inline font-bold">Token: </dt><dd class="inline">{{ $tokenMasked }} @if($hasToken)<span class="text-emerald-600 font-sans font-bold">tersimpan</span>@endif</dd></div></dl>
        <form method="POST" action="{{ route('web-settings.token') }}" class="flex flex-col sm:flex-row gap-2">@csrf
            <input type="password" name="github_token" placeholder="github_pat_xxx atau ghp_xxx" pattern="(github_pat_|ghp_)[A-Za-z0-9_]{10,}" title="Format: github_pat_xxx atau ghp_xxx" autocomplete="off" class="flex-1 bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500">
            <button class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium transition whitespace-nowrap"><i class="fa-solid fa-key mr-1"></i> Simpan Token</button>
        </form>
        @if(!$hasToken)<p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Private repo: isi token classic <code>repo</code> read-only dulu, baru Pull.</p>@endif
        <form method="POST" action="{{ route('web-settings.pull') }}" onsubmit="return confirm('Pull update dari GitHub sekarang?')">@csrf<button class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-cloud-arrow-down mr-1"></i> Pull & Update Sekarang</button></form>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
        <div class="flex items-center space-x-2 border-b border-slate-100 pb-3"><i class="fa-solid fa-server text-emerald-600"></i><h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Storage & Migrasi</h3></div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <form method="POST" action="{{ route('web-settings.symlink') }}">@csrf<button class="w-full px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition"><i class="fa-solid fa-link mr-1"></i> Symlink</button></form>
            <form method="POST" action="{{ route('web-settings.migrate') }}" onsubmit="return confirm('Jalankan migrasi table?')">@csrf<button class="w-full px-3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition"><i class="fa-solid fa-database mr-1"></i> Migrasi</button></form>
            <form method="POST" action="{{ route('web-settings.clear') }}">@csrf<button class="w-full px-3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition"><i class="fa-solid fa-broom mr-1"></i> Clear Cache</button></form>
        </div>
        <p class="text-xs text-slate-500">Symlink: buat link public/storage. Migrasi: migrate --force. Clear: optimize:clear.</p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
        <div class="flex items-center space-x-2 border-b border-slate-100 pb-3"><i class="fa-solid fa-hard-drive text-blue-600"></i><h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Database ({{ $db }})</h3></div>
        <p class="text-xs text-slate-500 font-mono break-all">{{ $dbName }}</p>
        <a href="{{ route('web-settings.export') }}" class="block text-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-download mr-1"></i> Export / Backup</a>
        <form method="POST" action="{{ route('web-settings.import') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2" onsubmit="return confirm('Import menimpa data! Lanjut?')">@csrf
            <input type="file" name="sql_file" accept=".sql,.txt,.dump" required class="flex-1 bg-slate-50 border border-slate-300 rounded-xl p-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            <button class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition whitespace-nowrap"><i class="fa-solid fa-upload mr-1"></i> Import</button>
        </form>
        @if($backups->count())<ul class="text-xs text-slate-600 space-y-1">@foreach($backups as $b)<li class="flex justify-between font-mono bg-slate-50 px-2 py-1 rounded"><span>{{ $b['name'] }}</span><span class="text-slate-400">{{ $b['size'] }}</span></li>@endforeach</ul>@endif
    </div>
    <div class="bg-slate-900 p-4 rounded-2xl shadow-sm border border-slate-800 flex flex-col">
        <div class="flex items-center justify-between pb-2 border-b border-slate-700 mb-2">
            <h3 class="font-bold text-slate-200 text-sm uppercase tracking-wider"><i class="fa-solid fa-terminal mr-1"></i> Log Terminal</h3>
            <form method="POST" action="{{ route('web-settings.clear-log') }}">@csrf<button class="text-xs text-slate-400 hover:text-white transition"><i class="fa-solid fa-trash mr-1"></i>Bersihkan</button></form>
        </div>
        <pre id="weblog" class="flex-1 min-h-[220px] max-h-[320px] overflow-y-auto text-[11px] leading-relaxed font-mono text-emerald-300 whitespace-pre-wrap break-all">{{ $log ?: '(belum ada aktivitas)' }}</pre>
    </div>
</div>
@endsection
@section('scripts')
<script>var el = document.getElementById('weblog'); if (el) el.scrollTop = el.scrollHeight;</script>
@endsection
