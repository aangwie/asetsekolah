@extends('layouts.app')
@section('title', 'Catat Aset Masuk')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 max-w-lg">
<div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-arrow-right-to-bracket text-blue-600"></i><h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Catat Aset Masuk</h3></div>
<form method="POST" action="{{ route('transactions.asset.masuk.store') }}" class="space-y-4">
@csrf
<label class="block text-xs font-bold text-slate-700 uppercase">Tahun Perolehan<select name="procurement_year" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@for($y = $yearNow; $y >= 1990; $y--)<option value="{{ $y }}" @selected(old('procurement_year') == $y)>{{ $y }}</option>@endfor</select></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Jenis Aset<select name="kib_type" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach(['A' => 'KIB A – Tanah', 'B' => 'KIB B – Peralatan & Mesin', 'C' => 'KIB C – Gedung & Bangunan', 'D' => 'KIB D – Jalan, Irigasi & Jaringan', 'E' => 'KIB E – Aset Tetap Lainnya'] as $v => $l)<option value="{{ $v }}" @selected(old('kib_type') === $v)>{{ $l }}</option>@endforeach</select></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Kode Barang<input name="asset_code" value="{{ old('asset_code') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500" placeholder="AST-2026-KIBB-0012"></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Nama Barang<input name="name" value="{{ old('name') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Harga Perolehan (Rp)<input name="acquisition_value" type="number" min="0" step="1" value="{{ old('acquisition_value') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Sumber Dana<select name="funding_source" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach(['BOS', 'DAK', 'HIBAH', 'Komite'] as $s)<option value="{{ $s }}" @selected(old('funding_source') === $s)>{{ $s }}</option>@endforeach</select></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Lokasi (opsional)<select name="location_id" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"><option value="">—</option>@foreach($locations as $loc)<option value="{{ $loc->id }}" @selected(old('location_id') == $loc->id)>{{ $loc->name }} ({{ $loc->building->name ?? 'Tanpa Gedung' }})</option>@endforeach</select></label>
<button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> Simpan</button>
</form>
</div>
@endsection
