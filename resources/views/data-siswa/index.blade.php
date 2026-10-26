@extends('layouts.sim')

@section('title', $agendaPage ? 'Agenda PKL' : 'Arsip Agenda PKL')
@section('page-title', $agendaPage ? 'Agenda PKL' : 'Arsip Agenda PKL')

@section('content')
<div class="sim-page space-y-6">
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <div class="page-toolbar">
        <div>
            <p class="page-intro text-slate-500">{{ $agendaPage ? 'Impor siswa dari Excel ke agenda ini. PKL dapat disiapkan, diselesaikan, diterbitkan, dan diunduh per siswa atau sekaligus per kelas. Industri siswa dibaca dari Excel.' : 'Buat agenda beserta periode PKL terlebih dahulu. Buka agenda untuk mengimpor siswa dan mengelola proses PKL per siswa maupun per kelas.' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($agendaPage)
                <a href="{{ route('data-siswa.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 ui-action"><x-ui.icon name="back" /> Kembali ke Arsip Agenda</a>
            @else
                <a href="{{ route('data-siswa.periode.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm ui-action"><x-ui.icon name="plus" /> Buat Agenda / Periode PKL</a>
            @endif
        </div>
    </div>

    @if(!$agendaPage && $periodes->isNotEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div><h2 class="font-bold text-slate-800">Arsip Agenda PKL</h2><p class="text-sm text-slate-500">Setiap agenda menyimpan periode, daftar peserta, perkembangan PKL, dan sertifikatnya sendiri. Pilih Buka Agenda untuk mengelolanya.</p></div>
            </div>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($periodes as $arsip)
                    <article class="rounded-xl border border-slate-200 p-4 transition hover:border-blue-300">
                        <a href="{{ route('data-siswa.agenda.show', $arsip) }}" class="block rounded-lg outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        <span class="block font-semibold text-slate-800">{{ $arsip->nama }}</span>
                        <span class="mt-1 block text-xs text-slate-500">{{ $arsip->tanggal_mulai->format('d/m/Y') }} – {{ $arsip->tanggal_selesai->format('d/m/Y') }}</span>
                        <span class="mt-3 inline-flex rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white ui-action"><x-ui.icon name="folder-open" /> Buka Agenda</span>
                    </a>
                    <div class="mt-3 flex gap-2 border-t border-slate-100 pt-3">
                        <x-ui.action icon="pencil" label="Edit Nama Agenda" tone="warning" href="{{ route('data-siswa.periode.edit', $arsip) }}" />
                        <form method="POST" action="{{ route('data-siswa.periode.destroy', $arsip) }}" onsubmit="return confirm('Hapus agenda ini beserta semua data siswa dan sertifikat PKL yang terkait? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <x-ui.action icon="trash" label="Hapus Agenda" tone="danger" type="submit" />
                        </form>
                    </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if(!$agendaPage && $periodes->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            Belum ada periode PKL. Buat agenda/periode terlebih dahulu sebelum mengimport peserta.
            <a href="{{ route('data-siswa.periode.create') }}" class="ml-1 font-bold underline ui-action"><x-ui.icon name="plus" /> Buat Periode PKL</a>
        </div>
    @elseif($agendaPage && $periodeTerpilihId && ($periodeTerpilih = $periodes->firstWhere('id', $periodeTerpilihId)))
        <div class="rounded-2xl border border-blue-200 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-800">Agenda: {{ $periodeTerpilih->nama }}</h2>
                    @if($periodeTerpilih->agenda)<p class="mt-1 text-sm text-slate-600">{{ $periodeTerpilih->agenda }}</p>@endif
                    <p class="mt-2 text-xs font-semibold text-blue-700">Periode {{ $periodeTerpilih->tanggal_mulai->format('d/m/Y') }} – {{ $periodeTerpilih->tanggal_selesai->format('d/m/Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('data-siswa.periode.edit', $periodeTerpilih) }}" class="page-button ui-action"><x-ui.icon name="pencil" /> Edit nama agenda</a>
                    <a href="{{ route('data-siswa.import', ['periode_pkl_id' => $periodeTerpilih->id]) }}" class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 ui-action"><x-ui.icon name="upload" /> Import Siswa ke Periode Ini</a>
                </div>
            </div>
        </div>
    @endif

    @if($agendaPage && $periodeTerpilihId && $periodes->contains('id', (int) $periodeTerpilihId))

    @if($kelasTerpilih)
        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold text-blue-950">{{ $periodes->firstWhere('id', $periodeTerpilihId)?->nama }} · Kelas {{ $kelasTerpilih }}</h2>
                    <p class="mt-1 text-sm text-blue-800">{{ $jumlahSiswaKelas }} siswa pada periode ini. Industri tiap siswa diambil dari kolom industri Excel. Tentukan program keahlian untuk menyiapkan PKL seluruh kelas.</p>
                </div>
                @if($sertifikatKelas->where('status', 'diterbitkan')->isNotEmpty())
                    <a href="{{ route('data-siswa.pdf-kelas', ['kelas' => $kelasTerpilih, 'periode_pkl_id' => $periodeTerpilihId]) }}" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 ui-action"><x-ui.icon name="download" /> Unduh PDF Sertifikat Kelas</a>
                @endif
            </div>
            <form method="POST" action="{{ route('data-siswa.proses-kelas') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @csrf
                <input type="hidden" name="kelas" value="{{ $kelasTerpilih }}">
                <input type="hidden" name="periode_pkl_id" value="{{ $periodeTerpilihId }}">
                <select name="program_keahlian" required class="rounded-lg border-slate-300 text-sm"><option value="">Pilih jurusan</option><option>RPL</option><option>DKV</option><option>TKJ</option><option>PSPT</option><option>Animasi</option><option>Pengembangan Gim</option></select>
                <div class="rounded-lg bg-white px-3 py-2 text-xs text-slate-600">Periode: {{ $periodes->firstWhere('id', $periodeTerpilihId)?->tanggal_mulai?->format('d/m/Y') }} – {{ $periodes->firstWhere('id', $periodeTerpilihId)?->tanggal_selesai?->format('d/m/Y') }}</div>
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 ui-action"><x-ui.icon name="process" /> Siapkan PKL untuk Kelas</button>
            </form>
            @if($sertifikatKelas->where('status', 'draft')->isNotEmpty())
                <form method="POST" action="{{ route('data-siswa.selesaikan-kelas') }}" onsubmit="return confirm('Tandai seluruh PKL kelas {{ $kelasTerpilih }} selesai?')">
                    @csrf<input type="hidden" name="kelas" value="{{ $kelasTerpilih }}"><input type="hidden" name="periode_pkl_id" value="{{ $periodeTerpilihId }}">
                    <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 ui-action"><x-ui.icon name="check" /> Tandai PKL Kelas Selesai</button>
                </form>
            @endif
            @if($sertifikatKelas->where('status', 'terverifikasi')->isNotEmpty())
                <form method="POST" action="{{ route('data-siswa.terbitkan-kelas') }}" onsubmit="return confirm('Terbitkan sertifikat untuk seluruh siswa kelas {{ $kelasTerpilih }} yang sudah disiapkan?')">
                    @csrf<input type="hidden" name="kelas" value="{{ $kelasTerpilih }}"><input type="hidden" name="periode_pkl_id" value="{{ $periodeTerpilihId }}">
                    <button class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-800 ui-action"><x-ui.icon name="certificate" /> Terbitkan Sertifikat Seluruh Kelas</button>
                </form>
            @endif
        </section>
    @endif

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('data-siswa.agenda.show', $periodes->firstWhere('id', $periodeTerpilihId)) }}" class="page-filter">
            <input type="hidden" name="periode_pkl_id" value="{{ $periodeTerpilihId }}">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, kelas, atau industri..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <select name="kelas" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas }}" @selected($kelasTerpilih === $kelas)>{{ $kelas }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <span class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600">{{ $kelasTerpilih ? 'Aksi massal untuk kelas: '.$kelasTerpilih : 'Cari / pilih satu kelas untuk proses massal' }}</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition ui-action"><x-ui.icon name="search" /> Cari Kelas</button>
                <a href="{{ route('data-siswa.agenda.show', $periodes->firstWhere('id', $periodeTerpilihId)) }}" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 ui-action ui-action-icon" title="Reset" aria-label="Reset"><x-ui.icon name="reset" /></a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Siswa -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th scope="col" class="py-3.5 px-4">NISN</th>
                        <th scope="col" class="py-3.5 px-4">Nama Lengkap</th>
                        <th scope="col" class="py-3.5 px-4">Kelas</th>
                        <th scope="col" class="py-3.5 px-4">Program Keahlian PKL</th>
                        <th scope="col" class="py-3.5 px-4">Industri PKL (Excel)</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Nilai &amp; Predikat</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Status PKL</th>
                        <th scope="col" class="py-3.5 px-4 text-center min-w-64">Aksi PKL per Siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($dataSiswas as $siswa)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3.5 px-4 font-mono font-semibold text-blue-600">{{ $siswa->nisn }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $siswa->nama }}</td>
                            <td class="py-3.5 px-4 font-semibold">{{ $siswa->kelas ?? 'Belum diimpor' }}</td>
                            @php($pkl = $pklByNisn->get($siswa->nisn))
                            <td class="py-3.5 px-4 font-medium text-slate-700">{{ $pkl?->program_keahlian ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">{{ $pkl?->nama_industri_pkl ?? $siswa->industri ?? $pkl?->industri?->nama_industri ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-slate-800">{{ $pkl?->nilai ?? '-' }}</span>
                                <span class="text-xs text-slate-500 block">{{ $pkl?->predikat ?? 'Belum dinilai' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($pkl?->status === 'diterbitkan')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Sertifikat Terbit</span>
                                @elseif($pkl?->status === 'terverifikasi')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">PKL Selesai</span>
                                @elseif($pkl)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Dalam Proses</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">Belum diproses</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    @if(!$pkl)
                                        @if(filled($siswa->industri))
                                        <form method="POST" action="{{ route('data-siswa.proses-siswa', $siswa) }}" class="flex items-center gap-1">
                                            @csrf
                                            <select name="program_keahlian" required class="max-w-32 rounded-lg border-slate-300 text-xs" aria-label="Program keahlian PKL">
                                                <option value="">Pilih jurusan</option>
                                                <option>RPL</option><option>DKV</option><option>TKJ</option><option>PSPT</option><option>Animasi</option><option>Pengembangan Gim</option>
                                            </select>
                                            <x-ui.action icon="process" label="Siapkan PKL siswa" tone="info" type="submit" />
                                        </form>
                                        @else
                                            <span class="text-xs text-rose-600">Isi industri siswa terlebih dahulu</span>
                                        @endif
                                    @else
                                        <x-ui.action icon="eye" label="Detail PKL siswa" tone="info" :href="route('sertifikat.show', ['sertifikat' => $pkl->id] + request()->only(['kelas', 'search', 'page']))" />
                                        @if($pkl->status === 'draft')
                                            <form method="POST" action="{{ route('data-siswa.selesaikan-siswa', $siswa) }}" onsubmit="return confirm('Tandai PKL {{ $siswa->nama }} selesai?')">
                                                @csrf
                                                <x-ui.action icon="check" label="Selesaikan PKL siswa" tone="success" type="submit" />
                                            </form>
                                        @elseif($pkl->status === 'terverifikasi')
                                            <form method="POST" action="{{ route('sertifikat.status', $pkl) }}" onsubmit="return confirm('Terbitkan sertifikat PKL {{ $siswa->nama }}?')">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="diterbitkan">
                                                <x-ui.action icon="certificate" label="Terbitkan sertifikat siswa" tone="success" type="submit" />
                                            </form>
                                        @elseif($pkl->status === 'diterbitkan')
                                            <x-ui.action icon="download" label="Unduh sertifikat siswa" tone="success" href="{{ route('sertifikat.pdf', $pkl) }}" target="_blank" />
                                        @endif
                                    @endif
<form method="POST" action="{{ route('data-siswa.destroy', $siswa) }}" class="inline" onsubmit="return confirm('Hapus data siswa ini beserta status sertifikatnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus Data" tone="danger" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data siswa. Gunakan tombol "Import Excel" untuk memuat data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dataSiswas->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $dataSiswas->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
