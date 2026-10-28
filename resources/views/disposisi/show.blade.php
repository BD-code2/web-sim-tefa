@extends('layouts.sim')
@section('title', 'Detail Disposisi')
@section('page-title', 'Detail Disposisi')

@section('content')
<div class="sim-page">
    <div class="page-toolbar">
        <div>
            <a href="{{ route('disposisi.antrian') }}" class="font-semibold text-slate-600 hover:text-blue-700 ui-action"><x-ui.icon name="back" /> Pemantauan disposisi</a>
        </div>
        @if(auth()->user()->isKepalaSekolah())<a href="{{ route('disposisi.cetak', $disposisi) }}" target="_blank" rel="noopener" class="page-button ui-action"><x-ui.icon name="printer" /> Cetak lembar disposisi</a>@endif
    </div>
    <section class="page-panel">
        <div class="page-panel-heading">
            <div><p class="text-xs font-medium text-blue-700">{{ $disposisi->suratMasuk?->no_agenda ?? '-' }}</p><h1 class="mt-2 text-xl font-semibold leading-7 text-slate-900">{{ $disposisi->suratMasuk?->perihal ?? '-' }}</h1></div>
            <x-ui.status :value="$disposisi->status" />
        </div>
        <div class="page-section space-y-6">
            <dl class="page-facts sm:grid-cols-2 lg:grid-cols-3">
                <div><dt>Nomor surat</dt><dd class="font-medium">{{ $disposisi->suratMasuk?->no_surat ?? '-' }}</dd></div>
                <div><dt>Instansi pengirim</dt><dd>{{ $disposisi->suratMasuk?->pengirim ?? '-' }}</dd></div>
                <div><dt>Sifat surat masuk</dt><dd class="capitalize">{{ $disposisi->suratMasuk?->sifat ?? '-' }}</dd></div>
                <div><dt>Tanggal surat</dt><dd>{{ $disposisi->suratMasuk?->tanggal_surat?->format('d F Y') ?? '-' }}</dd></div>
                <div><dt>Diterima oleh TU</dt><dd>{{ $disposisi->suratMasuk?->tanggal_diterima?->format('d F Y') ?? '-' }}</dd></div>
                <div><dt>Status surat masuk</dt><dd><x-ui.status :value="$disposisi->suratMasuk?->status" /></dd></div>
            </dl>
            <div class="border-t border-slate-200 pt-5">
                <h2 class="mb-3 font-semibold text-slate-900">Dokumen surat</h2>
                @include('disposisi.partials.downloads', ['showDetail' => false, 'align' => 'left'])
            </div>
        </div>
    </section>
    <section class="page-panel">
        <div class="page-panel-heading"><h2 class="page-title">Instruksi Kepala Sekolah</h2></div>
        <div class="page-section space-y-6">
            <dl class="page-facts sm:grid-cols-2">
                <div><dt>Diteruskan dari</dt><dd class="font-medium">{{ $disposisi->dariUser?->role_label ?? '-' }}</dd></div>
                <div><dt>Penerima tugas</dt><dd class="font-medium">{{ $disposisi->kepadaUser?->role_label ?? '-' }}</dd></div>
            </dl>
            <div class="page-note"><p class="font-semibold">Instruksi disposisi</p><p class="mt-2 whitespace-pre-line">{{ $disposisi->instruksi }}</p></div>
            @if($disposisi->tujuan_disposisi || $disposisi->penerima_lain)
                <div><h3 class="mb-2 font-semibold text-slate-800">Diteruskan kepada Sdr.</h3><div class="flex flex-wrap gap-2">
                    @foreach($disposisi->tujuan_disposisi ?? [] as $tujuan)<span class="page-badge page-badge-neutral">{{ $tujuan }}</span>@endforeach
                    @if($disposisi->penerima_lain)<span class="page-badge page-badge-neutral">Sdr. {{ $disposisi->penerima_lain }}</span>@endif
                </div></div>
            @endif
            @if($disposisi->harapan_instruksi)
                <div><h3 class="mb-2 font-semibold text-slate-800">Dengan hormat harap</h3><div class="flex flex-wrap gap-2">@foreach($disposisi->harapan_instruksi as $harapan)<span class="page-badge page-badge-info">{{ $harapan }}</span>@endforeach</div></div>
            @endif
            @if($disposisi->sifat_surat)
                <div><h3 class="mb-2 font-semibold text-slate-800">Sifat pada lembar disposisi</h3><div class="flex flex-wrap gap-2">@foreach($disposisi->sifat_surat as $sifat)<span class="page-badge page-badge-neutral">{{ ucfirst(str_replace('_', ' ', $sifat)) }}</span>@endforeach</div></div>
            @endif
            @if($disposisi->catatan)<div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="font-semibold text-slate-800">Catatan</h3><p class="mt-2 whitespace-pre-line text-slate-600">{{ $disposisi->catatan }}</p></div>@endif
            <dl class="page-facts border-t border-slate-200 pt-5 sm:grid-cols-2">
                <div><dt>Persetujuan penerima</dt><dd><x-ui.status :value="$disposisi->status_persetujuan" /></dd></div>
                <div><dt>Status tindak lanjut</dt><dd><x-ui.status :value="$disposisi->status" /></dd></div>
            </dl>
        </div>
    </section>
    @if(auth()->id() === $disposisi->kepada_user_id)
        <section class="page-panel">
            <div class="page-panel-heading"><div><h2 class="page-title">Tindak lanjuti disposisi</h2><p class="page-description">Berikan tanggapan setelah membaca surat dan instruksi di atas.</p></div></div>
            <div class="page-section space-y-6">
                @if($disposisi->status_persetujuan === 'pending')
                    <div>
                        <h3 class="mb-1 font-semibold text-slate-900">1. Tanggapan penerima</h3>
                        <p class="mb-4 text-slate-500">Konfirmasikan persetujuan Anda terhadap disposisi ini.</p>
                        <div class="flex flex-wrap gap-3">
                            <form action="{{ route('disposisi.persetujuan', $disposisi) }}" method="POST">@csrf<input type="hidden" name="persetujuan" value="setuju"><button type="submit" class="page-button page-button-success ui-action"><x-ui.icon name="check" /> Setujui disposisi</button></form>
                            <form action="{{ route('disposisi.persetujuan', $disposisi) }}" method="POST" onsubmit="return confirm('Tolak disposisi ini?')">@csrf<input type="hidden" name="persetujuan" value="tidak_setuju"><button type="submit" class="page-button page-button-danger ui-action"><x-ui.icon name="close" /> Tolak disposisi</button></form>
                        </div>
                    </div>
                @endif
                <form action="{{ route('disposisi.status', $disposisi) }}" method="POST" class="space-y-4 border-t border-slate-200 pt-5">
                    @csrf @method('PATCH')
                    <div><h3 class="font-semibold text-slate-900">{{ $disposisi->status_persetujuan === 'pending' ? '2. ' : '' }}Perbarui status tugas</h3><p class="mt-1 text-slate-500">Pilih Diproses saat mulai mengerjakan. Pilih Selesai untuk memindahkan tugas ke arsip disposisi.</p></div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="page-field w-full sm:max-w-xs"><label for="status-tugas" class="block">Status pengerjaan</label><select id="status-tugas" name="status" class="block w-full">
                            <option value="menunggu" @selected($disposisi->status === 'menunggu')>Menunggu</option>
                            <option value="diproses" @selected($disposisi->status === 'diproses')>Diproses</option>
                            <option value="selesai" @selected($disposisi->status === 'selesai')>Selesai</option>
                        </select>@error('status')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror</div>
                        <button type="submit" class="page-button page-button-primary ui-action"><x-ui.icon name="save" /> Simpan status tugas</button>
                    </div>
                </form>
            </div>
        </section>
    @endif
    @if((auth()->user()->isTU() || auth()->user()->isKepalaSekolah()) && $disposisi->suratMasuk?->suratKeluars->count() > 0)
        <section class="page-panel">
            <div class="page-panel-heading"><h2 class="page-title">Surat balasan terkait</h2></div>
            <div class="page-table-scroll"><table class="page-table-actions">
                <thead><tr><th scope="col">Nomor surat</th><th scope="col">Tujuan</th><th scope="col">Perihal</th><th scope="col">Status</th><th scope="col" class="text-right">Aksi</th></tr></thead>
                <tbody>@foreach($disposisi->suratMasuk->suratKeluars as $sk)<tr><td>{{ $sk->no_surat ?: 'Belum bernomor' }}</td><td>{{ $sk->tujuan }}</td><td>{{ $sk->perihal }}</td><td><x-ui.status :value="$sk->status" /></td><td><div class="page-actions"><x-ui.action icon="eye" label="Detail surat" tone="info" href="{{ route('surat-keluar.show', $sk) }}" />@if(auth()->user()->isTU() && $sk->no_surat)<x-ui.action icon="download" label="Unduh PDF" tone="info" href="{{ route('surat-keluar.pdf', $sk) }}" target="_blank" rel="noopener" />@endif</div></td></tr>@endforeach</tbody>
            </table></div>
        </section>
    @endif
</div>
@endsection
