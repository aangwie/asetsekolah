@extends('layouts.app')
@section('title', isset($tx) ? 'Ubah BHP Masuk' : 'Catat BHP Masuk')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
        <div class="flex items-center space-x-2"><i class="fa-solid fa-arrow-right-to-bracket text-emerald-600"></i>
            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ isset($tx) ? 'Ubah BHP Masuk' : 'Catat BHP Masuk' }}</h3>
        </div><a href="{{ route('transactions.bhp.masuk') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Lihat daftar penuh</a>
    </div>
    <form method="POST" id="bhpMasukForm" onsubmit="return stripRupiah()" action="{{ isset($tx) ? route('transactions.bhp.masuk.update', $tx) : route('transactions.bhp.masuk.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        @if(isset($tx))@method('PUT')@endif
        <label class="block text-xs font-bold text-slate-700 uppercase">Tahun Perolehan<select id="procurement_year" name="procurement_year" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@for($y = $yearNow; $y >= 1990; $y--)<option value="{{ $y }}" @selected(old('procurement_year', isset($tx) ? $tx->transaction_date->format('Y') : null) == $y)>{{ $y }}</option>@endfor</select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal<input id="transaction_date" name="transaction_date" type="date" value="{{ old('transaction_date', isset($tx) ? $tx->transaction_date->format('Y-m-d') : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Kategori<select name="category" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach(['ATK', 'Kebersihan', 'Kesehatan', 'Pemeliharaan', 'Lainnya'] as $c)<option value="{{ $c }}" @selected(old('category', isset($tx) ? $tx->item->category : null) === $c)>{{ $c }}</option>@endforeach</select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-3">Nama Barang<input name="name" value="{{ old('name', isset($tx) ? $tx->item->name : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Harga Satuan (Rp)<input id="unit_price" name="unit_price" type="text" inputmode="numeric" value="{{ old('unit_price', isset($tx) ? $tx->item->unit_price : '') }}" required placeholder="5.000" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Jumlah<input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', isset($tx) ? $tx->quantity : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Total (Rp)<input id="total_price" type="text" readonly tabindex="-1" class="mt-1 w-full bg-slate-100 border border-slate-300 rounded-xl p-2.5 text-sm font-bold outline-none"></label>
        <div class="md:col-span-3 flex items-center gap-2"><button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> {{ isset($tx) ? 'Update' : 'Simpan' }}</button>@if(isset($tx))<a href="{{ route('transactions.bhp.masuk.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">Batal</a>@endif</div>
    </form>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-list text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Hasil Input</h3>
    </div>
    <table id="bhpMasukTable" class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-left">Nama Barang</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-right">Harga Satuan</th>
                <th class="px-4 py-3 text-center">Jumlah</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>@forelse($txs as $t)<tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3">{{ $t->transaction_date->format('d-m-Y') }}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold">{{ $t->item->name }}</div>
                    <div class="font-mono text-xs text-blue-600">{{ $t->item->code }}</div>
                </td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">{{ $t->item->category }}</span></td>
                <td class="px-4 py-3 text-right">Rp {{ number_format($t->item->unit_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-center">{{ $t->quantity }}</td>
                <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($t->item->unit_price * $t->quantity, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('transactions.bhp.masuk.edit', $t) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200" title="Ubah"><i class="fa-solid fa-pencil text-xs"></i></a>
                    <form method="POST" action="{{ route('transactions.bhp.masuk.destroy', $t) }}" class="inline" onsubmit="return confirmDelete(event, this)">@csrf @method('DELETE')<button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 ml-1" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button></form>
                </td>
            </tr>@empty<tr>
                <td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
            </tr>@endforelse</tbody>
    </table>
</div>
<script>
    const py = document.getElementById('procurement_year'),
        td = document.getElementById('transaction_date');
    py.addEventListener('change', () => {
        if (!td.value || !td.value.startsWith(py.value)) td.value = py.value + (td.value ? td.value.slice(4) : '-01-01');
    });
    td.addEventListener('change', () => {
        if (td.value) py.value = td.value.slice(0, 4);
    });
    const up = document.getElementById('unit_price'),
        qt = document.getElementById('quantity'),
        tt = document.getElementById('total_price');
    const fmtRp = v => v.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    function calcTotal() {
        const p = parseInt(up.value.replace(/\./g, ''), 10) || 0,
            q = parseInt(qt.value, 10) || 0;
        tt.value = fmtRp(String(p * q));
    }
    if (up) {
        if (up.value) up.value = fmtRp(up.value);
        up.addEventListener('input', () => {
            up.value = fmtRp(up.value);
            calcTotal();
        });
    }
    if (qt) qt.addEventListener('input', calcTotal);
    calcTotal();

    function stripRupiah() {
        if (up) up.value = up.value.replace(/\./g, '');
        return true;
    }

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
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function($) {
        $(function() {
            $('#bhpMasukTable').DataTable({
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
</script>
@endsection