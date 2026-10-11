@extends('layouts.sim')

@section('title', 'Edit Surat Keluar')
@section('page-title', 'Edit Konsep Surat Undangan / Umum')

@section('content')
<style>
    [data-surat-edit] input:not([type="hidden"]):not([type="file"]),
    [data-surat-edit] select,
    [data-surat-edit] textarea {
        border-color:#cbd5e1; border-radius:.75rem; background:#fff; color:#1e293b;
        box-shadow:0 1px 2px rgb(15 23 42 / .04); transition:border-color .18s ease, box-shadow .18s ease;
    }
    [data-surat-edit] input:focus,
    [data-surat-edit] select:focus,
    [data-surat-edit] textarea:focus { border-color:#3b82f6; outline:0; box-shadow:0 0 0 4px rgb(59 130 246 / .12); }
</style>
<div class="sim-page mx-auto max-w-5xl" data-surat-edit>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Edit Konsep Surat Undangan / Surat Umum</h2>
                <p class="text-xs text-slate-500">Nomor: <span class="font-mono font-semibold">{{ $suratKeluar->no_surat ?? '(Belum generate)' }}</span></p>
            </div>
            <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali</a>
        </div>

        <form action="{{ route('surat-keluar.update', $suratKeluar) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <p class="font-semibold mb-1">Data belum dapat disimpan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $pesanError)
                            <li>{{ $pesanError }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Referensi Surat Masuk -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Referensi Surat Masuk (Bila Ada / Balasan)</label>
                <select name="referensi_surat_masuk_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                    <option value="">-- Tidak Berdasarkan Surat Masuk (Surat Baru) --</option>
                    @foreach ($suratMasukList as $sm)
                        <option value="{{ $sm->id }}" {{ old('referensi_surat_masuk_id', $suratKeluar->referensi_surat_masuk_id) == $sm->id ? 'selected' : '' }}>
                            {{ $sm->no_agenda }} - {{ $sm->pengirim }} ({{ $sm->perihal }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Informasi Umum Surat -->
            <fieldset class="border border-slate-200 rounded-xl bg-slate-50/60 p-5 sm:p-6">
                <legend class="text-sm font-semibold text-slate-700 px-2">Informasi Umum Surat</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tujuan Surat <span class="text-red-500">*</span></label>
                        <input type="text" name="tujuan" value="{{ old('tujuan', $suratKeluar->tujuan) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Kepada Yth. Pimpinan PT / Instansi / Kepala Sekolah">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Perihal / Hal <span class="text-red-500">*</span></label>
                        <input type="text" name="perihal" value="{{ old('perihal', $suratKeluar->perihal) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: Undangan Rapat Koordinasi UKK SMK 2026">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Acara / Kegiatan</label>
                        <input type="text" name="acara" value="{{ old('acara', $suratKeluar->acara) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Pelaksanaan</label>
                        <input type="date" name="tgl_pelaksanaan" value="{{ old('tgl_pelaksanaan', $suratKeluar->tgl_pelaksanaan?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu</label>
                        <input type="text" name="waktu" value="{{ old('waktu', $suratKeluar->waktu) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: 08.00 WIB s.d. selesai">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat Pelaksanaan</label>
                        <input type="text" name="tempat" value="{{ old('tempat', $suratKeluar->tempat) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: Aula SMK Negeri 4 Tanjungpinang">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ganti Berkas Lampiran (Opsional)</label>
                        @if ($suratKeluar->lampiran_path)
                            <p class="text-xs text-slate-500 mb-1">Berkas saat ini: <a href="{{ asset('storage/'.$suratKeluar->lampiran_path) }}" target="_blank" class="text-blue-600 hover:underline ui-action"><x-ui.icon name="eye" /> lihat lampiran</a></p>
                        @endif
                        <input type="file" name="lampiran"
                               class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
            </fieldset>

            <!-- Isi Surat -->
            <fieldset class="border border-slate-200 rounded-xl bg-slate-50/60 p-5 sm:p-6">
                <legend class="text-sm font-semibold text-slate-700 px-2">Isi Surat</legend>
                <textarea name="isi_surat" rows="6" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none mt-4"
                          placeholder="Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri ...">{{ old('isi_surat', $suratKeluar->isi_surat) }}</textarea>
            </fieldset>

            <!-- Aksi Simpan -->
            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="submit" name="action" value="draft" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition ui-action"><x-ui.icon name="save" /> Simpan Draf Saja</button>
                <button type="submit" name="action" value="ajukan" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm transition ui-action"><x-ui.icon name="send" /> Ajukan ke Kepala TU</button>
            </div>
        </form>
    </div>
</div>
@endsection
