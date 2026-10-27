@extends('layouts.sim')

@section('title', 'Detail Agenda Industri')
@section('page-title', 'Detail Agenda Industri')

@section('content')
<div class="sim-page max-w-3xl mx-auto space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('kerja-sama.show', $kerjaSama) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 ui-action">
            <x-ui.icon name="back" /> Kembali ke {{ $kerjaSama->nama_industri }}
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('kerja-sama.agenda.edit', [$kerjaSama, $agenda]) }}" class="page-button bg-amber-500 hover:bg-amber-600 text-white ui-action">
                <x-ui.icon name="pencil" /> Edit Agenda
            </a>
            <form action="{{ route('kerja-sama.agenda.destroy', [$kerjaSama, $agenda]) }}" method="POST" onsubmit="return confirm('Hapus agenda ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="page-button bg-rose-600 hover:bg-rose-700 text-white ui-action"><x-ui.icon name="trash" /> Hapus</button>
            </form>
        </div>
    </div>

    <article class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-700 to-indigo-700 text-white">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">Agenda {{ $kerjaSama->nama_industri }}</p>
                    <h1 class="mt-2 text-2xl font-bold">{{ $agenda->judul }}</h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-semibold capitalize">{{ $agenda->tipe }}</span>
                    @if($agenda->is_penting)<span class="px-3 py-1 rounded-full bg-rose-400 text-slate-900 text-xs font-bold">Penting</span>@endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Mulai</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $agenda->tanggal_mulai->format('d F Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Selesai</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $agenda->tanggal_selesai?->format('d F Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Waktu</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $agenda->waktu ? substr($agenda->waktu, 0, 5) : '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Lokasi</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $agenda->lokasi ?? '-' }}</p>
            </div>
            <div class="sm:col-span-2 border-t border-slate-100 pt-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Deskripsi</p>
                <p class="mt-2 leading-relaxed text-slate-700 whitespace-pre-line">{{ $agenda->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
            </div>
            <div class="sm:col-span-2 text-xs text-slate-400">
                Dibuat oleh {{ $agenda->creator?->name ?? '-' }} pada {{ $agenda->created_at?->format('d/m/Y H:i') ?? '-' }}
            </div>
        </div>
    </article>
</div>
@endsection
