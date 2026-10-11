@extends('layouts.sim')

@section('title', 'Daftar Surat Keluar')
@section('page-title', 'Buku Agenda Surat Keluar')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="page-intro max-w-2xl text-slate-500">Buat surat undangan atau rekomendasi mutasi siswa. Surat akan diperiksa Kepala TU, dikembalikan kepada TU untuk diteruskan ke Kepala Sekolah, lalu dapat diunduh setelah disetujui.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('surat-keluar.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 active:scale-[.98] ui-action"><x-ui.icon name="plus" /> Buat surat keluar</a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('surat-keluar.index') }}" class="page-filter">
            <div>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor surat, tujuan, perihal, atau acara..." class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draf</option>
                    <option value="menunggu_persetujuan" @selected(request('status') === 'menunggu_persetujuan')>Menunggu persetujuan</option>
                    <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak / revisi</option>
                    <option value="minta_revisi" @selected(request('status') === 'minta_revisi')>Perlu revisi</option>
                    <option value="dikirim" @selected(request('status') === 'dikirim')>Dikirim</option>
                    <option value="diarsipkan" @selected(request('status') === 'diarsipkan')>Diarsipkan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 ui-action"><x-ui.icon name="search" /> Cari surat</button>
                <a href="{{ route('surat-keluar.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 ui-action"><x-ui.icon name="reset" /> Reset</a>
            </div>
        </form>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="font-bold tracking-tight text-slate-900">Buku agenda surat keluar</h2>
                <p class="mt-1 text-xs text-slate-500">Pantau pengajuan, catatan revisi, persetujuan, pengiriman, dan arsip setiap surat.</p>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $suratKeluar->total() }} surat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full min-w-[1050px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-[11px] font-semibold uppercase tracking-[.08em] text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 py-3.5">Nomor surat</th>
                        <th scope="col" class="px-4 py-3.5">Jenis</th>
                        <th scope="col" class="px-4 py-3.5">Tujuan</th>
                        <th scope="col" class="px-4 py-3.5">Perihal</th>
                        <th scope="col" class="px-4 py-3.5">Pelaksanaan</th>
                        <th scope="col" class="px-4 py-3.5 text-center">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px] leading-5 text-slate-600">
                    @forelse($suratKeluar as $item)
                        <tr class="align-top transition-colors hover:bg-blue-50/40">
                            <td class="whitespace-nowrap px-4 py-4 font-mono text-xs font-semibold text-blue-700">
                                <a href="{{ route('surat-keluar.show', $item) }}" class="hover:underline">{{ $item->no_surat ?: 'Belum bernomor' }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-700">{{ ['undangan' => 'Undangan', 'spt' => 'Perintah Tugas', 'sk' => 'Keputusan', 'mutasi' => 'Rekomendasi Mutasi'][$item->jenis_surat] ?? 'Surat Keluar' }}</td>
                            <td class="max-w-56 px-4 py-4 font-medium text-slate-800">{{ $item->tujuan }}</td>
                            <td class="max-w-64 px-4 py-4 text-slate-700">{{ $item->perihal }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $item->tgl_pelaksanaan ? $item->tgl_pelaksanaan->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($item->status === 'disetujui')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Disetujui</span>
                                @elseif($item->status === 'menunggu_persetujuan')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">{{ $item->diteruskan_ke_kepala_sekolah_at ? 'Menunggu Kepala Sekolah' : ($item->diperiksa_kepala_tu_at ? 'Menunggu diteruskan TU' : 'Menunggu pemeriksaan Kepala TU') }}</span>
                                @elseif(in_array($item->status, ['ditolak', 'minta_revisi']))
                                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-semibold text-rose-700">{{ $item->status === 'minta_revisi' ? 'Perlu revisi' : 'Ditolak' }}</span>
                                @elseif($item->status === 'dikirim')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">Dikirim</span>
                                @elseif($item->status === 'diarsipkan')
                                    <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Diarsipkan</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Draf</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <x-ui.action icon="eye" label="Lihat detail surat {{ $item->no_surat ?? $item->perihal }}" tone="info" href="{{ route('surat-keluar.show', $item) }}" />
                                    @if($item->status === 'disetujui')
                                        <x-ui.action icon="printer" label="Cetak PDF surat {{ $item->no_surat }}" href="{{ route('surat-keluar.pdf', $item) }}" target="_blank" />
                                    @endif
                                    <form action="{{ route('surat-keluar.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat keluar ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus surat {{ $item->no_surat ?? $item->perihal }}" tone="danger" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center"><p class="font-semibold text-slate-700">Belum ada surat keluar</p><p class="mt-1 text-sm text-slate-500">Surat yang dibuat akan tampil di buku agenda ini.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratKeluar->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $suratKeluar->links() }}</div>
        @endif
    </section>
</div>
@endsection
