@extends('layouts.app')
@section('title', $title)
@section('content')
@include('reports._filter', ['action' => $action, 'filter' => $filter, 'defaultView' => $defaultView, 'yearNow' => $yearNow])
@if($filter)
@if($filter['view'] === 'lengkap')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-table-list text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ $title }} — Lengkap</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="reportAssetTable">
            <thead>
                <tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">@if($kind === 'mutasi')<th class="py-2 pr-3">Tanggal</th>
                    <th class="py-2 pr-3">Aset</th>
                    <th class="py-2 pr-3">Tipe</th>
                    <th class="py-2 pr-3">Peminjam</th>@else<th class="py-2 pr-3">Kode</th>
                    <th class="py-2 pr-3">Nama</th>
                    <th class="py-2 pr-3">KIB</th>
                    <th class="py-2 pr-3">Lokasi</th>
                    <th class="py-2 pr-3">Tanggal</th>
                    <th class="py-2 pr-3 text-right">Nilai</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                @if($kind === 'mutasi')<tr class="border-b border-slate-100">
                    <td class="py-2 pr-3">{{ $r->outflow_date?->format('Y-m-d') }}</td>
                    <td class="py-2 pr-3">{{ $r->asset?->name }}</td>
                    <td class="py-2 pr-3">{{ $r->location_type }}</td>
                    <td class="py-2 pr-3">{{ $r->borrower_name ?? '-' }}</td>
                </tr>
                @else<tr class="border-b border-slate-100">
                    <td class="py-2 pr-3">{{ $r->asset_code }}</td>
                    <td class="py-2 pr-3">{{ $r->name }}</td>
                    <td class="py-2 pr-3">KIB {{ $r->kib_type }}</td>
                    <td class="py-2 pr-3">{{ $r->location?->name ?? '-' }}</td>
                    <td class="py-2 pr-3">{{ $r->acquisition_date?->format('Y-m-d') }}</td>
                    <td class="py-2 pr-3 text-right">{{ number_format((float) $r->acquisition_value, 0, ',', '.') }}</td>
                </tr>@endif
                @empty<tr>
                    <td colspan="6" class="py-4 text-center text-slate-400">Tidak ada data.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-chart-pie text-emerald-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ $title }} — Rekap</h3>
    </div>
    @if($kind === 'mutasi')
    <p class="text-sm text-slate-600">Total mutasi: <strong>{{ $summary['total'] ?? 0 }}</strong></p>
    <ul class="mt-2 text-sm text-slate-600 list-disc ml-5">@foreach(($summary['per_type'] ?? []) as $t => $c)<li>{{ $t }}: <strong>{{ $c }}</strong></li>@endforeach</ul>
    @else
    <p class="text-sm text-slate-600">Total aset: <strong>{{ $summary['total'] ?? 0 }}</strong> • Total nilai: <strong>{{ number_format((float) ($summary['value'] ?? 0), 0, ',', '.') }}</strong></p>
    <ul class="mt-2 text-sm text-slate-600 list-disc ml-5">@foreach(($summary['per_kib'] ?? []) as $t => $s)<li>KIB {{ $t }}: <strong>{{ $s['count'] }}</strong> • {{ number_format((float) $s['value'], 0, ',', '.') }}</li>@endforeach</ul>
    @endif
</div>
@endif
@endif
@endsection