@extends('layouts.sim')

@section('title', 'Sertifikat Siswa PKL')
@section('page-title', 'Sertifikat Praktik Kerja Lapangan (PKL)')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="page-intro text-slate-500">Kelola tahapan PKL dan sertifikat per siswa: selesaikan PKL, terbitkan sertifikat, lalu unduh PDF siswa tersebut. Data peserta dan agenda dikelola melalui menu Sertifikat dan PKL.</p>
        </div>
        <a href="{{ route('data-siswa.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0A4DBA] hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition-all ui-action"><x-ui.icon name="folder-open" /> Kelola Agenda, Periode &amp; Peserta PKL</a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('sertifikat.index') }}" class="page-filter">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Siswa, NISN, atau No. Sertifikat..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="terverifikasi" {{ request('status') == 'terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="diterbitkan" {{ request('status') == 'diterbitkan' ? 'selected' : '' }}>Diterbitkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition ui-action"><x-ui.icon name="filter" /> Filter</button>
                <a href="{{ route('sertifikat.index') }}" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 ui-action"><x-ui.icon name="reset" /> Reset</a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Sertifikat -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th scope="col" class="py-3.5 px-4">No. Sertifikat</th>
                        <th scope="col" class="py-3.5 px-4">Nama Siswa / NISN</th>
                        <th scope="col" class="py-3.5 px-4">Kelas & Jurusan</th>
                        <th scope="col" class="py-3.5 px-4">Mitra DUDI</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Nilai</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                        <th scope="col" class="py-3.5 px-4 text-center w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sertifikats as $s)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-600">
                                {{ $s->no_sertifikat ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $s->nama_siswa }}</div>
                                <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700">{{ $s->kelas }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $s->program_keahlian }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">
                                {{ $s->nama_industri_pkl ?: ($s->industri?->nama_industri ?? '-') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-slate-800">{{ $s->nilai ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $s->predikat ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($s->status === 'diterbitkan')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Diterbitkan</span>
                                @elseif($s->status === 'terverifikasi')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">Terverifikasi</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">Draft</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    <x-ui.action icon="eye" label="Detail" tone="info" href="{{ route('sertifikat.show', $s) }}" />
                                    @if($s->status === 'draft')
                                        <form action="{{ route('sertifikat.status', $s) }}" method="POST" onsubmit="return confirm('Tandai PKL {{ $s->nama_siswa }} selesai?')">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="terverifikasi">
                                            <x-ui.action icon="check" label="Selesaikan PKL" tone="success" type="submit" />
                                        </form>
                                    @elseif($s->status === 'terverifikasi')
                                        <form action="{{ route('sertifikat.status', $s) }}" method="POST" onsubmit="return confirm('Terbitkan sertifikat untuk {{ $s->nama_siswa }}?')">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="diterbitkan">
                                            <x-ui.action icon="certificate" label="Terbitkan" tone="success" type="submit" />
                                        </form>
                                    @endif
                                    @if($s->status === 'diterbitkan')
                                        <x-ui.action icon="download" label="Unduh Cetak PDF" tone="info" href="{{ route('sertifikat.pdf', $s) }}" target="_blank" />
                                    @endif
                                    <form action="{{ route('sertifikat.destroy', $s) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sertifikat ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus Sertifikat" tone="danger" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data sertifikat siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sertifikats->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sertifikats->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
