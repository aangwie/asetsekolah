@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Panel Admin</span><span>&bull;</span><span>Ringkasan</span></div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard Aset & BHP</h2>
        <p class="text-sm text-slate-500 mt-1">Rekap unit aset tetap, nilai perolehan, dan stok barang habis pakai.</p>
    </div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
    <div class="bg-gradient-to-br from-blue-900 to-blue-800 text-white p-4 rounded-2xl shadow-sm border border-blue-900">
        <div class="flex items-center justify-between"><span class="text-[11px] font-medium uppercase tracking-wider text-blue-200">Total Aset</span>
            <div class="w-7 h-7 rounded-lg bg-blue-700/60 flex items-center justify-center text-xs text-white"><i class="fa-solid fa-cubes"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-2xl font-black leading-none">{{ $totalAssets }}</h4>
            <p class="text-[11px] text-blue-200 mt-1">Seluruh Unit</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between"><span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Gedung</span>
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs"><i class="fa-solid fa-building"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-xl font-bold text-slate-900 leading-none">{{ $totalBuildings }}</h4>
            <p class="text-[10px] text-slate-500 mt-1">Gedung Sekolah</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between"><span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Ruang</span>
            <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs"><i class="fa-solid fa-door-open"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-xl font-bold text-slate-900 leading-none">{{ $totalRooms }}</h4>
            <p class="text-[10px] text-slate-500 mt-1">Ruangan</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between"><span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Nilai Aset</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs"><i class="fa-solid fa-rupiah-sign"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-lg font-bold text-slate-900 leading-none">Rp {{ number_format($totalValue, 0, ',', '.') }}</h4>
            <p class="text-[10px] text-slate-500 mt-1">Total Perolehan</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between"><span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Dipinjam</span>
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs"><i class="fa-solid fa-hand-holding-hand"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-xl font-bold text-slate-900 leading-none">{{ $borrowed }}</h4>
            <p class="text-[10px] text-slate-500 mt-1">Belum Kembali</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex items-center justify-between"><span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Stok Menipis</span>
            <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="mt-3">
            <h4 class="text-xl font-bold text-rose-600 leading-none">{{ $lowStock }}</h4>
            <p class="text-[10px] text-slate-500 mt-1">Item BHP</p>
        </div>
    </div>
</div>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider mb-3"><i class="fa-solid fa-hand-holding-hand text-blue-600 mr-1"></i> Peminjaman Aktif</h3>
        <div class="overflow-x-auto">
            <table id="activeLoanTable" class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                        <th class="px-4 py-3 text-left">Nama Aset</th>
                        <th class="px-4 py-3 text-left">Peminjam</th>
                        <th class="px-4 py-3 text-left">Tgl Pinjam</th>
                    </tr>
                </thead>
                <tbody>@forelse($activeOutflows as $o)<tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3"><div class="font-semibold">{{ $o->asset->name ?? '-' }}</div><div class="font-mono text-xs text-blue-600">{{ $o->asset->asset_code ?? '-' }}</div></td>
                        <td class="px-4 py-3">{{ $o->borrower_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $o->loan_date?->format('d-m-Y') ?? '-' }}</td>
                    </tr>@empty<tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Kosong.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider mb-3"><i class="fa-solid fa-cubes text-blue-600 mr-1"></i> Stok Menipis</h3>@forelse($lowItems as $i)<p class="text-sm py-1 border-b border-slate-100">{{ $i->name }} ({{ $i->current_stock }}/{{ $i->minimum_stock }})</p>@empty<p class="text-sm text-slate-500">Aman.</p>@endforelse
    </div>
</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    (function($) {
        $(function() {
            $('#activeLoanTable').DataTable({
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