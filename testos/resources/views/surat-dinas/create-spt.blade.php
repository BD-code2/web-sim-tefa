@extends('layouts.sim')

@section('title', 'Buat Surat Perintah Tugas (SPT)')
@section('page-title', 'Buat Surat Perintah Tugas (SPT)')

@section('content')
<style>[x-cloak] { display: none !important; }</style>

@php
    $pesertaAwal = old('peserta_list');
    if (! is_array($pesertaAwal)) {
        $pesertaAwal = [];
    }
    $pesertaAwal = array_values(array_map(fn ($baris) => [
        'nama' => is_array($baris) ? ($baris['nama'] ?? '') : '',
        'kelas' => is_array($baris) ? ($baris['kelas'] ?? '') : '',
    ], $pesertaAwal));
    if ($pesertaAwal === []) {
        $pesertaAwal = [['nama' => '', 'kelas' => '']];
    }
@endphp

<div class="sim-page max-w-4xl mx-auto" x-data="{ siswa: {{ json_encode($pesertaAwal) }} }" x-cloak>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Form Surat Perintah Tugas</h2>
                <p class="text-xs text-slate-500">Isi identitas surat, pegawai yang ditugaskan, rincian tugas, dan daftar peserta. Surat akan diajukan kepada Kepala Sekolah setelah disimpan.</p>
            </div>
            <a href="{{ route('surat-dinas.index') }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali ke Surat Dinas</a>
        </div>

        <form action="{{ route('surat-dinas.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="jenis_surat" value="SPT">

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

            <!-- 1. Identitas Surat -->
            <fieldset class="border border-slate-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Identitas Surat</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">No. Surat <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="no_surat" value="{{ old('no_surat') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                               placeholder="Contoh: 400.3.8/115/2026">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Ditetapkan</label>
                        <input type="date" name="tgl_ditetapkan" value="{{ old('tgl_ditetapkan') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tentang / Judul Surat <span class="text-red-500">*</span></label>
                        <input type="text" name="tentang_judul" value="{{ old('tentang_judul') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                               placeholder="Contoh: Pengawasan Ujian Sekolah Tahun Pelajaran 2026/2027">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat Ditetapkan</label>
                        <input type="text" name="tempat_ditetapkan" value="{{ old('tempat_ditetapkan', 'Tanjungpinang') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>
            </fieldset>

            <!-- 2. Dasar -->
            <fieldset class="border border-slate-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Dasar</legend>
                <div class="mt-3">
                    <textarea name="dasar" rows="3"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                              placeholder="1. Surat Edaran Kepala Dinas Pendidikan Nomor ...&#10;2. Petunjuk Teknis Pelaksanaan ...">{{ old('dasar') }}</textarea>
                </div>
            </fieldset>

            <!-- 3. Pegawai yang Ditugaskan -->
            <fieldset class="border border-slate-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Pegawai yang Ditugaskan</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="pegawai_nama" value="{{ old('pegawai_nama') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                               placeholder="Contoh: Rina Wati, S.Pd.">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pangkat / Golongan</label>
                        <input type="text" name="pegawai_pangkat_golongan" value="{{ old('pegawai_pangkat_golongan') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                               placeholder="Contoh: Penata Tk. I / III.d">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIP / NIPPPK</label>
                        <input type="text" name="pegawai_nip" value="{{ old('pegawai_nip') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                        <input type="text" name="pegawai_jabatan" value="{{ old('pegawai_jabatan') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                               placeholder="Contoh: Guru Mata Pelajaran Bahasa Indonesia">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat dan tanggal penerbitan</label>
                        <input type="date" name="tgl_ditetapkan" value="{{ old('tgl_ditetapkan', now()->toDateString()) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </fieldset>

            <!-- 4. Rincian Tugas -->
            <fieldset class="border border-slate-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Untuk (Rincian Tugas)</legend>
                <div class="space-y-4 mt-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Melaksanakan Tugas <span class="text-red-500">*</span></label>
                        <textarea name="untuk_tugas" rows="2" required
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                  placeholder="Contoh: Mengawasi pelaksanaan Ujian Sekolah kelas XII.">{{ old('untuk_tugas') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat</label>
                            <input type="text" name="untuk_tempat" value="{{ old('untuk_tempat') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                   placeholder="Gedung A">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="untuk_tanggal_mulai" value="{{ old('untuk_tanggal_mulai') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                            <input type="date" name="untuk_tanggal_selesai" value="{{ old('untuk_tanggal_selesai') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- 5. Daftar Peserta -->
            <fieldset class="border border-amber-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Daftar Siswa / Peserta</legend>
                <div class="flex items-center justify-between mb-3 mt-3">
                    <p class="text-xs text-slate-500">Baris kosong otomatis tidak dikirim.</p>
                    <button type="button" @click="siswa.push({ nama: '', kelas: '' })"
                            class="text-xs px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200 transition font-semibold flex items-center gap-1 ui-action">
                        <x-ui.icon name="plus" />
                        Tambah Peserta
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="page-table-actions w-full text-sm">
                        <thead class="bg-amber-50">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-left font-semibold text-slate-600 border border-amber-200 w-12">No</th>
                                <th scope="col" class="px-3 py-2 text-left font-semibold text-slate-600 border border-amber-200">Nama Lengkap</th>
                                <th scope="col" class="px-3 py-2 text-left font-semibold text-slate-600 border border-amber-200 w-44">Kelas</th>
                                <th scope="col" class="px-3 py-2 text-center font-semibold text-slate-600 border border-amber-200 w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in siswa" :key="index">
                                <tr class="border-t border-amber-100 align-top">
                                    <td class="px-3 py-2 border border-amber-200 text-center text-slate-500" x-text="index + 1"></td>
                                    <td class="px-3 py-2 border border-amber-200">
                                        <input type="text" :name="'peserta_list[' + index + '][nama]'" x-model="row.nama"
                                               class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                               placeholder="Nama lengkap siswa">
                                    </td>
                                    <td class="px-3 py-2 border border-amber-200">
                                        <input type="text" :name="'peserta_list[' + index + '][kelas]'" x-model="row.kelas"
                                               class="w-full px-3 py-1.5 rounded border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                               placeholder="Contoh: XII RPL 1">
                                    </td>
                                    <td class="px-3 py-2 border border-amber-200 text-center">
                                        <x-ui.action icon="trash" label="Hapus" tone="danger" type="button" @click="siswa.splice(index, 1)" />
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </fieldset>

            <!-- 6. Aksi Simpan -->
            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <span class="text-xs text-slate-400 mr-auto">Surat akan langsung diajukan ke Kepala Sekolah untuk persetujuan.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm transition ui-action"><x-ui.icon name="send" /> Ajukan ke Kepala Sekolah</button>
            </div>
        </form>
    </div>
</div>
@endsection
