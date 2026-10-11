@extends('layouts.sim')

@section('title', 'Laporan Surat')
@section('page-title', 'Laporan Surat')

@section('content')
@php
    $jenisLabel = [
        'surat_masuk' => 'Surat Masuk',
        'surat_keluar' => 'Surat Keluar',
        'surat_dinas' => 'Surat Dinas',
        'disposisi' => 'Disposisi',
    ];
@endphp

<div class="sim-page space-y-5">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Cetak Laporan Surat</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih jenis dan periode. Semua data yang terpilih akan digabung dalam satu PDF.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('laporan-surat.index') }}" class="space-y-4">
            <div>
                <p class="text-sm font-semibold text-slate-700 mb-2">Jenis laporan</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    @foreach($jenisLabel as $nilai => $label)
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer hover:border-blue-300 hover:bg-blue-50/50">
                            <input type="checkbox" name="jenis[]" value="{{ $nilai }}" @checked(in_array($nilai, $filter['jenis'], true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $filter['tanggal_selesai'] }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <button type="submit" class="page-button bg-blue-600 hover:bg-blue-500 text-white ui-action justify-center">
                    <x-ui.icon name="filter" /> Terapkan Filter
                </button>
                <div class="flex gap-2">
                    <a href="{{ route('laporan-surat.index') }}" class="page-button border border-slate-300 text-slate-600 hover:bg-slate-50 ui-action justify-center flex-1">
                        <x-ui.icon name="reset" /> Reset
                    </a>
                    <button type="submit" formaction="{{ route('laporan-surat.cetak') }}" formtarget="_blank" class="page-button bg-emerald-600 hover:bg-emerald-500 text-white ui-action justify-center flex-1">
                        <x-ui.icon name="printer" /> Cetak Semua
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach($jenisLabel as $jenis => $label)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 {{ in_array($jenis, $filter['jenis'], true) ? '' : 'opacity-50' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($laporan[$jenis]->count()) }}</p>
                <p class="text-xs text-slate-400">data ditemukan</p>
            </div>
        @endforeach
    </div>

    @foreach($filter['jenis'] as $jenis)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Pratinjau {{ $jenisLabel[$jenis] }}</h3>
                @if($laporan[$jenis]->count() > 10)
                    <span class="text-xs text-slate-500">Menampilkan 10 dari {{ $laporan[$jenis]->count() }} data</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        @if($jenis === 'surat_masuk')
                            <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">No. Surat</th><th class="px-4 py-3 text-left">Pengirim</th><th class="px-4 py-3 text-left">Perihal</th><th class="px-4 py-3 text-left">Status</th></tr>
                        @elseif($jenis === 'surat_keluar')
                            <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">No. Surat</th><th class="px-4 py-3 text-left">Tujuan</th><th class="px-4 py-3 text-left">Perihal</th><th class="px-4 py-3 text-left">Status</th></tr>
                        @elseif($jenis === 'surat_dinas')
                            <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">No. Surat</th><th class="px-4 py-3 text-left">Tentang</th><th class="px-4 py-3 text-left">Status</th></tr>
                        @else
                            <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Surat</th><th class="px-4 py-3 text-left">Dari</th><th class="px-4 py-3 text-left">Penerima</th><th class="px-4 py-3 text-left">Status</th></tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($laporan[$jenis]->take(10) as $item)
                            @if($jenis === 'surat_masuk')
                                <tr><td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_diterima?->format('d/m/Y') ?? '-' }}</td><td class="px-4 py-3">{{ $item->no_surat }}</td><td class="px-4 py-3">{{ $item->pengirim }}</td><td class="px-4 py-3">{{ $item->perihal }}</td><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $item->status) }}</td></tr>
                            @elseif($jenis === 'surat_keluar')
                                <tr><td class="px-4 py-3 whitespace-nowrap">{{ $item->tgl_pelaksanaan?->format('d/m/Y') ?? '-' }}</td><td class="px-4 py-3">{{ $item->no_surat ?? '-' }}</td><td class="px-4 py-3">{{ $item->tujuan }}</td><td class="px-4 py-3">{{ $item->perihal }}</td><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $item->status) }}</td></tr>
                            @elseif($jenis === 'surat_dinas')
                                <tr><td class="px-4 py-3 whitespace-nowrap">{{ $item->tgl_ditetapkan?->format('d/m/Y') ?? '-' }}</td><td class="px-4 py-3">{{ $item->jenis_surat }}</td><td class="px-4 py-3">{{ $item->no_surat ?? '-' }}</td><td class="px-4 py-3">{{ $item->tentang_judul }}</td><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $item->status) }}</td></tr>
                            @else
                                <tr><td class="px-4 py-3 whitespace-nowrap">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td><td class="px-4 py-3">{{ $item->suratMasuk?->no_surat ?? '-' }}</td><td class="px-4 py-3">{{ $item->dariUser?->name ?? '-' }}</td><td class="px-4 py-3">{{ $item->kepadaUser?->name ?? '-' }}</td><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $item->status) }}</td></tr>
                            @endif
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
