@extends('layouts.sim')
@section('title', 'Arsip Disposisi')
@section('page-title', 'Arsip Disposisi')

@section('content')
@php($filterAktif = request()->filled('search') || request()->filled('penerima') || request()->filled('persetujuan') || request()->filled('tanggal_mulai') || request()->filled('tanggal_selesai'))
<div class="sim-page">
    <div class="page-toolbar">
        <div><p class="font-medium text-slate-800">Disposisi yang telah selesai</p><p class="page-description">Tugas masuk ke arsip setelah penerima memilih status Selesai. Buka detail untuk melihat instruksi dan penerima, atau unduh surat masuk serta lembar disposisinya.</p></div>
        <a href="{{ route('disposisi.antrian') }}" class="page-button ui-action"><x-ui.icon name="back" /> Pemantauan disposisi</a>
    </div>
    <form method="GET" action="{{ route('disposisi.arsip') }}" class="page-panel page-section page-filter">
        <div class="page-field">
            <label for="search-arsip" class="block">Pencarian</label>
            <input id="search-arsip" type="search" name="search" value="{{ request('search') }}" class="block w-full" placeholder="Cari agenda, perihal, atau pengirim">
        </div>
        @if(auth()->user()->isTU() || auth()->user()->isKepalaSekolah())
            <div class="page-field">
                <label for="penerima-arsip" class="block">Penerima</label>
                <select id="penerima-arsip" name="penerima" class="block w-full">
                    <option value="">Semua penerima</option>
                    @foreach($recipientRoles as $role => $label)<option value="{{ $role }}" @selected(request('penerima') === $role)>{{ $label }}</option>@endforeach
                </select>
            </div>
        @endif
        <div class="page-field">
            <label for="persetujuan-arsip" class="block">Persetujuan</label>
            <select id="persetujuan-arsip" name="persetujuan" class="block w-full">
                <option value="">Semua status</option>
                <option value="setuju" @selected(request('persetujuan') === 'setuju')>Disetujui</option>
                <option value="pending" @selected(request('persetujuan') === 'pending')>Menunggu</option>
                <option value="tidak_setuju" @selected(request('persetujuan') === 'tidak_setuju')>Ditolak</option>
            </select>
        </div>
        <div class="page-field">
            <label for="tanggal-mulai-arsip" class="block">Selesai dari</label>
            <input id="tanggal-mulai-arsip" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="block w-full">
        </div>
        <div class="page-field">
            <label for="tanggal-selesai-arsip" class="block">Sampai tanggal</label>
            <input id="tanggal-selesai-arsip" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="block w-full">
        </div>
        <div class="flex flex-wrap items-end gap-2 page-filter-actions">
            <button type="submit" class="page-button page-button-primary ui-action"><x-ui.icon name="filter" /> Terapkan filter</button>
            @if($filterAktif)<a href="{{ route('disposisi.arsip') }}" class="page-button ui-action"><x-ui.icon name="reset" /> Reset filter</a>@endif
        </div>
    </form>
    <section class="page-panel">
        <div class="page-panel-heading"><h2 class="page-title">Arsip tugas selesai</h2><span class="page-badge page-badge-success">{{ $arsipDisposisi->total() }} disposisi</span></div>
        <div class="page-table-scroll">
            <table class="page-table-actions">
                <thead><tr><th scope="col">Surat / agenda</th><th scope="col">Perihal</th><th scope="col">Penerima</th><th scope="col">Selesai pada</th><th scope="col" class="text-right">Dokumen &amp; detail</th></tr></thead>
                <tbody>
                    @forelse($arsipDisposisi as $disposisi)
                        <tr>
                            <td class="min-w-[170px]"><a href="{{ route('disposisi.show', $disposisi) }}" class="font-medium text-blue-700 hover:underline">{{ $disposisi->suratMasuk?->no_agenda ?? '-' }}</a><p class="mt-1 text-xs text-slate-500">{{ $disposisi->suratMasuk?->no_surat ?? '-' }}</p></td>
                            <td class="max-w-72"><p class="font-medium text-slate-900">{{ $disposisi->suratMasuk?->perihal ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $disposisi->suratMasuk?->pengirim ?? '-' }}</p></td>
                            <td><p class="font-medium text-slate-900">{{ $disposisi->kepadaUser?->role_label ?? '-' }}</p></td>
                            <td class="whitespace-nowrap">{{ $disposisi->selesai_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="min-w-[168px]">
                                @include('disposisi.partials.downloads')
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="page-empty">{{ $filterAktif ? 'Arsip disposisi yang sesuai filter tidak ditemukan.' : 'Belum ada disposisi yang selesai dan diarsipkan.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($arsipDisposisi->hasPages())<div class="border-t border-slate-200 p-5">{{ $arsipDisposisi->links() }}</div>@endif
    </section>
</div>
@endsection
