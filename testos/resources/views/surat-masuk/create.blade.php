@extends('layouts.sim')

@section('title', 'Catat Surat Masuk')
@section('page-title', 'Catat Surat Masuk Baru')

@section('content')
<div class="sim-page max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Buku Agenda Surat Masuk</h2>
                <p class="text-xs text-slate-500">Isi data mengikuti kolom pada format buku agenda.</p>
            </div>
            <a href="{{ route('surat-masuk.index') }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali</a>
        </div>

        <form action="{{ route('surat-masuk.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <x-ui.form-errors />
            <p class="text-sm text-slate-500">Isian bertanda * wajib diisi. Nomor urut dibuat otomatis oleh sistem.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Urut</label>
                    <input type="text" value="Otomatis oleh sistem" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Surat *</label>
                    <input type="text" name="no_surat" value="{{ old('no_surat') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nomor pada surat">
                    @error('no_surat')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Surat *</label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('tanggal_surat')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Diterima *</label>
                    <input type="date" name="tanggal_diterima" value="{{ old('tanggal_diterima', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('tanggal_diterima')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Asal (Pengirim) *</label>
                    <textarea name="pengirim" rows="2" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nama instansi dan alamat pengirim">{{ old('pengirim') }}</textarea>
                    @error('pengirim')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Perihal *</label>
                    <input type="text" name="perihal" value="{{ old('perihal') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Perihal surat">
                    @error('perihal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Disposisi (Tindak Lanjut/Surat Diteruskan Ke)</label>
                    <textarea name="disposisi_tindak_lanjut" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Tindak lanjut atau tujuan penerusan surat">{{ old('disposisi_tindak_lanjut') }}</textarea>
                    @error('disposisi_tindak_lanjut')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Keterangan tambahan">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Dokumen Surat Asli untuk Diunduh (opsional)</label>
                    <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700">
                    <p class="mt-1 text-[11px] text-slate-500">Berkas ini tersedia untuk penerima disposisi dan tidak menjadi kolom buku agenda.</p>
                    @error('lampiran')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('surat-masuk.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm font-semibold hover:bg-slate-50 ui-action"><x-ui.icon name="close" /> Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm ui-action"><x-ui.icon name="save" /> Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>
@endsection
