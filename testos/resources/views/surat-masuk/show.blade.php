@extends('layouts.sim')

@section('title', 'Detail Surat Masuk')
@section('page-title', 'Detail Surat Masuk & Disposisi')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ auth()->user()->isTU() ? route('surat-masuk.index') : route('disposisi.antrian') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-blue-700 ui-action"><x-ui.icon name="back" /> {{ auth()->user()->isTU() ? 'Buku agenda surat masuk' : 'Pemantauan disposisi' }}</a>
    </div>

    <!-- Info Detail Surat Masuk -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-7 sm:py-6">
            <div>
                <span class="inline-flex rounded-md bg-blue-100 px-2.5 py-1 font-mono text-xs font-semibold text-blue-800">Agenda {{ $suratMasuk->nomor_urut }}</span>
                <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900">{{ $suratMasuk->perihal }}</h1>
                <p class="mt-1 text-sm text-slate-500">Nomor surat: {{ $suratMasuk->no_surat }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $suratMasuk->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ ucfirst($suratMasuk->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 text-sm md:grid-cols-2 sm:p-7">
            <div class="space-y-5">
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Nomor Surat Asli</span>
                    <span class="font-semibold text-slate-800">{{ $suratMasuk->no_surat }}</span>
                </div>
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Alamat Asal (Pengirim)</span>
                    <span class="font-semibold text-slate-800">{{ $suratMasuk->pengirim }}</span>
                </div>
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Tanggal Diterima</span>
                    <span class="font-semibold text-slate-800">{{ $suratMasuk->tanggal_diterima->format('d F Y') }}</span>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Tanggal Surat</span>
                    <span class="font-semibold text-slate-800">{{ $suratMasuk->tanggal_surat->format('d F Y') }}</span>
                </div>
                <div>
                    <span class="mb-2 block text-xs font-medium text-slate-500">Dokumen surat asli</span>
                    @if($suratMasuk->lampiran_path)
                        <a href="{{ route('surat-masuk.unduh', $suratMasuk) }}" class="inline-flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-blue-700 shadow-sm" aria-hidden="true">
                                <x-ui.icon name="download" size="md" />
                            </span>
                            <span class="text-left"><span class="block">Unduh surat asli</span><span class="mt-0.5 block text-xs font-normal text-blue-700">Simpan lampiran surat ini ke perangkat</span></span>
                        </a>
                    @else
                        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-500">Lampiran surat belum tersedia</span>
                    @endif
                </div>
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Disposisi (Tindak Lanjut/Surat Diteruskan Ke)</span>
                    <span class="font-semibold text-slate-800 whitespace-pre-line">{{ $suratMasuk->disposisi_tindak_lanjut ?: '—' }}</span>
                </div>
                <div>
                    <span class="mb-1 block text-xs font-medium text-slate-500">Keterangan</span>
                    <span class="font-semibold text-slate-800 whitespace-pre-line">{{ $suratMasuk->keterangan ?: '—' }}</span>
                </div>
            </div>

            @if($suratMasuk->isi_ringkas)
            <div class="border-t border-slate-100 pt-5 md:col-span-2">
                <span class="text-xs text-slate-400 block mb-1">Isi Ringkas Surat</span>
                <p class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">{{ $suratMasuk->isi_ringkas }}</p>
            </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->isTU() || auth()->user()->isKepalaSekolah())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-1">Pemantauan Disposisi</h2>
        @if(auth()->user()->isTU())
            @if($suratMasuk->diteruskan_ke_kepala_sekolah_at)
                <p class="text-sm text-emerald-700">Surat sudah diteruskan ke Kepala Sekolah pada {{ $suratMasuk->diteruskan_ke_kepala_sekolah_at->format('d/m/Y H:i') }}.</p>
            @else
                <p class="text-xs text-slate-500 mb-4">TU meneruskan surat ini ke Kepala Sekolah untuk diisi lembar disposisinya.</p>
                <form method="POST" action="{{ route('surat-masuk.teruskan-kepala-sekolah', $suratMasuk) }}" onsubmit="return confirm('Teruskan surat ini kepada Kepala Sekolah?')">
                    @csrf
                    <button class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold ui-action"><x-ui.icon name="send" /> Teruskan ke Kepala Sekolah</button>
                </form>
            @endif
        @elseif(auth()->user()->isKepalaSekolah())
            @if($suratMasuk->diteruskan_ke_kepala_sekolah_at && $suratMasuk->disposisis->isEmpty())
                <p class="text-xs text-slate-500 mb-4">Surat sudah diteruskan TU dan menunggu lembar disposisi Kepala Sekolah.</p>
                <a href="{{ route('disposisi.lembar.create', $suratMasuk) }}" class="inline-flex px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold ui-action"><x-ui.icon name="pencil" /> Isi Lembar Disposisi</a>
            @elseif($suratMasuk->diteruskan_ke_kepala_sekolah_at)
                <p class="text-sm text-emerald-700">Surat sudah didisposisikan kepada penerima.</p>
            @else
                <p class="text-sm text-amber-700">Surat belum diteruskan oleh TU.</p>
            @endif
        @endif
    </div>
    @endif

    <!-- Riwayat Disposisi Surat -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4">Riwayat & Status Disposisi</h2>

        @if($suratMasuk->disposisis->count() > 0)
            <div class="space-y-4">
                @foreach($suratMasuk->disposisis as $disp)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-slate-800">{{ $disp->kepadaUser?->role_label ?? 'Penerima disposisi' }}</span>
                                
                            </div>
                            <p class="text-xs font-medium text-blue-700">Instruksi: {{ $disp->instruksi }}</p>
                            @if($disp->catatan)
                                <p class="text-[11px] text-slate-500 mt-0.5">Catatan: {{ $disp->catatan }}</p>
                            @endif
                        </div>

<div class="flex flex-wrap items-center gap-3">
                            <x-ui.status :value="$disp->status" />
                            @if(auth()->id() === $disp->kepada_user_id || auth()->user()->isTU() || auth()->user()->isKepalaSekolah())
                                <x-ui.action icon="eye" label="Buka detail disposisi" tone="info" href="{{ route('disposisi.show', $disp) }}" />
                            @endif

                            <!-- Tombol Cetak Lembar Disposisi (Kepala Sekolah & Admin) -->
                            @if(auth()->user()->isKepalaSekolah())
                                <x-ui.action icon="printer" label="Cetak Lembar Disposisi" href="{{ route('disposisi.cetak', $disp->id) }}" target="_blank" />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-400 py-4 text-center">Surat ini belum memiliki catatan disposisi.</p>
        @endif
    </div>
</div>
@endsection
