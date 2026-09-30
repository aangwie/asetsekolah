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
                <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)<tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $r->transaction_date?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">{{ $r->item?->name }}</td>
                    <td class="px-4 py-3">{{ $r->type === 'in' ? 'Masuk' : 'Keluar' }}</td>
                    <td class="px-4 py-3 text-right">{{ $r->quantity }}</td>
                    <td class="px-4 py-3">{{ $r->location?->name ?? '-' }}</td>
                </tr>
                @empty<tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">Tidak ada data.</td>
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
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    (function($) {
        $(function() {
            $('#reportBhpTable').DataTable({
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