@extends('layouts.app')
@section('title', 'Aset Masuk')
@section('content')
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                <span>Transaksi</span><span>&bull;</span><span>Aset Masuk</span></div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Aset Masuk</h2>
            <p class="text-sm text-slate-500 mt-1">Pencatatan perolehan aset tetap KIB A–E.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select id="tplKib"
                class="px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium outline-none border border-slate-200"
                title="Pilih Template KIB">
                <option value="">Template Umum</option>
                <option value="A">KIB A – Tanah</option>
                <option value="B">KIB B – Peralatan</option>
                <option value="C">KIB C – Gedung</option>
                <option value="D">KIB D – Jalan/Jaringan</option>
                <option value="E">KIB E – Lainnya</option>
            </select>
            <a id="tplBtn" href="{{ route('transactions.asset.masuk.template') }}"
                class="inline-flex items-center px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition"><i
                    class="fa-solid fa-file-excel mr-2 text-emerald-600"></i> Template</a>
            <a id="expBtn" href="{{ route('transactions.asset.masuk.export') }}"
                class="inline-flex items-center px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition"><i
                    class="fa-solid fa-download mr-2 text-blue-600"></i> Export</a>
            <button type="button" onclick="document.getElementById('impFile').click()"
                class="inline-flex items-center px-3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition shadow-sm"><i
                    class="fa-solid fa-upload mr-2"></i> Import</button>
            <input type="file" id="impFile" accept=".xlsx,.xls,.csv" class="hidden">
            <a href="{{ route('transactions.asset.masuk.create') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm"><i
                    class="fa-solid fa-plus mr-2"></i> Catat Aset Masuk</a>
        </div>
    </div>
    <div id="impWrap" class="hidden bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <div class="flex justify-between text-xs font-bold uppercase text-slate-500 mb-2"><span id="impTxt">0%</span><span
                id="impCnt"></span></div>
        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
            <div id="impBar" class="h-3 bg-blue-600 rounded-full transition-all" style="width:0%"></div>
        </div>
        <ul id="impErr" class="mt-2 text-xs text-red-600 list-disc ml-5 space-y-1"></ul>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <form id="filter-form" method="GET" action="" class="space-y-4" onsubmit="return false;">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2"><i class="fa-solid fa-filter text-blue-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Filter Data Aset Masuk</h3>
                </div><button type="button" id="btn-reset-filter"
                    class="text-xs text-slate-500 hover:text-blue-600 transition font-medium flex items-center gap-1"><i
                        class="fa-solid fa-arrows-rotate"></i> Reset Filter</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                <div><label for="filter_kib" class="block text-xs font-bold text-slate-700 uppercase mb-1.5"><i
                            class="fa-solid fa-layer-group mr-1"></i> Filter Jenis Aset</label><select id="filter_kib"
                        name="kib_type"
                        class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none transition">
                        <option value="">-- Semua Jenis (KIB A s/d KIB E) --</option>
                        <option value="A">KIB A - Tanah</option>
                        <option value="B">KIB B - Peralatan & Mesin</option>
                        <option value="C">KIB C - Gedung & Bangunan</option>
                        <option value="D">KIB D - Jalan, Irigasi & Jaringan</option>
                        <option value="E">KIB E - Aset Tetap Lainnya</option>
                    </select></div>
            </div>
        </form>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table id="assetMasukIndexTable" class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Jenis / Kode</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-right">Jml</th>
                    <th class="px-4 py-3 text-right">Satuan</th>
                    <th class="px-4 py-3 text-right">Perolehan</th>
                    <th class="px-4 py-3 text-left">Dana</th>
                    <th class="px-4 py-3 text-center">Bukti</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>@forelse($assets as $a)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $a->acquisition_date->format('d-m-Y') }}</td>
                    <td class="px-4 py-3"><span
                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">KIB
                            {{ $a->kib_type }}</span>
                        <div class="font-mono text-xs font-bold text-blue-600 mt-1">{{ $a->asset_code }}</div>
                    </td>
                    <td class="px-4 py-3 font-semibold">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($a->quantity ?? 1, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp
                        {{ number_format($a->unit_price ?? $a->acquisition_value, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($a->acquisition_value, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ $a->funding_source ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">@if($a->proof_path)<button type="button"
                        onclick="openProof('{{ asset('storage/' . $a->proof_path) }}', '{{ strtolower(pathinfo($a->proof_path, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'img' }}')"
                        class="text-blue-600 hover:text-blue-800" title="Lihat bukti"><i
                    class="fa-solid fa-file-lines"></i></button>@else<span class="text-slate-300">-</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap"><a
                            href="{{ route('transactions.asset.masuk.edit', $a) }}"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200"
                            title="Ubah"><i class="fa-solid fa-pencil text-xs"></i></a>
                        <form method="POST" action="{{ route('transactions.asset.masuk.destroy', $a) }}" class="inline"
                            onsubmit="return confirmDelete(event, this)">@csrf @method('DELETE')<button
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 ml-1"
                                title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button></form>
                    </td>
            </tr>@empty<tr>
                    <td colspan="9" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div id="proofLightbox" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        onclick="if(event.target===this)closeProof()">
        <div
            class="bg-white rounded-2xl shadow-xl w-[75vw] h-[75vh] max-w-[75vw] max-h-[75vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-200"><span
                    class="text-xs font-bold uppercase tracking-wider text-slate-600">Bukti Belanja</span><button
                    type="button" onclick="closeProof()"
                    class="text-slate-500 hover:text-red-600 text-lg leading-none">&times;</button></div>
            <div class="flex-1 min-h-0"><img id="proofImg" class="hidden w-full h-full object-contain bg-slate-50"
                    alt="Bukti"><iframe id="proofFrame" class="hidden w-full h-full bg-white" title="Bukti PDF"></iframe>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>(function ($) { $(function () { var assetMasukTbl = $('#assetMasukIndexTable').DataTable({ responsive: true, pageLength: 10, language: { search: "Cari Data:", lengthMenu: "Tampilkan _MENU_ data", zeroRecords: "Data tidak ditemukan.", info: "Halaman _PAGE_ dari _PAGES_ (_TOTAL_ total data)", infoEmpty: "Tidak ada data tersedia", infoFiltered: "(difilter dari _MAX_ total data)", paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Sebelumnya" } }, columnDefs: [{ targets: -1, orderable: false, searchable: false }] }); $('#filter_kib').on('change', function () { var v = $(this).val(); if (v) { assetMasukTbl.column(1).search('KIB ' + v, true, false).draw(); } else { assetMasukTbl.column(1).search('').draw(); } }); $('#btn-reset-filter').on('click', function () { $('#filter_kib').val(''); assetMasukTbl.search('').columns().search('').draw(); }); }); })(jQuery); function confirmDelete(e, f) { e.preventDefault(); Swal.fire({ title: 'Hapus aset?', text: 'Data terhapus permanen.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then(r => { if (r.isConfirmed) f.submit(); }); return false; } function openProof(url, kind) { const lb = document.getElementById('proofLightbox'), im = document.getElementById('proofImg'), fr = document.getElementById('proofFrame'); im.classList.add('hidden'); fr.classList.add('hidden'); if (kind === 'pdf') { fr.src = url; fr.classList.remove('hidden'); } else { im.src = url; im.classList.remove('hidden'); } lb.classList.remove('hidden'); document.body.style.overflow = 'hidden'; } function closeProof() { const lb = document.getElementById('proofLightbox'), im = document.getElementById('proofImg'), fr = document.getElementById('proofFrame'); lb.classList.add('hidden'); im.src = ''; fr.src = ''; document.body.style.overflow = ''; } document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProof(); });
        const impEl = document.getElementById('impFile');
        if (impEl) impEl.addEventListener('change', async (e) => {
            if (!e.target.files.length) return;
            const wrap = document.getElementById('impWrap'), bar = document.getElementById('impBar'), txt = document.getElementById('impTxt'), cnt = document.getElementById('impCnt'), err = document.getElementById('impErr');
            wrap.classList.remove('hidden'); err.innerHTML = ''; bar.style.width = '5%'; txt.textContent = 'Upload...';
            const fd = new FormData(); fd.append('file', e.target.files[0]); fd.append('_token', '{{ csrf_token() }}');
            const pre = await (await fetch('{{ route('transactions.asset.masuk.import-preview') }}', { method: 'POST', body: fd })).json();
            if (!pre.token) { txt.textContent = 'Gagal upload.'; return; }
            const total = pre.total, limit = 200; let done = 0, okAll = 0;
            if (!total) { txt.textContent = 'File kosong.'; return; }
            while (done < total) {
                const r = await (await fetch('{{ route('transactions.asset.masuk.import-chunk') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ token: pre.token, offset: done, limit }) })).json();
                done += limit; okAll += r.ok || 0;
                const p = Math.min(100, Math.round(done / total * 100));
                bar.style.width = p + '%'; txt.textContent = p + '%'; cnt.textContent = Math.min(done, total) + '/' + total + ' (' + okAll + ' simpan)';
                (r.errors || []).forEach(m => { const li = document.createElement('li'); li.textContent = m; err.appendChild(li); });
            }
            txt.textContent = 'Selesai: ' + okAll + '/' + total; setTimeout(() => location.reload(), 1500);
        });
        const tplSel = document.getElementById('tplKib'), tplBtn = document.getElementById('tplBtn'), expBtn = document.getElementById('expBtn');
        if (tplSel && tplBtn) { tplSel.addEventListener('change', () => { const v = tplSel.value; const bT = '{{ route('transactions.asset.masuk.template') }}', bE = '{{ route('transactions.asset.masuk.export') }}'; tplBtn.href = v ? bT + '?kib=' + v : bT; if (expBtn) expBtn.href = v ? bE + '?kib=' + v : bE; }); }
    </script>
@endsection