@php($f = $filter ?? null)
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 mb-6" x-data="{ mode: '{{ $f['mode'] ?? 'tahun' }}' }">
    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3 mb-4"><i class="fa-solid fa-filter text-blue-600"></i>
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Filter Laporan</h3>
    </div>
    <form method="GET" action="{{ $action }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <label class="block text-xs font-bold text-slate-700 uppercase">Mode Filter<select name="filter_mode" x-model="mode" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                <option value="tahun" @selected(($f['mode'] ?? 'tahun' )==='tahun' )>Tahun</option>
                <option value="periode" @selected(($f['mode'] ?? null)==='periode' )>Periode</option>
            </select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase" x-show="mode === 'tahun'">Tahun<select name="year" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">@for($y = $yearNow + 1; $y >= 1990; $y--)<option value="{{ $y }}" @selected((int) ($f['year'] ?? $yearNow)===$y)>{{ $y }}</option>@endfor</select></label>
        <label class="block text-xs font-bold text-slate-700 uppercase" x-show="mode === 'periode'">Tanggal Mulai<input type="date" name="start_date" value="{{ $f['from'] ?? '' }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase" x-show="mode === 'periode'">Tanggal Sampai<input type="date" name="end_date" value="{{ $f['to'] ?? '' }}" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"></label>
        <label class="block text-xs font-bold text-slate-700 uppercase">Tampilan<select name="view_type" class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                <option value="lengkap" @selected(($f['view'] ?? $defaultView ?? 'lengkap' )==='lengkap' )>Lengkap</option>
                <option value="rekap" @selected(($f['view'] ?? $defaultView ?? 'lengkap' )==='rekap' )>Rekap</option>
            </select></label>
        <div class="md:col-span-4"><button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition"><i class="fa-solid fa-magnifying-glass mr-2"></i> Tampilkan</button></div>
    </form>
    @if($f)<p class="mt-3 text-xs text-slate-500">Periode: {{ $f['from'] }} s/d {{ $f['to'] }} • Mode: {{ $f['view'] }}</p>@endif
</div>