@extends('layouts.sim')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard SIM Sekolah')

@section('content')
@php
    $user = auth()->user();
    $dashboardDescription = match (true) {
        $user->isTU() => 'Catat surat masuk, ajukan surat keluar dan surat dinas, lalu pantau disposisi, kerja sama industri, serta kegiatan PKL.',
        $user->isKepalaSekolah() => 'Tinjau pengajuan surat dari TU, berikan arahan disposisi kepada penerima, dan pantau penyelesaian tugas.',
        $user->isHumas() => 'Kelola kerja sama industri dan kegiatan PKL, serta tindak lanjuti disposisi yang ditugaskan kepada akun Anda.',
        $user->isSiswa() => 'Lihat ringkasan dan pemberitahuan yang tersedia untuk akun Anda.',
        default => 'Baca disposisi dari Kepala Sekolah, unduh dokumen, berikan tanggapan, dan perbarui status pengerjaan melalui halaman detail.',
    };
@endphp
<div class="sim-page">
    <section class="page-panel page-section">
        <div class="page-toolbar">
            <div><p class="text-slate-500">Selamat datang</p><h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ $user->role_label }}</h1><p class="page-intro mt-2 max-w-2xl text-slate-500">{{ $dashboardDescription }}</p></div>
            @if($user->isTU())
                <a href="{{ route('surat-masuk.create') }}" class="page-button page-button-primary ui-action"><x-ui.icon name="plus" /> Catat surat masuk</a>
            @elseif($user->isKepalaSekolah())
                <a href="{{ route('disposisi.antrian', ['tab' => 'persetujuan']) }}#persetujuan" class="page-button page-button-primary ui-action"><x-ui.icon name="eye" /> Tinjau persetujuan surat</a>
            @elseif(!$user->isSiswa())
                <a href="{{ route('disposisi.antrian') }}" class="page-button page-button-primary ui-action"><x-ui.icon name="clipboard" /> Buka tugas disposisi</a>
            @endif
        </div>
    </section>

    @if($user->isTU() || $user->isKepalaSekolah() || $user->isHumas())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Surat masuk', $stats['total_surat_masuk'], $stats['surat_masuk_baru'].' surat berstatus diterima'],
                ['Surat keluar', $stats['total_surat_keluar'], $stats['surat_keluar_menunggu'].' menunggu persetujuan'],
                ['Mitra industri', $stats['total_kerja_sama'], $stats['kerja_sama_aktif'].' kerja sama aktif'],
                ['Sertifikat dan PKL', $stats['total_sertifikat'], 'Sertifikat tersimpan di sistem'],
            ] as [$label, $total, $description])
                <section class="page-panel p-5"><h2 class="text-sm text-slate-500">{{ $label }}</h2><p class="mt-3 text-3xl font-semibold text-slate-900">{{ $total }}</p><p class="mt-2 text-xs text-slate-500">{{ $description }}</p></section>
            @endforeach
        </div>
    @endif

    @if($user->isKepalaSekolah())
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">Persetujuan dan disposisi</h2><p class="page-description">Pilih pekerjaan yang ingin ditinjau.</p></div></div>
            <div class="page-section grid gap-5 sm:grid-cols-2">
                <div><h3 class="font-semibold text-slate-900">Persetujuan surat dari TU</h3><p class="mt-2 text-slate-500">Periksa surat undangan, mutasi siswa, Surat Keputusan, dan Surat Perintah Tugas sebelum diterbitkan.</p><a href="{{ route('disposisi.antrian', ['tab' => 'persetujuan']) }}#persetujuan" class="page-button mt-4 ui-action"><x-ui.icon name="clipboard" /> Buka daftar persetujuan</a></div>
                <div><h3 class="font-semibold text-slate-900">Disposisi surat masuk</h3><p class="mt-2 text-slate-500">Pilih penerima, tuliskan instruksi, lalu pantau perkembangan tugas yang telah diteruskan.</p><a href="{{ route('disposisi.antrian') }}#surat-masuk" class="page-button mt-4 ui-action"><x-ui.icon name="clipboard" /> Kelola disposisi</a></div>
            </div>
        </section>
    @endif

    @unless($user->isSiswa())
        <section class="page-panel">
            <div class="page-panel-heading">
                <div><h2 class="page-title">Disposisi terbaru untuk Anda</h2><p class="page-description">{{ $disposisiMenungguCount }} tugas menunggu tindak lanjut.</p></div>
                <a href="{{ route('disposisi.antrian') }}" class="font-semibold text-blue-700 hover:underline ui-action"><x-ui.icon name="clipboard" /> Lihat semua tugas</a>
            </div>
            <div class="page-table-scroll">
                <table>
                    <thead><tr><th scope="col">Surat / agenda</th><th scope="col">Instruksi</th><th scope="col">Status</th><th scope="col" class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($disposisiSaya as $disp)
                            <tr><td class="min-w-[200px] max-w-80"><p class="font-medium text-slate-900">{{ $disp->suratMasuk?->perihal ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $disp->suratMasuk?->no_agenda ?? '-' }}</p></td><td class="max-w-80 whitespace-pre-line">{{ $disp->instruksi }}</td><td><x-ui.status :value="$disp->status" /></td><td><div class="page-actions"><x-ui.action icon="eye" label="{{ $disp->status === 'selesai' ? 'Lihat detail' : 'Buka detail & proses' }}" tone="info" href="{{ route('disposisi.show', $disp) }}" /></div></td></tr>
                        @empty
                            <tr><td colspan="4" class="page-empty">Belum ada disposisi yang ditugaskan kepada Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endunless

    @if($user->isTU())
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">Surat masuk terbaru</h2><p class="page-description">Lima surat terakhir yang tercatat dalam buku agenda.</p></div><a href="{{ route('surat-masuk.index') }}" class="font-semibold text-blue-700 hover:underline ui-action"><x-ui.icon name="inbox" /> Buka buku agenda</a></div>
            <div class="page-table-scroll"><table>
                <thead><tr><th scope="col">Nomor agenda</th><th scope="col">Perihal</th><th scope="col">Pengirim</th><th scope="col">Diterima</th><th scope="col" class="text-right">Aksi</th></tr></thead>
                <tbody>@forelse($suratMasukTerbaru as $sm)<tr><td>{{ $sm->no_agenda }}</td><td class="max-w-72 font-medium text-slate-900">{{ $sm->perihal }}</td><td class="max-w-64">{{ $sm->pengirim }}</td><td class="whitespace-nowrap">{{ $sm->tanggal_diterima?->format('d/m/Y') ?? '-' }}</td><td><div class="page-actions"><x-ui.action icon="eye" label="Detail surat" tone="info" href="{{ route('surat-masuk.show', $sm) }}" /></div></td></tr>@empty<tr><td colspan="5" class="page-empty">Belum ada surat masuk yang dicatat.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
    @endif
</div>
@endsection
