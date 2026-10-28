@extends('layouts.sim')

@section('title', 'Detail Surat Keluar')
@section('page-title', 'Detail Surat Keluar & Persetujuan')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ auth()->user()->isKepalaTU() ? route('surat-keluar.pemeriksaan-kepala-tu') : (auth()->user()->isKepalaSekolah() ? route('disposisi.antrian', ['tab' => 'persetujuan']) : route('surat-keluar.index')) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-blue-700 ui-action"><x-ui.icon name="back" /> {{ auth()->user()->isKepalaTU() ? 'Pemeriksaan Surat' : (auth()->user()->isKepalaSekolah() ? 'Daftar persetujuan surat' : 'Buku agenda surat keluar') }}</a>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isTU() && $suratKeluar->status === 'disetujui')
            <a href="{{ route('surat-keluar.pdf', $suratKeluar) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800 focus:outline-none focus:ring-4 focus:ring-rose-100 ui-action">
                <x-ui.icon name="download" />
                Cetak / unduh PDF
            </a>
            @endif
            @if(auth()->user()->isTU() && in_array($suratKeluar->status, ['draft', 'ditolak', 'minta_revisi']))
                <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 ui-action">
                    <x-ui.icon name="pencil" />
                    Edit Konsep
                </a>
            @endif
        </div>
    </div>

    <!-- Informasi Surat Keluar -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-7 sm:py-6">
            <div>
                <span class="inline-flex rounded-md bg-blue-100 px-2.5 py-1 font-mono text-xs font-semibold text-blue-800">
                    {{ $suratKeluar->no_surat ?? 'Draf (Belum Terbit)' }}
                </span>
                <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900">{{ $suratKeluar->perihal }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ ['undangan'=>'Surat Undangan','spt'=>'Surat Perintah Tugas','sk'=>'Surat Keputusan','mutasi'=>'Rekomendasi Mutasi'][$suratKeluar->jenis_surat] ?? 'Surat Undangan' }}</p>
            </div>
            <div>
                @if($suratKeluar->status === 'disetujui')
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Disetujui Kepala Sekolah</span>
                @elseif($suratKeluar->status === 'menunggu_persetujuan')
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $suratKeluar->diteruskan_ke_kepala_sekolah_at ? 'Menunggu persetujuan Kepala Sekolah' : ($suratKeluar->diperiksa_kepala_tu_at ? 'Sudah diperiksa, menunggu diteruskan TU' : 'Menunggu pemeriksaan Kepala TU') }}</span>
                @elseif(in_array($suratKeluar->status, ['ditolak', 'minta_revisi']))
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Ditolak (Perlu Revisi)</span>
                @else
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">{{ ucfirst($suratKeluar->status) }}</span>
                @endif
            </div>
        </div>

        @if($suratKeluar->status !== 'disetujui')
            <p class="border-b border-slate-100 px-5 py-3 text-xs text-slate-500 sm:px-7">PDF dapat dicetak atau diunduh setelah surat disetujui.</p>
        @endif
        <div class="grid grid-cols-1 gap-5 p-5 text-sm md:grid-cols-2 sm:p-7">
            <div class="space-y-5">
                <div>
                    <span class="text-xs text-slate-400 block">Tujuan Surat</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->tujuan }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Acara / Kegiatan</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->acara ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Pelaksanaan</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->tgl_pelaksanaan ? $suratKeluar->tgl_pelaksanaan->format('d F Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Waktu</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->waktu ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tempat Pelaksanaan</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->tempat ?? '-' }}</span>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <span class="text-xs text-slate-400 block">Dibuat Oleh</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->creator?->role_label ?? 'TU' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Disetujui Oleh</span>
                    <span class="font-semibold text-slate-800">{{ $suratKeluar->approver?->role_label ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Referensi Surat Masuk</span>
                    @if($suratKeluar->referensiSuratMasuk)
                        <a href="{{ route('surat-masuk.show', $suratKeluar->referensiSuratMasuk) }}" class="text-blue-600 font-semibold hover:underline">
                            {{ $suratKeluar->referensiSuratMasuk->no_agenda }} ({{ $suratKeluar->referensiSuratMasuk->pengirim }})
                        </a>
                    @else
                        <span class="text-slate-400">Tanpa referensi surat masuk</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Berkas Terlampir</span>
                    @if($suratKeluar->lampiran_path)
                        <a href="{{ asset('storage/' . $suratKeluar->lampiran_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 ui-action"><x-ui.icon name="eye" /> Lihat lampiran</a>
                    @else
                        <span class="text-slate-400">Tidak ada lampiran</span>
                    @endif
                </div>
            </div>

            @if($suratKeluar->isi_surat)
            <div class="md:col-span-2 pt-3 border-t border-slate-100">
                <span class="text-xs text-slate-400 block mb-1">Isi Surat</span>
                <p class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700 whitespace-pre-line">{{ $suratKeluar->isi_surat }}</p>
            </div>
            @endif

            @if($suratKeluar->catatan_revisi)
            <div class="md:col-span-2 pt-2">
                <span class="text-xs font-semibold text-rose-500 block mb-1">Catatan Persetujuan / Revisi:</span>
                <p class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-6 text-rose-800">{{ $suratKeluar->catatan_revisi }}</p>
            </div>
            @endif
        </div>
    </div>

    @include('surat-keluar.partials.detail-isian')

    @if((auth()->user()->isAdmin() || auth()->user()->isKepalaSekolah()) && $suratKeluar->status === 'menunggu_persetujuan' && $suratKeluar->diteruskan_ke_kepala_sekolah_at)
        <x-ui.approval :letter="$suratKeluar" kind="keluar" />
    @endif

    @if(auth()->user()->isTU() && $suratKeluar->status === 'menunggu_persetujuan' && $suratKeluar->diperiksa_kepala_tu_at && ! $suratKeluar->diteruskan_ke_kepala_sekolah_at)
        <section class="page-panel p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="font-semibold text-slate-900">Pemeriksaan Kepala TU selesai</h2><p class="mt-1 text-sm text-slate-500">Teruskan surat ini ke Kepala Sekolah untuk persetujuan akhir.</p></div>
            <form action="{{ route('surat-keluar.teruskan-kepala-sekolah', $suratKeluar) }}" method="POST">@csrf<x-ui.action icon="send" label="Teruskan ke Kepala Sekolah" tone="success" type="submit" /></form>
        </section>
    @endif

    <!-- Update Status Pengiriman / Arsip untuk TU -->
    @if(auth()->user()->isTU() && in_array($suratKeluar->status, ['disetujui', 'dikirim']))
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Tindak Lanjut Surat Keluar</h3>
            <p class="text-xs text-slate-500">Ubah status setelah dicetak dan dikirimkan ke alamat tujuan, lalu arsipkan.</p>
        </div>
        <div class="flex items-center gap-2">
            @if($suratKeluar->status === 'disetujui')
                <form action="{{ route('surat-keluar.status', $suratKeluar) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="dikirim">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="check" /> Tandai Sudah Dikirim</button>
                </form>
            @endif

            @if($suratKeluar->status === 'dikirim')
                <form action="{{ route('surat-keluar.status', $suratKeluar) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="diarsipkan">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="archive" /> Simpan di Arsip</button>
                </form>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
