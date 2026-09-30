@extends('layouts.app')
@section('title', 'Aset Keluar')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Transaksi</span><span>&bull;</span><span>Aset Keluar</span></div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Aset Keluar</h2>
        <p class="text-sm text-slate-500 mt-1">Pemakaian / peminjaman aset dalam & luar sekolah.</p>
    </div>
    <a href="{{ route('transactions.asset.keluar.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm"><i class="fa-solid fa-plus mr-2"></i> Catat Aset Keluar</a>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <table id="assetKeluarIndexTable" class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                <th class="px-4 py-3 text-left">Tanggal Keluar</th>
                <th class="px-4 py-3 text-left">Nama Barang</th>
                <th class="px-4 py-3 text-left">Jml</th>
                <th class="px-4 py-3 text-left">Lokasi</th>
                <th class="px-4 py-3 text-left">Penanggung Jawab</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>@forelse($outflows as $o)<tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3">{{ $o->outflow_date->format('d-m-Y') }}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold">{{ $o->asset->name }}</div>
                    <div class="font-mono text-xs text-blue-600">{{ $o->asset->asset_code }}</div>
                </td>
                <td class="px-4 py-3">{{ $o->location_type === 'Luar Sekolah' ? ($o->quantity ?? 1) : '-' }}</td>
                <td class="px-4 py-3">@if($o->location_type === 'Luar Sekolah')<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">Luar Sekolah</span>@else<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">Sekolah</span>@endif</td>
                <td class="px-4 py-3">@if($o->location_type === 'Luar Sekolah')<div class="font-semibold">{{ $o->borrower_name }}</div>
                    <div class="text-xs text-slate-500">Pinjam: {{ $o->loan_date->format('d-m-Y') }}</div>@if($o->return_date)<div class="text-xs text-emerald-600">Kembali: {{ $o->return_date->format('d-m-Y') }}</div>@else<div class="text-xs text-amber-600">Belum kembali</div>@endif<span class="text-slate-300">-</span>@endif
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('transactions.asset.keluar.edit', $o) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200" title="Ubah"><i class="fa-solid fa-pencil text-xs"></i></a>
                    <form method="POST" action="{{ route('transactions.asset.keluar.destroy', $o) }}" class="inline" onsubmit="return confirmDelete(event, this)">@csrf @method('DELETE')<button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 ml-1" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button></form>
                </td>
            </tr>@empty<tr>
                <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
            </tr>@endforelse</tbody>
    </table>
</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function($) {
        $(function() {
            $('#assetKeluarIndexTable').DataTable({
                searching: true,
                paging: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Semua']
                ],
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
                }
            });
        });
    })(jQuery);

    function confirmDelete(e, f) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus data?',
            text: 'Data terhapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(r => {
            if (r.isConfirmed) f.submit();
        });
        return false;
    }
</script>
@endsection