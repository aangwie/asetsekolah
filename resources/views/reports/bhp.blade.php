@extends('layouts.app')
@section('title', 'Laporan BHP')
@section('content')
@include('reports._filter', ['action' => route('reports.bhp.index'), 'filter' => $filter, 'defaultView' => 'lengkap', 'yearNow' => $yearNow])
@if($filter)
@if($filter['view'] === 'lengkap')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-box-open text-rose-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Laporan BHP — Lengkap</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="reportBhpTable">
            <thead>
                <tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                    <th class="py-2 pr-3">Tanggal</th>
                    <th class="py-2 pr-3">Barang</th>
                    <th class="py-2 pr-3">Tipe</th>
                    <th class="py-2 pr-3 text-right">Qty</th>
                    <th class="py-2 pr-3">Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)<tr class="border-b border-slate-100">
                    <td class="py-2 pr-3">{{ $r->transaction_date?->format('Y-m-d') }}</td>
                    <td class="py-2 pr-3">{{ $r->item?->name }}</td>
                    <td class="py-2 pr-3">{{ $r->type === 'in' ? 'Masuk' : 'Keluar' }}</td>
                    <td class="py-2 pr-3 text-right">{{ $r->quantity }}</td>
                    <td class="py-2 pr-3">{{ $r->location?->name ?? '-' }}</td>
                </tr>
                @empty<tr>
                    <td colspan="5" class="py-4 text-center text-slate-400">Tidak ada data.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-chart-pie text-emerald-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Laporan BHP — Rekap</h3>
    </div>
    <p class="text-sm text-slate-600">Total masuk: <strong>{{ $summary['total_in'] ?? 0 }}</strong> • Total keluar: <strong>{{ $summary['total_out'] ?? 0 }}</strong></p>
    <ul class="mt-2 text-sm text-slate-600 list-disc ml-5">@foreach(($summary['per_item'] ?? []) as $s)<li>{{ $s['name'] }}: masuk <strong>{{ $s['in'] }}</strong> • keluar <strong>{{ $s['out'] }}</strong></li>@endforeach</ul>
</div>
@endif
@endif
@endsection