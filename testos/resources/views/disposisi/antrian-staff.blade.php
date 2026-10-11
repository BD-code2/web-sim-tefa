@extends('layouts.sim')
@section('title', 'Tugas Disposisi Saya')
@section('page-title', 'Tugas Disposisi Masuk')

@section('content')
<div class="sim-page">
    <div class="page-toolbar">
        <div><p class="font-medium text-slate-800">Tugas dari Kepala Sekolah</p><p class="page-description">Pilih Buka detail & proses untuk membaca surat dan arahan Kepala Sekolah. Di halaman detail, berikan tanggapan lalu ubah status tugas menjadi Diproses atau Selesai.</p></div>
        <a href="{{ route('disposisi.arsip') }}" class="page-button ui-action"><x-ui.icon name="archive" /> Arsip disposisi</a>
    </div>
    <section class="page-panel">
        <div class="page-panel-heading">
            <div><h2 class="page-title">Disposisi yang perlu ditindaklanjuti</h2><p class="page-description">Dokumen surat masuk dan lembar disposisi dapat diunduh. Tugas yang ditandai Selesai akan muncul pada Arsip Disposisi.</p></div>
            <span class="page-badge page-badge-info">{{ $disposisiSaya->total() }} tugas aktif</span>
        </div>
        <div class="page-table-scroll">
            <table class="page-table-actions">
                <thead><tr><th scope="col">Surat / agenda</th><th scope="col">Instruksi dan catatan</th><th scope="col">Persetujuan</th><th scope="col">Status tugas</th><th scope="col" class="text-right">Dokumen &amp; tindakan</th></tr></thead>
                <tbody>
                    @forelse($disposisiSaya as $disp)
                        <tr>
                            <td class="min-w-[200px] max-w-72"><a href="{{ route('disposisi.show', $disp) }}" class="font-medium text-blue-700 hover:underline">{{ $disp->suratMasuk?->no_agenda ?? '-' }}</a><p class="mt-2 font-semibold text-slate-900">{{ $disp->suratMasuk?->perihal ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $disp->suratMasuk?->pengirim ?? '-' }}</p></td>
                            <td class="min-w-[200px] max-w-80"><p class="whitespace-pre-line text-slate-700">{{ $disp->instruksi }}</p>@if($disp->catatan)<p class="mt-3 whitespace-pre-line text-xs text-slate-500"><span class="font-semibold">Catatan:</span> {{ $disp->catatan }}</p>@endif</td>
                            <td><x-ui.status :value="$disp->status_persetujuan" /></td>
                            <td><x-ui.status :value="$disp->status" /></td>
                            <td class="min-w-[168px]">@include('disposisi.partials.downloads', ['disposisi' => $disp])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="page-empty"><p class="font-semibold text-slate-700">Belum ada tugas aktif</p><p class="mt-1">Disposisi yang dikirim Kepala Sekolah akan muncul di sini.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($disposisiSaya->hasPages())<div class="border-t border-slate-200 p-5">{{ $disposisiSaya->links() }}</div>@endif
    </section>
</div>
@endsection
