@extends('layouts.app')
@section('title', 'Aset Masuk')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
<div><div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Transaksi</span><span>&bull;</span><span>Aset Masuk</span></div>
<h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Aset Masuk</h2>
<p class="text-sm text-slate-500 mt-1">Pencatatan perolehan aset tetap KIB A–E.</p></div>
<a href="{{ route('transactions.asset.masuk.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm"><i class="fa-solid fa-plus mr-2"></i> Catat Aset Masuk</a>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase"><th class="px-4 py-3 text-left">Tahun</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Kode</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-right">Harga</th><th class="px-4 py-3 text-left">Dana</th></tr></thead>
<tbody>@forelse($assets as $a)<tr class="border-b border-slate-100 hover:bg-slate-50"><td class="px-4 py-3">{{ $a->acquisition_date->format('Y') }}</td><td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">KIB {{ $a->kib_type }}</span></td><td class="px-4 py-3 font-mono text-xs font-bold text-blue-600">{{ $a->asset_code }}</td><td class="px-4 py-3 font-semibold">{{ $a->name }}</td><td class="px-4 py-3 text-right">Rp {{ number_format($a->acquisition_value, 0, ',', '.') }}</td><td class="px-4 py-3">{{ $a->funding_source ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
</table></div>
<div class="mt-4">{{ $assets->links() }}</div>
@endsection
