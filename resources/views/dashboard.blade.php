@extends('layouts.sim')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard SIM Sekolah')

@section('content')
@php
    $user = auth()->user();
    $dashboardDescription = match (true) {
        $user->isTU() => 'Catat surat masuk, ajukan surat keluar dan surat dinas, lalu pantau disposisi, kerja sama industri, serta kegiatan PKL.',
        $user->isKepalaTU() => 'Periksa Surat Keluar, SK, dan SPT yang diajukan TU sebelum diteruskan kepada Kepala Sekolah.',
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

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['inbox', 'Surat masuk', $stats['total_surat_masuk'], $user->isSiswa() ? 'Tidak tersedia untuk role ini' : $stats['surat_masuk_baru'].' surat berstatus diterima', 'bg-blue-50 text-blue-700'],
            ['outbox', 'Surat keluar', $stats['total_surat_keluar'], $user->isKepalaTU() ? 'Menunggu pemeriksaan Anda' : (($user->isTU() || $user->isKepalaSekolah()) ? $stats['surat_keluar_menunggu'].' menunggu persetujuan' : 'Tidak tersedia untuk role ini'), 'bg-emerald-50 text-emerald-700'],
            ['document', 'Surat dinas', $stats['total_surat_dinas'], $user->isKepalaTU() ? 'Menunggu pemeriksaan Anda' : (($user->isTU() || $user->isKepalaSekolah()) ? $stats['surat_dinas_menunggu'].' menunggu persetujuan' : 'Tidak tersedia untuk role ini'), 'bg-violet-50 text-violet-700'],
            ['clipboard', 'Disposisi', $stats['total_disposisi'], $user->isSiswa() ? 'Tidak tersedia untuk role ini' : $stats['disposisi_menunggu'].' tugas masih menunggu', 'bg-amber-50 text-amber-700'],
        ] as [$icon, $label, $total, $description, $tone])
            <section class="page-panel p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-slate-600">{{ $label }}</h2>
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tone }}"><x-ui.icon :name="$icon" size="md" /></span>
                </div>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $total }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $description }}</p>
            </section>
        @endforeach
    </div>

    @if($user->isKepalaTU())
        <section class="page-panel p-5 page-toolbar">
            <div><h2 class="font-semibold text-slate-900">Pemeriksaan surat</h2><p class="mt-1 text-sm text-slate-500">{{ $stats['total_surat_keluar'] + $stats['total_surat_dinas'] }} surat keluar, SK, dan SPT menunggu pemeriksaan.</p></div>
            <a href="{{ route('surat-keluar.pemeriksaan-kepala-tu') }}" class="page-button page-button-primary ui-action"><x-ui.icon name="check-circle" /> Buka antrean pemeriksaan</a>
        </section>
    @endif

    @if($user->isTU() || $user->isHumas())
        <div class="grid gap-4 sm:grid-cols-2">
            <section class="page-panel p-5"><h2 class="text-sm text-slate-500">Mitra industri</h2><p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_kerja_sama'] }}</p><p class="mt-2 text-xs text-slate-500">{{ $stats['kerja_sama_aktif'] }} kerja sama aktif</p></section>
            <section class="page-panel p-5"><h2 class="text-sm text-slate-500">Sertifikat dan PKL</h2><p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_sertifikat'] }}</p><p class="mt-2 text-xs text-slate-500">Sertifikat tersimpan di sistem</p></section>
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
