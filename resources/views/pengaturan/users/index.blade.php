@extends('layouts.sim')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Kelola Akun & Hak Akses Pengguna')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="page-intro text-slate-500">Tambah atau ubah akun sekolah, tentukan role dan hak aksesnya, serta atur status aktif akun. Role menentukan menu yang dapat dibuka oleh setiap pengguna.</p>
        </div>
        <a href="{{ route('pengaturan.users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0A4DBA] hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition-all ui-action"><x-ui.icon name="plus" /> Tambah Pengguna Baru</a>
    </div>

    <!-- Tabel Daftar Pengguna -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase">
                    <tr>
                        
                        <th scope="col" class="py-3.5 px-4">Email</th>
                        <th scope="col" class="py-3.5 px-4">Role / Hak Akses</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                        <th scope="col" class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            
                            <td class="py-3.5 px-4 font-mono">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                    {{ $u->role_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u->is_active)
                                    <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-ui.action icon="pencil" label="Edit" tone="warning" href="{{ route('pengaturan.users.edit', $u) }}" />
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('pengaturan.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.action icon="trash" label="Hapus" tone="danger" type="submit" />
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
