@extends('layouts.app')
@section('title', ($location->exists ? 'Edit' : 'Tambah').' Ruangan')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 max-w-lg">
<div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-door-open text-blue-600"></i><h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ $location->exists ? 'Edit' : 'Tambah' }} Ruangan</h3></div>
<form method="POST" action="{{ $location->exists ? route('locations.update', $location) : route('locations.store') }}" class="space-y-4">
@csrf @if($location->exists) @method('PUT') @endif
<label class="block text-xs font-bold text-slate-700 uppercase">Kode<input name="code" value="{{ old('code', $location->code) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-500" placeholder="LAB-IPA-01"></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Nama Ruangan<input name="name" value="{{ old('name', $location->name) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
<label class="block text-xs font-bold text-slate-700 uppercase">Gedung<select name="building_id" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"><option value="">— Pilih Gedung —</option>@foreach($buildings as $b)<option value="{{ $b->id }}" @selected((string) old('building_id', $location->building_id) === (string) $b->id)>{{ $b->code }} — {{ $b->name }}</option>@endforeach</select></label>
@if($location->exists && $location->building)<p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"><i class="fa-solid fa-building mr-1 text-blue-600"></i> Gedung saat ini: <b>{{ $location->building->code }} — {{ $location->building->name }}</b></p>@endif
<label class="block text-xs font-bold text-slate-700 uppercase">PIC<select name="pic_user_id" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"><option value="">— Tanpa PIC —</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected((string) old('pic_user_id', $location->pic_user_id) === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach</select></label>
<button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> Simpan</button>
</form>
</div>
@endsection
