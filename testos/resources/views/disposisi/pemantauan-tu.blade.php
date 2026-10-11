@extends('layouts.sim')

@section('title', 'Pemantauan Disposisi')
@section('page-title', 'Pemantauan Disposisi')

@section('content')
<div class="sim-page">
    <div class="page-note page-intro">
        Setelah mencatat surat pada menu Surat Masuk, pilih Teruskan ke Kepala Sekolah di bawah ini. Kepala Sekolah menentukan penerima dan instruksi disposisi. Tugas yang ditujukan kepada TU dapat diproses melalui halaman detail.
    </div>
    <div class="flex justify-end"><a href="{{ route('disposisi.arsip') }}" class="page-button ui-action"><x-ui.icon name="archive" /> Arsip Disposisi</a></div>

    <section class="page-panel">
        <div class="page-panel-heading">
            <div>
                <h2 class="font-bold text-slate-800">Surat Masuk Menunggu Diteruskan</h2>
                <p class="page-description">Pilih “Teruskan ke Kepala Sekolah” setelah pencatatan surat selesai.</p>
            </div>
            <span class="page-badge page-badge-warning">{{ $suratMasukBelumDiteruskan->total() }} menunggu</span>
        </div>
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full min-w-[900px] text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide">
                    <tr>
                        <th scope="col" class="p-3 text-center">Nomor Urut</th><th scope="col" class="p-3">Nomor dan Tanggal Surat</th><th scope="col" class="p-3">Tanggal Diterima</th><th scope="col" class="p-3">Alamat Asal (Pengirim)</th><th scope="col" class="p-3">Perihal</th><th scope="col" class="p-3">Aksi TU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suratMasukBelumDiteruskan as $surat)
                        <tr class="align-top">
                            <td class="p-3 text-center font-semibold">{{ $surat->nomor_urut }}</td>
                            <td class="p-3"><div class="font-medium">{{ $surat->no_surat }}</div><div class="text-slate-500 mt-1">{{ $surat->tanggal_surat->format('d/m/Y') }}</div></td>
                            <td class="p-3 whitespace-nowrap">{{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
                            <td class="p-3 whitespace-pre-line">{{ $surat->pengirim }}</td>
                            <td class="p-3">{{ $surat->perihal }}</td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('surat-masuk.teruskan-kepala-sekolah', $surat) }}" onsubmit="return confirm('Teruskan surat ini kepada Kepala Sekolah?')">
                                    @csrf
                                    <x-ui.action icon="send" label="Teruskan ke Kepala Sekolah" tone="info" type="submit" />
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">Tidak ada surat masuk yang menunggu diteruskan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratMasukBelumDiteruskan->hasPages())<div class="p-4 border-t border-slate-100">{{ $suratMasukBelumDiteruskan->links() }}</div>@endif
    </section>

    <section class="page-panel">
        <div class="page-panel-heading">
            <h2 class="font-bold text-slate-800">Terakhir Diteruskan ke Kepala Sekolah</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[750px] text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide"><tr><th scope="col" class="p-3 text-center">Nomor Urut</th><th scope="col" class="p-3">Nomor Surat</th><th scope="col" class="p-3">Perihal</th><th scope="col" class="p-3">Diteruskan Pada</th><th scope="col" class="p-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suratMasukSudahDiteruskan as $surat)
                        <tr><td class="p-3 text-center">{{ $surat->nomor_urut }}</td><td class="p-3">{{ $surat->no_surat }}</td><td class="p-3">{{ $surat->perihal }}</td><td class="p-3">{{ $surat->diteruskan_ke_kepala_sekolah_at->format('d/m/Y H:i') }}</td><td class="p-3">{{ $surat->disposisis_count ? 'Sudah didisposisikan' : 'Menunggu disposisi Kepala Sekolah' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada surat yang diteruskan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="page-panel">
        <div class="page-panel-heading"><h2 class="font-bold text-slate-800">Disposisi yang Ditujukan kepada TU</h2><p class="page-description">Tindak lanjuti tugas yang Kepala Sekolah teruskan kepada Koordinator TU.</p></div>
        <div class="overflow-x-auto">
            <table class="page-table-actions w-full min-w-[750px] text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide"><tr><th scope="col" class="p-3">Surat / Agenda</th><th scope="col" class="p-3">Dari</th><th scope="col" class="p-3">Instruksi</th><th scope="col" class="p-3">Status</th><th scope="col" class="p-3">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disposisiSaya as $disposisi)
                        <tr>
                            <td class="p-3"><a class="font-semibold text-blue-700" href="{{ route('disposisi.show', $disposisi) }}">{{ $disposisi->suratMasuk?->no_agenda ?? 'Surat' }}</a><div class="text-slate-500">{{ $disposisi->suratMasuk?->perihal }}</div></td>
                            <td class="p-3">{{ $disposisi->dariUser?->role_label ?? 'Kepala Sekolah' }}</td>
                            <td class="p-3">{{ $disposisi->instruksi }}</td>
                            <td class="p-3"><x-ui.status :value="$disposisi->status" /></td>
                            <td class="p-3 min-w-[168px]">@include('disposisi.partials.downloads')</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada tugas disposisi untuk TU.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
