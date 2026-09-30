@extends('layouts.app')
@section('title', ($user->exists ? 'Edit' : 'Tambah').' User')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 max-w-lg">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-user text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ $user->exists ? 'Edit' : 'Tambah' }} User</h3>
    </div>
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="space-y-4">
        @csrf @if($user->exists) @method('PUT') @endif
        <label class="block text-xs font-bold text-slate-700 uppercase">Nama<input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Email<input name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Password{{ $user->exists ? ' (kosongkan = tetap)' : '' }}<input name="password" type="password" {{ $user->exists ? '' : 'required' }} class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Role<select name="role" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach($roles as $r)<option value="{{ $r }}" @selected(old('role', $user->roles->first()?->name) === $r)>{{ $r }}</option>@endforeach</select></label>
        <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> Simpan</button>
    </form>
</div>
@endsection