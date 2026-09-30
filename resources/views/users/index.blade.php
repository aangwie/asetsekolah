@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
    <div>
        <div class="flex items-center space-x-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1"><span>Pengaturan</span><span>&bull;</span><span>Users</span></div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Users</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola akun petugas dan hak akses role.</p>
    </div>
    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm"><i class="fa-solid fa-user-plus mr-2"></i> Tambah User</a>
</div>
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
    <table id="usersTable" class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase">
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>@foreach($users as $u)<tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold">{{ $u->name }}</td>
                <td class="px-4 py-3">{{ $u->email }}</td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">{{ $u->roles->pluck('name')->join(', ') }}</span></td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><a href="{{ route('users.edit', $u) }}" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition" title="Edit"><i class="fa-solid fa-pen text-sm"></i></a>
                    <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="p-2 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 transition ml-1" title="Hapus"><i class="fa-solid fa-trash text-sm"></i></button></form>
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
            $('#usersTable').DataTable({
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