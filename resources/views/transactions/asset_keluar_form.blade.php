@extends('layouts.app')
@section('title', isset($outflow) ? 'Ubah Aset Keluar' : 'Catat Aset Keluar')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
        <div class="flex items-center space-x-2"><i class="fa-solid fa-arrow-right-from-bracket text-red-500"></i>
            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">{{ isset($outflow) ? 'Ubah Aset Keluar' : 'Catat Aset Keluar' }}</h3>
        </div><a href="{{ route('transactions.asset.keluar') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Lihat daftar penuh</a>
    </div>
    <form method="POST" action="{{ isset($outflow) ? route('transactions.asset.keluar.update', $outflow) : route('transactions.asset.keluar.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        @if(isset($outflow))@method('PUT')@endif
        <label class="block text-xs font-bold text-slate-700 uppercase">Nama Barang<select id="asset_id" name="asset_id" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" data-tgl="" data-stock="">— Pilih aset —</option>@foreach($assets as $a)<option value="{{ $a->id }}" data-tgl="{{ $a->acquisition_date?->format('d-m-Y') }}" data-stock="{{ $stockMap[$a->id] ?? $a->quantity ?? 1 }}" @selected(old('asset_id', isset($outflow) ? $outflow->asset_id : null) == $a->id)>{{ $a->name }} ({{ $a->asset_code }})</option>@endforeach
            </select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal Perolehan<input id="acq_date" type="text" readonly tabindex="-1" placeholder="— pilih barang —" class="mt-1 w-full bg-slate-100 border border-slate-200 rounded-xl p-2.5 text-sm text-slate-600 outline-none"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal Keluar<input name="outflow_date" type="date" value="{{ old('outflow_date', isset($outflow) ? $outflow->outflow_date->format('Y-m-d') : '') }}" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Lokasi<select id="location_type" name="location_type" required class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@foreach(['Sekolah', 'Luar Sekolah'] as $l)<option value="{{ $l }}" @selected(old('location_type', isset($outflow) ? $outflow->location_type : null) === $l)>{{ $l }}</option>@endforeach</select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-2">Keterangan (opsional)<input name="notes" value="{{ old('notes', isset($outflow) ? $outflow->notes : '') }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <div id="luarFields" class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
            <label class="block text-xs font-bold text-slate-700 uppercase">Jumlah Aset Saat Ini<input id="stock_now" type="text" readonly tabindex="-1" placeholder="— pilih barang —" class="mt-1 w-full bg-slate-100 border border-slate-200 rounded-xl p-2.5 text-sm text-slate-600 outline-none"></label>
            <label class="block text-xs font-bold text-slate-700 uppercase">Jumlah Dipinjam<input name="quantity" type="number" min="1" value="{{ old('quantity', isset($outflow) ? $outflow->quantity ?? 1 : 1) }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@error('quantity')<span class="text-red-600 normal-case font-medium">{{ $message }}</span>@enderror</label>
            <label class="block text-xs font-bold text-slate-700 uppercase">Nama Penanggung Jawab<input name="borrower_name" value="{{ old('borrower_name', isset($outflow) ? $outflow->borrower_name : '') }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
            <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal Pinjam<input name="loan_date" type="date" value="{{ old('loan_date', isset($outflow) && $outflow->loan_date ? $outflow->loan_date->format('Y-m-d') : '') }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
            <label class="block text-xs font-bold text-slate-700 uppercase md:col-span-3"><span class="inline-flex items-center gap-2 normal-case font-semibold"><input id="has_return" name="has_return" type="checkbox" value="1" @checked(old('has_return', isset($outflow) && ($outflow->returnedQty() > 0 || $outflow->return_date))) class="w-4 h-4 accent-blue-600"> Sudah ada pengembalian (centang dulu, lalu isi jumlah + tanggal)</span></label>
            <label class="block text-xs font-bold text-slate-700 uppercase">Jumlah Dikembalikan<input id="returned_qty" name="returned_quantity" type="number" min="0" value="{{ old('returned_quantity', isset($outflow) ? ($outflow->returned_quantity ?? '') : '') }}" placeholder="cth: 2" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@error('returned_quantity')<span class="block text-red-600 normal-case font-medium mt-1">{{ $message }}</span>@enderror</label>
            <label class="block text-xs font-bold text-slate-700 uppercase">Tanggal Kembali<input id="return_date" name="return_date" type="date" value="{{ old('return_date', isset($outflow) && $outflow->return_date ? $outflow->return_date->format('Y-m-d') : '') }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@error('return_date')<span class="block text-red-600 normal-case font-medium mt-1">{{ $message }}</span>@enderror</label>
            <div class="block text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-2.5">Sisa belum kembali: <strong id="sisa_info">-</strong><br>Stok tersedia setelah simpan: <strong id="stock_after_info">-</strong><span class="block text-[11px] text-slate-500 mt-1">Stok saat ini sudah termasuk akumulasi retur lain.</span></div>
        </div>
        <div class="md:col-span-3 flex items-center gap-2"><button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-floppy-disk mr-2"></i> {{ isset($outflow) ? 'Update' : 'Simpan' }}</button>@if(isset($outflow))<a href="{{ route('transactions.asset.keluar.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">Batal</a>@endif</div>
    </form>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-list text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Hasil Input</h3>
    </div>
    <table id="assetKeluarTable" class="w-full text-sm">
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
                <td class="px-4 py-3">{{ $o->location_type === 'Luar Sekolah' ? (($o->quantity ?? 1).($o->returnedQty() > 0 ? ' (kembali '.$o->returnedQty().', sisa '.$o->outstandingQty().')' : '')) : '-' }}</td>
                <td class="px-4 py-3">@if($o->location_type === 'Luar Sekolah')<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">Luar Sekolah</span>@else<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">Sekolah</span>@endif</td>
                <td class="px-4 py-3">@if($o->location_type === 'Luar Sekolah')<div class="font-semibold">{{ $o->borrower_name }}</div>
                    <div class="text-xs text-slate-500">Pinjam: {{ $o->loan_date->format('d-m-Y') }}</div>
                    @if($o->returnedQty() > 0)<div class="text-xs text-emerald-600">Kembali {{ $o->returnedQty() }}@if($o->return_date) ({{ $o->return_date->format('d-m-Y') }})@endif</div>@endif
                    @if($o->outstandingQty() > 0)<div class="text-xs text-amber-600">Sisa {{ $o->outstandingQty() }} belum kembali</div>@else<div class="text-xs text-emerald-600">Lunas kembali</div>@endif
                    @endif
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('transactions.asset.keluar.edit', $o) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200" title="Ubah"><i class="fa-solid fa-pencil text-xs"></i></a>
                    <form method="POST" action="{{ route('transactions.asset.keluar.destroy', $o) }}" class="inline" onsubmit="return confirmDelete(event, this)">@csrf @method('DELETE')<button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 ml-1" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button></form>
                </td>
            </tr>@empty<tr>
                <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
            </tr>@endforelse</tbody>
    </table>
</div>
<script>
    (function() {
        const s = document.getElementById('location_type'),
            w = document.getElementById('luarFields'),
            a = document.getElementById('asset_id'),
            tgl = document.getElementById('acq_date'),
            stk = document.getElementById('stock_now'),
            qty = document.querySelector('input[name="quantity"]'),
            chk = document.getElementById('has_return'),
            ret = document.getElementById('returned_qty'),
            rdt = document.getElementById('return_date'),
            sisa = document.getElementById('sisa_info'),
            after = document.getElementById('stock_after_info');

        function t() {
            w.classList.toggle('hidden', s.value !== 'Luar Sekolah');
        }
        s.addEventListener('change', t);
        t();
        function syncTgl() {
            const o = a.selectedOptions[0];
            tgl.value = o?.dataset.tgl || '';
            stk.value = o?.dataset.stock ?? '';
            if (qty && o?.dataset.stock !== undefined && o.dataset.stock !== '') qty.max = o.dataset.stock;
            calc();
        }
        function syncRet() {
            const on = chk.checked;
            ret.disabled = !on;
            rdt.disabled = !on;
            if (!on) { ret.value = ''; rdt.value = ''; }
            else if (ret.value === '') { ret.value = qty.value || 0; }
            calc();
        }
        function calc() {
            const q = parseInt(qty.value || 0, 10) || 0;
            const r = chk.checked ? (parseInt(ret.value || 0, 10) || 0) : 0;
            const st = parseInt((a.selectedOptions[0]?.dataset.stock ?? ''), 10);
            ret.max = q;
            sisa.textContent = (q - Math.min(r, q)) + ' dari ' + q;
            after.textContent = isNaN(st) ? '-' : (st - q + Math.min(r, q));
        }
        a.addEventListener('change', syncTgl);
        chk.addEventListener('change', syncRet);
        qty.addEventListener('input', calc);
        ret.addEventListener('input', calc);
        syncRet();
        syncTgl();
    })();

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
            $('#assetKeluarTable').DataTable({
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