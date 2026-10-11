@extends('layouts.sim')

@section('title', 'Buat Surat Keputusan (SK)')
@section('page-title', 'Buat Surat Keputusan (SK)')

@section('content')
<style>[x-cloak] { display: none !important; }</style>

@php
    $diktumAwal = old('dictum_list');
    $diktumAwal = is_array($diktumAwal) ? array_values(array_filter($diktumAwal, 'is_string')) : [];
    if ($diktumAwal === []) {
        $diktumAwal = [''];
    }
@endphp

<div class="sim-page max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Form Surat Keputusan</h2>
                <p class="text-xs text-slate-500">Isi identitas surat dan butir Menimbang / Mengingat / Menetapkan (diktum bisa ditambah tanpa batas). Nomor surat dapat diisi manual atau dibuat otomatis saat disetujui Kepala Sekolah.</p>
            </div>
            <a href="{{ route('surat-dinas.index') }}" class="page-button ui-action"><x-ui.icon name="back" /> Kembali ke Surat Dinas</a>
        </div>

        <form action="{{ route('surat-dinas.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="jenis_surat" value="SK">

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
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: 386 TAHUN 2026">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Ditetapkan</label>
                        <input type="date" name="tgl_ditetapkan" value="{{ old('tgl_ditetapkan') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tentang / Judul Surat <span class="text-red-500">*</span></label>
                        <input type="text" name="tentang_judul" value="{{ old('tentang_judul') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Contoh: Penetapan Panitia Pelaksana Ujian Sekolah Tahun 2026/2027">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat Ditetapkan</label>
                        <input type="text" name="tempat_ditetapkan" value="{{ old('tempat_ditetapkan', 'Tanjungpinang') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Pelajaran</label>
                        <input type="text" name="tahun_pelajaran" value="{{ old('tahun_pelajaran') }}" placeholder="2026/2027" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai Berlaku</label>
                        <input type="date" name="tanggal_mulai_berlaku" value="{{ old('tanggal_mulai_berlaku') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </fieldset>

            <!-- 2. Menimbang & Mengingat -->
            <fieldset class="border border-slate-200 rounded-xl p-5">
                <legend class="text-sm font-semibold text-slate-700 px-2">Menimbang &amp; Mengingat</legend>
                <div class="space-y-4 mt-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Menimbang <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(satu butir per baris)</span></label>
                        <textarea name="menimbang" rows="4" required
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                  placeholder="a. Bahwa untuk kelancaran pelaksanaan ujian sekolah perlu membentuk panitia.">{{ old('menimbang') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mengingat <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(satu butir per baris)</span></label>
                        <textarea name="mengingat" rows="3" required
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                  placeholder="1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional.">{{ old('mengingat') }}</textarea>
                    </div>
                </div>
            </fieldset>

            <!-- 3. Menetapkan (Diktum dinamis) -->
            <fieldset class="border border-slate-200 rounded-xl p-5"
                      x-data="{
                          dictums: {{ json_encode($diktumAwal) }},
                          labels: ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH', 'KESEBELAS', 'KEDUABELAS', 'KETIGABELAS', 'KEEMPATBELAS', 'KELIMABELAS', 'KEENAMBELAS', 'KETUJUHBELAS', 'KEDELAPANBELAS', 'KESEMBILANBELAS', 'KEDUAPULUH'],
                          getLabel(index) {
                              return this.labels[index] || ('KE-' + (index + 1));
                          }
                      }">
                <legend class="text-sm font-semibold text-slate-700 px-2">Menetapkan (Diktum)</legend>
                <div class="space-y-4 mt-3">
                    <p class="text-xs text-slate-500">Penamaan butir (KESATU, KEDUA, KETIGA, ...) dibuat otomatis sesuai urutan, tanpa batas jumlah diktum.</p>

                    <template x-for="(dictum, i) in dictums" :key="i">
                        <div class="border border-slate-100 bg-slate-50/60 rounded-xl p-3 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <label class="block text-sm font-semibold text-slate-700" x-text="getLabel(i) + ':'"></label>
                                <button type="button" @click="if (dictums.length > 1) dictums.splice(i, 1)"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium ui-action"><x-ui.icon name="trash" /> Hapus</button>
                            </div>
                            <textarea :name="'dictum_list[' + i + ']'" rows="2" x-model="dictums[i]" required
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                      placeholder="Contoh: Menetapkan susunan panitia ujian sekolah."></textarea>
                        </div>
                    </template>

                    <button type="button" @click="dictums.push('')"
                            class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-semibold flex items-center gap-1 ui-action">
                        <x-ui.icon name="plus" />
                        Tambah Diktum
                    </button>

                    <div class="pt-3 border-t border-slate-100">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tembusan <span class="text-slate-400 font-normal">(satu penerima per baris)</span></label>
                        <textarea name="tembusan" rows="3"
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('tembusan') }}</textarea>
                    </div>
                </div>
            </fieldset>

            <!-- 4. Aksi Simpan -->
            <div class="page-form-footer pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <span class="text-xs text-slate-400 mr-auto">Surat akan langsung diajukan ke Kepala Sekolah untuk persetujuan.</span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-sm transition ui-action"><x-ui.icon name="send" /> Ajukan ke Kepala Sekolah</button>
            </div>
        </form>
    </div>
</div>
@endsection
