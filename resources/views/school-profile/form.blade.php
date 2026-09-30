@extends('layouts.app')
@section('title', 'Data Sekolah')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-school text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Data Sekolah</h3>
    </div>
    <form method="POST" action="{{ route('school-profile.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')
        <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-2">Nama Sekolah<input name="school_name" value="{{ old('school_name', $profile->school_name) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">NPSN<input name="npsn" value="{{ old('npsn', $profile->npsn) }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-2">Alamat<textarea name="address" rows="3" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $profile->address) }}</textarea></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Logo Pemkab <span class="text-slate-400 normal-case">(gambar, maks 500KB, jadi .webp)</span>@if($profile->logo_pemkab_path)<img src="{{ asset('storage/'.$profile->logo_pemkab_path) }}" alt="Logo Pemkab" class="mt-2 h-20 w-auto object-contain bg-slate-50 border border-slate-200 rounded-xl p-2">@endif<input name="logo_pemkab" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Logo Sekolah <span class="text-slate-400 normal-case">(gambar, maks 500KB, jadi .webp)</span>@if($profile->logo_sekolah_path)<img src="{{ asset('storage/'.$profile->logo_sekolah_path) }}" alt="Logo Sekolah" class="mt-2 h-20 w-auto object-contain bg-slate-50 border border-slate-200 rounded-xl p-2">@endif<input name="logo_sekolah" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <div class="md:col-span-2"><button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> Update</button></div>
    </form>
</div>
@endsection