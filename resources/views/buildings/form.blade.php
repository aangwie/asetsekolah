@extends('layouts.app')
@section('title', ($building->exists ? 'Edit' : 'Tambah').' Gedung')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 max-w-lg">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-building text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ $building->exists ? 'Edit' : 'Tambah' }} Gedung</h3>
    </div>
    <form method="POST" action="{{ $building->exists ? route('buildings.update', $building) : route('buildings.store') }}" class="space-y-4">
        @csrf @if($building->exists) @method('PUT') @endif
        <label class="block text-xs font-bold text-slate-700 uppercase">Kode<input name="code" value="{{ old('code', $building->code) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500" placeholder="GDG-A"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Nama Gedung<input name="name" value="{{ old('name', $building->name) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500" placeholder="Gedung A"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Keterangan<textarea name="description" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $building->description) }}</textarea></label>
        <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> Simpan</button>
    </form>
</div>
@endsection