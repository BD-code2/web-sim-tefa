@extends('layouts.sim')

@section('title', 'Edit Kerja Sama Industri')
@section('page-title', 'Edit Kerja Sama Industri')

@section('content')
<div class="sim-page max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Edit Kemitraan: {{ $kerjaSama->nama_industri }}</h2>
                <p class="text-xs text-slate-500">Perbarui informasi kemitraan, masa berlaku MoU, atau status.</p>
            </div>
            <a href="{{ route('kerja-sama.show', $kerjaSama) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 ui-action"><x-ui.icon name="close" /> Batal</a>
        </div>

        <form action="{{ route('kerja-sama.update', $kerjaSama) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Perusahaan / Industri *</label>
                    <input type="text" name="nama_industri" value="{{ old('nama_industri', $kerjaSama->nama_industri) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Perusahaan *</label>
                    <textarea name="alamat" rows="2" required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('alamat', $kerjaSama->alamat) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kontak Person</label>
                    <input type="text" name="kontak" value="{{ old('kontak', $kerjaSama->kontak) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Kemitraan</label>
                    <input type="email" name="email" value="{{ old('email', $kerjaSama->email) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Kemitraan *</label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="aktif" {{ $kerjaSama->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="akan_berakhir" {{ $kerjaSama->status === 'akan_berakhir' ? 'selected' : '' }}>Akan Berakhir</option>
                        <option value="berakhir" {{ $kerjaSama->status === 'berakhir' ? 'selected' : '' }}>Berakhir</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai Berlaku *</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kerjaSama->tanggal_mulai->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Berakhir *</label>
                    <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir', $kerjaSama->tanggal_berakhir->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ganti Dokumen MoU (Opsional)</label>
                    <input type="file" name="dokumen_mou"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('catatan', $kerjaSama->catatan) }}</textarea>
                </div>
            </div>

            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('kerja-sama.show', $kerjaSama) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition ui-action"><x-ui.icon name="close" /> Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm transition ui-action"><x-ui.icon name="save" /> Perbarui Kemitraan</button>
            </div>
        </form>
    </div>
</div>
@endsection
