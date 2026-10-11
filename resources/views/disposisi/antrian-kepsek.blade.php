@extends('layouts.sim')
@section('title', 'Pemantauan Disposisi')
@section('page-title', 'Pemantauan Disposisi')

@section('content')
@php($totalPersetujuan = $suratKeluarMenunggu->count() + $suratDinasMenunggu->count())
<div class="sim-page">
    <div class="page-toolbar">
        <p class="page-intro text-slate-600">Periksa pengajuan surat dari TU dan berikan keputusan. Untuk surat masuk yang telah diteruskan TU, pilih penerima serta instruksi disposisi, lalu pantau pengerjaan tugasnya.</p>
        <a href="{{ route('disposisi.arsip') }}" class="page-button ui-action"><x-ui.icon name="archive" /> Arsip disposisi</a>
    </div>
    <nav class="grid gap-3 sm:grid-cols-3" aria-label="Ringkasan pemantauan">
        <a href="#persetujuan" class="page-panel px-5 py-4 transition hover:border-blue-300">
            <span class="flex items-center gap-2 text-slate-500"><x-ui.icon name="check-circle" /> Menunggu persetujuan</span>
            <span class="mt-2 block text-2xl font-semibold text-slate-900">{{ $totalPersetujuan }} <span class="text-sm font-normal text-slate-500">surat</span></span>
        </a>
        <a href="#surat-masuk" class="page-panel px-5 py-4 transition hover:border-blue-300">
            <span class="flex items-center gap-2 text-slate-500"><x-ui.icon name="inbox" /> Belum didisposisikan</span>
            <span class="mt-2 block text-2xl font-semibold text-slate-900">{{ $suratBelumDisposisi->count() }} <span class="text-sm font-normal text-slate-500">surat masuk</span></span>
        </a>
        <a href="#riwayat" class="page-panel px-5 py-4 transition hover:border-blue-300">
            <span class="flex items-center gap-2 text-slate-500"><x-ui.icon name="clipboard" /> Disposisi diterbitkan</span>
            <span class="mt-2 block text-2xl font-semibold text-slate-900">{{ $riwayatDisposisi->total() }} <span class="text-sm font-normal text-slate-500">tugas</span></span>
        </a>
    </nav>
    <section id="persetujuan" class="page-panel scroll-mt-24">
        <div class="page-panel-heading">
            <div><h2 class="page-title">Surat menunggu persetujuan</h2><p class="page-description">Surat Keluar, SK, dan SPT muncul setelah diperiksa Kepala TU dan diteruskan kembali oleh TU.</p></div>
            <span class="page-badge page-badge-warning">{{ $totalPersetujuan }} menunggu</span>
        </div>
        @if($errors->has('catatan_revisi'))
            <div role="alert" class="mx-6 mt-5 rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700">{{ $errors->first('catatan_revisi') }}</div>
        @endif
        @if($totalPersetujuan === 0)
            <div class="page-empty"><p class="font-semibold text-slate-700">Semua pengajuan sudah ditinjau</p><p class="mt-1">Surat baru dari TU akan muncul di sini.</p></div>
        @else
            <div class="page-table-scroll">
                <table class="page-table-actions">
                    <thead><tr><th scope="col">Surat yang diajukan</th><th scope="col">Pembuat / penerima</th><th scope="col">Tanggal</th><th scope="col" class="text-right">Keputusan</th></tr></thead>
                    @foreach($suratKeluarMenunggu as $letter)
                        @include('disposisi.partials.approval-row', ['letter' => $letter, 'kind' => 'keluar'])
                    @endforeach
                    @foreach($suratDinasMenunggu as $letter)
                        @include('disposisi.partials.approval-row', ['letter' => $letter, 'kind' => 'dinas'])
                    @endforeach
                </table>
            </div>
        @endif
    </section>
    <section id="surat-masuk" class="page-panel scroll-mt-24">
        <div class="page-panel-heading">
            <div><h2 class="page-title">Surat masuk untuk didisposisikan</h2><p class="page-description">Surat telah diteruskan oleh TU. Tentukan penerima dan instruksi melalui lembar disposisi.</p></div>
            <span class="page-badge page-badge-neutral">{{ $suratBelumDisposisi->count() }} surat</span>
        </div>
        <div class="page-table-scroll">
            <table class="page-table-actions">
                <thead><tr><th scope="col">Nomor agenda / surat</th><th scope="col">Pengirim</th><th scope="col">Perihal</th><th scope="col">Diterima</th><th scope="col" class="text-right">Keputusan</th></tr></thead>
                <tbody>
                    @forelse($suratBelumDisposisi as $sm)
                        <tr>
                            <td><div class="font-medium text-slate-900">{{ $sm->no_agenda }}</div><div class="mt-1 text-xs text-slate-500">{{ $sm->no_surat }}</div></td>
                            <td class="max-w-56 whitespace-pre-line">{{ $sm->pengirim }}</td>
                            <td class="max-w-64 font-medium text-slate-900">{{ $sm->perihal }}</td>
                            <td class="whitespace-nowrap">{{ $sm->tanggal_diterima?->format('d/m/Y') ?? '-' }}</td>
                            <td><div class="page-actions">
                                <x-ui.action icon="eye" label="Detail surat" tone="info" href="{{ route('surat-masuk.show', $sm) }}" />
                                <x-ui.action icon="pencil" label="Isi lembar disposisi" tone="warning" href="{{ route('disposisi.lembar.create', $sm) }}" />
                                <form action="{{ route('disposisi.surat-masuk.tolak', $sm) }}" method="POST" onsubmit="return confirm('Tolak permintaan disposisi dari TU untuk surat ini?')">
                                    @csrf
                                    <x-ui.action icon="close" label="Tolak" tone="danger" type="submit" />
                                </form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="page-empty">Tidak ada surat masuk yang menunggu disposisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section id="riwayat" class="page-panel scroll-mt-24">
        <div class="page-panel-heading"><div><h2 class="page-title">Riwayat disposisi yang diterbitkan</h2><p class="page-description">Pantau tanggapan penerima dan perkembangan setiap tugas.</p></div></div>
        <div class="page-table-scroll">
            <table class="page-table-actions">
                <thead><tr><th scope="col">Surat / agenda</th><th scope="col">Penerima</th><th scope="col">Instruksi</th><th scope="col">Persetujuan</th><th scope="col">Status tugas</th><th scope="col" class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse($riwayatDisposisi as $rd)
                        <tr>
                            <td class="min-w-[170px]"><a href="{{ route('disposisi.show', $rd) }}" class="font-medium text-blue-700 hover:underline">{{ $rd->suratMasuk?->no_agenda ?? '-' }}</a><p class="mt-1 text-xs text-slate-500">{{ $rd->suratMasuk?->perihal ?? '-' }}</p></td>
                            <td><div class="font-medium text-slate-900">{{ $rd->kepadaUser?->role_label ?? '-' }}</div></td>
                            <td class="min-w-[180px] max-w-64 whitespace-pre-line">{{ $rd->instruksi }}</td>
                            <td><x-ui.status :value="$rd->status_persetujuan" /></td>
                            <td><x-ui.status :value="$rd->status" /></td>
                            <td><div class="page-actions">
                                <x-ui.action icon="eye" label="Detail" tone="info" href="{{ route('disposisi.show', $rd) }}" />
                                <x-ui.action icon="printer" label="Cetak" href="{{ route('disposisi.cetak', $rd) }}" target="_blank" rel="noopener" />
                                <form action="{{ route('disposisi.destroy', $rd) }}" method="POST" onsubmit="return confirm('Hapus lembar disposisi ini?')">@csrf @method('DELETE')<x-ui.action icon="trash" label="Hapus" tone="danger" type="submit" /></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="page-empty">Belum ada lembar disposisi yang diterbitkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayatDisposisi->hasPages())<div class="border-t border-slate-200 p-5">{{ $riwayatDisposisi->links() }}</div>@endif
    </section>
</div>
@endsection
