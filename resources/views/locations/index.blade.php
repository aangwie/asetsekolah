@extends('layouts.app')
@section('title', 'Ruangan')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Master Data</span><span>&bull;</span><span>Ruangan</span></div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Ruangan</h2>
        <p class="text-sm text-slate-500 mt-1">Setiap ruang terikat ke satu gedung.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('locations.template') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition"><i class="fa-solid fa-file-excel mr-2 text-emerald-600"></i> Template</a>
        <a href="{{ route('locations.export') }}" class="inline-flex items-center px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition"><i class="fa-solid fa-download mr-2 text-blue-600"></i> Export</a>
        <button type="button" onclick="document.getElementById('impFile').click()" class="inline-flex items-center px-3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition shadow-sm"><i class="fa-solid fa-upload mr-2"></i> Import</button>
        <input type="file" id="impFile" accept=".xlsx,.xls,.csv" class="hidden">
        <a href="{{ route('locations.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm"><i class="fa-solid fa-plus mr-2"></i> Tambah Ruangan</a>
    </div>
</div>
<div id="impWrap" class="hidden bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex justify-between text-xs font-bold uppercase text-slate-500 mb-2"><span id="impTxt">0%</span><span id="impCnt"></span></div>
    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden"><div id="impBar" class="h-3 bg-blue-600 rounded-full transition-all" style="width:0%"></div></div>
    <ul id="impErr" class="mt-2 text-xs text-red-600 list-disc ml-5 space-y-1"></ul>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <table id="roomsTable" class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Ruangan</th>
                <th class="px-4 py-3 text-left">Gedung</th>
                <th class="px-4 py-3 text-left">PIC</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>@foreach($locations as $l)<tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs font-bold text-blue-600">{{ $l->code }}</td>
                <td class="px-4 py-3 font-semibold">{{ $l->name }}</td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">{{ $l->building->name ?? '-' }}</span></td>
                <td class="px-4 py-3">{{ $l->pic->name ?? '-' }}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('locations.edit', $l) }}" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Edit"><i class="fa-solid fa-pen text-sm"></i></a>
                    <form method="POST" action="{{ route('locations.destroy', $l) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="p-2 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 transition ml-1" title="Hapus"><i class="fa-solid fa-trash text-sm"></i></button></form>
                </td>
            </tr>@endforeach</tbody>
    </table>
</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    (function($) {
        $(function() {
            $('#roomsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
                },
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }]
            });
        });
    })(jQuery);
    document.getElementById('impFile').addEventListener('change', async (e) => {
        if (!e.target.files.length) return;
        const wrap = document.getElementById('impWrap'), bar = document.getElementById('impBar'),
            txt = document.getElementById('impTxt'), cnt = document.getElementById('impCnt'), err = document.getElementById('impErr');
        wrap.classList.remove('hidden'); err.innerHTML = ''; bar.style.width = '5%'; txt.textContent = 'Upload...';
        const fd = new FormData(); fd.append('file', e.target.files[0]); fd.append('_token', '{{ csrf_token() }}');
        const pre = await (await fetch('{{ route('locations.import-preview') }}', { method: 'POST', body: fd })).json();
        if (!pre.token) { txt.textContent = 'Gagal upload.'; return; }
        const total = pre.total, limit = 200; let done = 0, okAll = 0;
        if (!total) { txt.textContent = 'File kosong.'; return; }
        while (done < total) {
            const r = await (await fetch('{{ route('locations.import-chunk') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ token: pre.token, offset: done, limit }) })).json();
            done += limit; okAll += r.ok || 0;
            const p = Math.min(100, Math.round(done / total * 100));
            bar.style.width = p + '%'; txt.textContent = p + '%'; cnt.textContent = Math.min(done, total) + '/' + total + ' (' + okAll + ' simpan)';
            (r.errors || []).forEach(m => { const li = document.createElement('li'); li.textContent = m; err.appendChild(li); });
        }
        txt.textContent = 'Selesai: ' + okAll + '/' + total; setTimeout(() => location.reload(), 1500);
    });
</script>
@endsection