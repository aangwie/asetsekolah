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
                <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">@if($kind === 'mutasi')<th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Aset</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Peminjam</th>@else<th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">KIB</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Tersedia</th>
                    <th class="px-4 py-3 text-right">Nilai</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                @if($kind === 'mutasi')<tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $r->outflow_date?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">{{ $r->asset?->name }}@if(($r->quantity ?? null) !== null)<span class="text-xs text-slate-500"> (pinjam {{ $r->quantity ?? 1 }}, kembali {{ $r->returned_quantity ?? 0 }}, sisa {{ max(0, ($r->quantity ?? 1) - ($r->returned_quantity ?? 0)) }})</span>@endif</td>
                    <td class="px-4 py-3">{{ $r->location_type }}</td>
                    <td class="px-4 py-3">{{ $r->borrower_name ?? '-' }}</td>
                </tr>
                @else<tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $r->asset_code }}</td>
                    <td class="px-4 py-3">{{ $r->name }}</td>
                    <td class="px-4 py-3">KIB {{ $r->kib_type }}</td>
                    <td class="px-4 py-3">{{ $r->location?->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $r->acquisition_date?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format((int) ($r->available ?? $r->quantity ?? 1), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format((float) $r->acquisition_value, 0, ',', '.') }}</td>
                </tr>@endif
                @empty<tr>
                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">Tidak ada data.</td>
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
    <p class="text-sm text-slate-600">Total aset: <strong>{{ $summary['total'] ?? 0 }}</strong> • Total tersedia: <strong>{{ number_format((int) ($summary['units'] ?? 0), 0, ',', '.') }}</strong> • Total nilai: <strong>{{ number_format((float) ($summary['value'] ?? 0), 0, ',', '.') }}</strong></p>
    <ul class="mt-2 text-sm text-slate-600 list-disc ml-5">@foreach(($summary['per_kib'] ?? []) as $t => $s)<li>KIB {{ $t }}: <strong>{{ $s['count'] }}</strong> jenis • {{ number_format((int) ($s['units'] ?? 0), 0, ',', '.') }} tersedia • {{ number_format((float) $s['value'], 0, ',', '.') }}</li>@endforeach</ul>
    <p class="mt-2 text-xs text-slate-500">Hanya tampil barang tersedia. Dihapus / habis dipinjam sembunyi.</p>
    @endif
</div>
@endif
@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    (function($) {
        $(function() {
            $('#reportAssetTable').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "Cari Data:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan.",
                    info: "Halaman _PAGE_ dari _PAGES_ (_TOTAL_ total data)",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Sebelumnya" }
                }
            });
        });
    })(jQuery);
</script>
@endsection