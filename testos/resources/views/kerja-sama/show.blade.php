@extends('layouts.sim')

@section('title', 'Detail Kerja Sama Industri')
@section('page-title', 'Detail Kerja Sama')

@section('content')
<div class="sim-page max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('kerja-sama.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 ui-action"><x-ui.icon name="back" /> Kembali ke Daftar Kemitraan</a>
        <div class="flex items-center gap-2">
            <a href="{{ route('kerja-sama.edit', $kerjaSama) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="pencil" /> Edit Kemitraan</a>
            <form action="{{ route('kerja-sama.destroy', $kerjaSama) }}" method="POST" onsubmit="return confirm('Hapus kemitraan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="trash" /> Hapus</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-700 to-indigo-700 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider">Mitra DUDI SMKN 4</span>
                    <h1 class="text-2xl font-bold mt-2">{{ $kerjaSama->nama_industri }}</h1>
                    <p class="text-xs text-blue-100 mt-1">{{ $kerjaSama->alamat }}</p>
                </div>
                <div class="text-right">
                    @if($kerjaSama->approval_status === 'pending')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-300 text-slate-900 shadow">Menunggu Persetujuan Waka Humas</span>
                    @elseif($kerjaSama->approval_status === 'rejected')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-400 text-slate-900 shadow">Persetujuan Ditolak</span>
                    @elseif($kerjaSama->status === 'aktif')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-400 text-slate-900 shadow">Status: Aktif</span>
                    @elseif($kerjaSama->status === 'akan_berakhir')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-300 text-slate-900 shadow">Akan Berakhir</span>
                    @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-400 text-slate-900 shadow">Berakhir</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Informasi Kontak</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">Telepon / WhatsApp</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->kontak ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Email Perusahaan</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->email ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Masa Berlaku Perjanjian</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">Tanggal Mulai MoU</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->tanggal_mulai->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Tanggal Berakhir MoU</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->tanggal_berakhir->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Dokumen Perjanjian (MoU)</dt>
                        <dd class="font-medium text-slate-800">
                            @if($kerjaSama->dokumen_mou_path)
                                <a href="{{ asset('storage/' . $kerjaSama->dokumen_mou_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-blue-600 font-semibold hover:underline ui-action">
                                    <x-ui.icon name="download" />
                                    Unduh Dokumen MoU
                                </a>
                            @else
                                <span class="text-slate-400 text-xs">Belum ada file dokumen diunggah</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if($kerjaSama->catatan)
            <div class="md:col-span-2 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Catatan Tambahan</h3>
                <p class="text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl text-xs">{{ $kerjaSama->catatan }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
