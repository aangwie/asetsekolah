@extends('layouts.app')
@section('title', $label)
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 text-center py-16">
    <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xl"><i class="fa-solid fa-hourglass-half"></i></div>
    <h2 class="mt-3 text-xl font-extrabold text-slate-900">{{ $label }}</h2>
    <p class="text-sm text-slate-500 mt-1">Modul segera hadir. Alur menyusul.</p>
</div>
@endsection