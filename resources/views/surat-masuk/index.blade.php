@extends('layouts.sim')

@section('title', 'Daftar Surat Masuk')
@section('page-title', 'Buku Agenda Surat Masuk')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="page-intro text-slate-500">Catat surat yang diterima sekolah, unggah dokumen aslinya, lalu teruskan kepada Kepala Sekolah melalui menu Pemantauan Disposisi.</p>
        <a href="{{ route('surat-masuk.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 active:scale-[.98] ui-action"><x-ui.icon name="plus" /> Catat surat masuk</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('surat-masuk.index') }}" class="page-filter">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor surat, agenda, pengirim, atau perihal..." class="min-w-0 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100 ">
            <select name="status" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <option value="">Semua status</option>
                <option value="diterima" @selected(request('status') === 'diterima')>Diterima</option>
                <option value="didisposisi" @selected(request('status') === 'didisposisi')>Didisposisikan</option>
                <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                <option value="diarsipkan" @selected(request('status') === 'diarsipkan')>Diarsipkan</option>
            </select>
            <select name="sifat" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <option value="">Semua sifat</option>
                <option value="biasa" @selected(request('sifat') === 'biasa')>Biasa</option>
                <option value="segera" @selected(request('sifat') === 'segera')>Segera</option>
                <option value="rahasia" @selected(request('sifat') === 'rahasia')>Rahasia</option>
            </select>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-4 lg:justify-end">
                <button type="submit" class="flex-1 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 ui-action lg:flex-none"><x-ui.icon name="search" /> Cari / Filter</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('sifat'))
                    <a href="{{ route('surat-masuk.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50 ui-action"><x-ui.icon name="reset" /> Reset</a>
                @endif
            </div>
        </form>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="font-bold tracking-tight text-slate-900">Buku agenda surat masuk</h2>
                <p class="mt-1 text-xs text-slate-500">Buku agenda mencatat nomor surat, pengirim, perihal, tanggal diterima, dan tindak lanjut surat.</p>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $suratMasuk->total() }} surat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full min-w-[1120px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/90 text-[11px] font-semibold uppercase tracking-[.08em] text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center">No.</th>
                        <th scope="col" class="px-4 py-3.5">Nomor dan tanggal surat</th>
                        <th scope="col" class="px-4 py-3.5">Diterima</th>
                        <th scope="col" class="px-4 py-3.5">Pengirim</th>
                        <th scope="col" class="px-4 py-3.5">Perihal</th>
                        <th scope="col" class="px-4 py-3.5">Disposisi</th>
                        <th scope="col" class="px-4 py-3.5">Keterangan</th>
                        <th scope="col" class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px] leading-5 text-slate-600">
                    @forelse($suratMasuk as $item)
                        <tr class="align-top transition-colors hover:bg-blue-50/40">
                            <td class="px-4 py-4 text-center"><span class="inline-flex min-w-8 justify-center rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-600">{{ $item->nomor_urut }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-medium text-slate-800">{{ $item->no_surat }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $item->tanggal_surat->format('d/m/Y') }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $item->tanggal_diterima->format('d/m/Y') }}</td>
                            <td class="max-w-56 whitespace-pre-line px-4 py-4 text-slate-700">{{ $item->pengirim }}</td>
                            <td class="max-w-64 px-4 py-4 font-medium text-slate-800">{{ $item->perihal }}</td>
                            <td class="max-w-64 whitespace-pre-line px-4 py-4">{{ $item->disposisi_tindak_lanjut ?: '—' }}</td>
                            <td class="max-w-56 whitespace-pre-line px-4 py-4">{{ $item->keterangan ?: '—' }}</td>
                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <x-ui.action icon="eye" label="Lihat detail surat masuk {{ $item->no_surat }}" tone="info" href="{{ route('surat-masuk.show', $item) }}" />
                                    <x-ui.action icon="reply" label="Buat surat balasan untuk {{ $item->no_surat }}" tone="success" href="{{ route('surat-keluar.create', ['referensi_id' => $item->id]) }}" />
                                    <form action="{{ route('surat-masuk.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat masuk ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action icon="trash" label="Hapus surat masuk {{ $item->no_surat }}" tone="danger" type="submit" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <p class="font-semibold text-slate-700">Belum ada surat masuk</p>
                                <p class="mt-1 text-sm text-slate-500">Surat yang dicatat akan muncul di buku agenda ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratMasuk->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $suratMasuk->links() }}</div>
        @endif
    </section>
</div>
@endsection
