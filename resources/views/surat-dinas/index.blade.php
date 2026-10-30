@extends('layouts.sim')

@section('title', 'Daftar Surat Dinas')
@section('page-title', 'Surat Dinas (SK & SPT)')

@section('content')
<div class="sim-page space-y-6">
    <div class="page-toolbar">
        <div>
            <p class="page-intro text-slate-500">{{ auth()->user()->isKepalaSekolah() ? 'Periksa rincian SK dan SPT yang telah diteruskan TU, lalu setujui atau kembalikan dengan catatan revisi. PDF tersedia setelah surat disetujui.' : 'Buat SK atau SPT, lalu ajukan kepada Kepala TU untuk diperiksa. Setelah diperiksa, TU meneruskannya kepada Kepala Sekolah untuk persetujuan akhir.' }}</p>
        </div>
        <div class="grid w-full grid-cols-1 items-stretch gap-2 sm:w-auto sm:grid-cols-2">
            <a href="{{ route('surat-dinas.create-sk') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-blue-700 px-3 py-2.5 text-center text-sm font-semibold leading-tight text-white transition hover:bg-blue-800 ui-action"><x-ui.icon name="plus" /> <span>Buat Surat Keputusan</span></a>
            <a href="{{ route('surat-dinas.create-spt') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-blue-700 px-3 py-2.5 text-center text-sm font-semibold leading-tight text-white transition hover:bg-blue-800 ui-action"><x-ui.icon name="plus" /> <span>Buat Surat Perintah Tugas</span></a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('surat-dinas.index') }}" class="page-filter">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Surat atau Tentang / Judul..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <select name="jenis" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Jenis</option>
                    <option value="SK" {{ request('jenis') == 'SK' ? 'selected' : '' }}>SK (Surat Keputusan)</option>
                    <option value="SPT" {{ request('jenis') == 'SPT' ? 'selected' : '' }}>SPT (Surat Perintah Tugas)</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="menunggu_persetujuan" {{ request('status') == 'menunggu_persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak (Revisi)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition ui-action"><x-ui.icon name="filter" /> Filter</button>
                <a href="{{ route('surat-dinas.index') }}" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 ui-action"><x-ui.icon name="reset" /> Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Surat Dinas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th scope="col" class="py-3.5 px-4">Jenis</th>
                        <th scope="col" class="py-3.5 px-4">No. Surat</th>
                        <th scope="col" class="py-3.5 px-4">Tentang / Judul</th>
                        <th scope="col" class="py-3.5 px-4">Tgl. Ditetapkan</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                        <th scope="col" class="py-3.5 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($suratDinas as $item)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3.5 px-4 text-center">
                                @if($item->jenis_surat === 'SK')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">SK</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">SPT</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-600">
                                @if($item->no_surat)
                                    <a href="{{ route('surat-dinas.show', $item) }}">{{ $item->no_surat }}</a>
                                @else
                                    <span class="text-slate-400 italic">(Belum bernomor / Draf)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 max-w-xs truncate">{{ $item->tentang_judul }}</td>
                            <td class="py-3.5 px-4">{{ $item->tgl_ditetapkan?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->status === 'disetujui')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Disetujui</span>
                                @elseif($item->status === 'menunggu_persetujuan')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">{{ $item->diteruskan_ke_kepala_sekolah_at ? 'Menunggu Kepala Sekolah' : ($item->diperiksa_kepala_tu_at ? 'Menunggu Diteruskan TU' : 'Menunggu Kepala TU') }}</span>
                                @elseif($item->status === 'ditolak')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">{{ $item->diperiksa_kepala_tu_at && !$item->diteruskan_ke_kepala_sekolah_at ? 'Perlu Revisi TU' : 'Revisi Kepala Sekolah' }}</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">Draft</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form action="{{ route('surat-dinas.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat dinas ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus surat {{ $item->no_surat ?? $item->tentang_judul }}" tone="danger" type="submit" />
                                    </form>
                                    @if($item->status === 'disetujui')
                                        <x-ui.action icon="printer" label="Cetak PDF" href="{{ route('surat-dinas.pdf', $item) }}" target="_blank" />
                                    @else
                                        <span class="p-1.5 rounded-lg bg-slate-100 text-slate-300 cursor-not-allowed ui-action ui-action-icon" title="Cetak PDF aktif setelah disetujui Kepala Sekolah." aria-disabled="true" aria-label="Cetak PDF aktif setelah disetujui Kepala Sekolah.">
                                            <x-ui.icon name="printer" />
                                        </span>
                                    @endif
                                    <x-ui.action icon="eye" label="Detail" tone="info" href="{{ route('surat-dinas.show', $item) }}" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada surat dinas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suratDinas->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $suratDinas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
