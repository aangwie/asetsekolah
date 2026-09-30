@extends('layouts.app')
@section('title', isset($tx) ? 'Ubah BHP Keluar' : 'Catat BHP Keluar')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
        <div class="flex items-center space-x-2"><i class="fa-solid fa-arrow-right-from-bracket text-amber-600"></i>
            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ isset($tx) ? 'Ubah BHP Keluar' : 'Catat BHP Keluar' }}</h3>
        </div><a href="{{ route('transactions.bhp.keluar') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Lihat daftar penuh</a>
    </div>
    <form method="POST" action="{{ isset($tx) ? route('transactions.bhp.keluar.update', $tx) : route('transactions.bhp.keluar.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        @if(isset($tx))@method('PUT')@endif
        <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-2">Nama BHP @if(isset($tx))<span class="text-slate-400 normal-case">(terkunci)</span>@endif<select id="bhp_item_id" name="bhp_item_id" required @disabled(isset($tx)) class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100">@forelse($items as $i)<option value="{{ $i->id }}" data-stock="{{ (isset($tx) && $tx->bhp_item_id === $i->id) ? $stockAvail : $i->current_stock }}" @selected((int) old('bhp_item_id', isset($tx) ? $tx->bhp_item_id : '') === $i->id)>{{ $i->name }} ({{ $i->code }})</option>@empty<option value="" disabled>Stok kosong — catat BHP Masuk dulu</option>@endforelse</select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal Pengambilan<input name="transaction_date" type="date" value="{{ old('transaction_date', isset($tx) ? $tx->transaction_date->format('Y-m-d') : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Jumlah Pengambilan <span class="float-right normal-case text-blue-600 font-semibold">Stok: <span id="stockAvailNum">0</span></span><input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', isset($tx) ? $tx->quantity : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Sisa Barang<input id="sisa_stock" type="text" readonly tabindex="-1" class="mt-1 w-full bg-slate-100 border border-slate-300 rounded-xl p-2.5 text-sm font-bold outline-none"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Digunakan di (Ruang)<select name="location_id" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach($locations as $l)<option value="{{ $l->id }}" @selected((int) old('location_id', isset($tx) ? $tx->location_id : '') === $l->id)>{{ $l->name }} ({{ $l->code }})</option>@endforeach</select></label>
        <div class="md:col-span-3 flex items-center gap-2"><button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> {{ isset($tx) ? 'Update' : 'Simpan' }}</button>@if(isset($tx))<a href="{{ route('transactions.bhp.keluar.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">Batal</a>@endif</div>
    </form>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-list text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Hasil Input</h3>
    </div>
    <table id="bhpKeluarTable" class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-left">Nama Barang</th>
                <th class="px-4 py-3 text-center">Jumlah</th>
                <th class="px-4 py-3 text-left">Digunakan di</th>
                <th class="px-4 py-3 text-center">Sisa</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>@forelse($txs as $t)<tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3">{{ $t->transaction_date->format('d-m-Y') }}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold">{{ $t->item->name }}</div>
                    <div class="font-mono text-xs text-blue-600">{{ $t->item->code }}</div>
                </td>
                <td class="px-4 py-3 text-center">{{ $t->quantity }}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold">{{ $t->location->name ?? '-' }}</div>
                    <div class="font-mono text-xs text-slate-500">{{ $t->location->code ?? '' }}</div>
                </td>
                <td class="px-4 py-3 text-center font-bold">{{ $t->item->current_stock }}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('transactions.bhp.keluar.edit', $t) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200" title="Ubah"><i class="fa-solid fa-pencil text-xs"></i></a>
                    <form method="POST" action="{{ route('transactions.bhp.keluar.destroy', $t) }}" class="inline" onsubmit="return confirmDelete(event, this)">@csrf @method('DELETE')<button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 ml-1" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button></form>
                </td>
            </tr>@empty<tr>
                <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
            </tr>@endforelse</tbody>
    </table>
</div>
<script>
    const sel = document.getElementById('bhp_item_id'),
        qt = document.getElementById('quantity'),
        av = document.getElementById('stockAvailNum'),
        ss = document.getElementById('sisa_stock');

    function stockNow() {
        const o = sel.options[sel.selectedIndex];
        return o ? parseInt(o.dataset.stock || '0', 10) : 0;
    }

    function syncStock() {
        const s = stockNow();
        av.textContent = s;
        qt.max = s;
        const q = parseInt(qt.value, 10) || 0;
        ss.value = s - q;
        ss.classList.toggle('text-red-600', s - q < 0);
    }
    sel.addEventListener('change', syncStock);
    qt.addEventListener('input', syncStock);
    syncStock();

    function confirmDelete(e, f) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus data?',
            text: 'Stok kembali seperti semula.',
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
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function($) {
        $(function() {
            $('#bhpKeluarTable').DataTable({
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
                },
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    })(jQuery);
</script>
@endsection