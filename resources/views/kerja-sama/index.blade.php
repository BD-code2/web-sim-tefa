@extends('layouts.sim')

@section('title', 'Kerja Sama Industri')
@section('page-title', 'Kerja Sama Industri')

@section('content')
<div class="sim-page space-y-6">
    <!-- Subtitle & Tombol Tambah Kerja Sama Sesuai Mockup -->
    <div class="page-toolbar">
        <div>
        <p class="page-intro text-slate-500">Kelola identitas mitra industri, dokumen MoU, serta masa berlaku kerja sama. Mitra baru yang ditambahkan TU menunggu persetujuan Humas; penambahan oleh Humas tidak memerlukan persetujuan.</p>
        </div>
        <a href="{{ route('kerja-sama.create') }}" class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0A4DBA] hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition-all ui-action"><x-ui.icon name="plus" /> Tambah Kerja Sama</a>
    </div>

    <!-- 4 Stats Cards Persis Seperti di Mockup -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Total Kerja Sama -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">Total Kerja Sama</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['total'] }}</h3>
                <span class="text-xs text-slate-400 font-medium mt-0.5 inline-block">Semua mitra</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                <x-ui.icon name="building" size="md" />
            </div>
        </div>

        <!-- Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">Aktif</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['aktif'] }}</h3>
                <span class="text-xs text-emerald-600 font-medium mt-0.5 inline-block">Lihat aktif</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <x-ui.icon name="check-circle" size="md" />
            </div>
        </div>

        <!-- Akan Berakhir -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">Akan Berakhir</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['akan_berakhir'] }}</h3>
                <span class="text-xs text-amber-600 font-medium mt-0.5 inline-block">Perlu perhatian</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                <x-ui.icon name="warning" size="md" />
            </div>
        </div>

        <!-- Berakhir -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">Berakhir</p>
                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ $stats['berakhir'] }}</h3>
                <span class="text-xs text-slate-400 font-medium mt-0.5 inline-block">Arsip</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center">
                <x-ui.icon name="archive" size="md" />
            </div>
        </div>
    </div>

    <!-- Filter Bar Sesuai Desain Mockup -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('kerja-sama.index') }}" class="page-filter">
            <!-- Cari nama industri -->
            <div class=" relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama industri..."
                       class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="akan_berakhir" {{ request('status') == 'akan_berakhir' ? 'selected' : '' }}>Akan Berakhir</option>
                    <option value="berakhir" {{ request('status') == 'berakhir' ? 'selected' : '' }}>Berakhir</option>
                </select>
            </div>

            <div>
                <select name="approval_status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua Persetujuan</option>
                    <option value="pending" @selected(request('approval_status') === 'pending')>Menunggu Persetujuan</option>
                    <option value="approved" @selected(request('approval_status') === 'approved')>Disetujui</option>
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition ui-action"><x-ui.icon name="filter" /> Filter</button>
                <a href="{{ route('kerja-sama.index') }}" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 ui-action ui-action-icon" title="Reset Filter" aria-label="Reset Filter">
                    <x-ui.icon name="reset" />
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Kerja Sama Industri -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <p class="px-4 py-3 text-xs text-slate-500">Geser tabel ke samping untuk melihat detail dan aksi kerja sama.</p>
        <div class="overflow-x-auto directory-scroll" tabindex="0" role="region" aria-label="Daftar kerja sama industri">
            <table class="page-table-actions directory-table min-w-[1120px] whitespace-nowrap w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th scope="col" class="py-3.5 px-4 w-12 text-center">No</th>
                        <th scope="col" class="py-3.5 px-4">Nama Industri</th>
                        <th scope="col" class="py-3.5 px-4">Alamat</th>
                        <th scope="col" class="py-3.5 px-4">Kontak</th>
                        <th scope="col" class="py-3.5 px-4">Mulai</th>
                        <th scope="col" class="py-3.5 px-4">Berakhir</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                        <th scope="col" class="py-3.5 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($industris as $index => $item)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3.5 px-4 text-center font-medium">{{ $industris->firstItem() + $index }}</td>
                            <td class="directory-name py-3.5 px-4 font-semibold text-slate-800">
                                <a href="{{ route('kerja-sama.show', $item) }}" class="hover:text-blue-600">
                                    {{ $item->nama_industri }}
                                </a>
                            </td>
                            <td class="directory-address py-3.5 px-4 text-slate-500">{{ $item->alamat }}</td>
                            <td class="directory-meta py-3.5 px-4 font-mono">{{ $item->kontak ?? '-' }}</td>
                            <td class="directory-meta py-3.5 px-4">{{ $item->tanggal_mulai->format('d/m/Y') }}</td>
                            <td class="directory-meta py-3.5 px-4">{{ $item->tanggal_berakhir->format('d/m/Y') }}</td>
                            <td class="directory-status py-3.5 px-4 text-center">
                                @if($item->approval_status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">Menunggu Persetujuan</span>
                                @elseif($item->approval_status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">Ditolak</span>
                                @elseif($item->status === 'aktif')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                @elseif($item->status === 'akan_berakhir')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Akan Berakhir</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">Berakhir</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if(auth()->user()->isHumas() && $item->approval_status === 'pending')
                                        <form action="{{ route('kerja-sama.approve', $item) }}" method="POST">
                                            @csrf
                                            <x-ui.action icon="check" label="Setujui" tone="success" type="submit" />
                                        </form>
                                    @endif
                                    <x-ui.action icon="eye" label="Detail" tone="info" href="{{ route('kerja-sama.show', $item) }}" />
                                    <x-ui.action icon="pencil" label="Edit" tone="warning" href="{{ route('kerja-sama.edit', $item) }}" />
                                    <form action="{{ route('kerja-sama.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kerja sama industri ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus Kerja Sama" tone="danger" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data kerja sama industri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($industris->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $industris->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
