@extends('layouts.sim')

@section('title', 'Buat Surat Otomatis')
@section('page-title', 'Buat Surat Keluar Otomatis')

@section('content')
<style>
    [x-cloak]{display:none!important}
    [data-surat-generator] label { display:block; color:#475569; font-size:.8rem; font-weight:600; line-height:1.4; }
    [data-surat-generator] input:not([type="hidden"]),
    [data-surat-generator] select,
    [data-surat-generator] textarea:not(.hidden) {
        display:block; width:100%; margin-top:.4rem; border:1px solid #cbd5e1; border-radius:.75rem;
        background:#fff; padding:.7rem .85rem; color:#1e293b; font-size:.875rem; line-height:1.5;
        box-shadow:0 1px 2px rgb(15 23 42 / .04); transition:border-color .18s ease, box-shadow .18s ease;
    }
    [data-surat-generator] input:focus,
    [data-surat-generator] select:focus,
    [data-surat-generator] textarea:focus { border-color:#3b82f6; outline:0; box-shadow:0 0 0 4px rgb(59 130 246 / .12); }
    [data-surat-generator] textarea:not(.hidden) { min-height:7rem; resize:vertical; }
    [data-surat-generator] [data-panel-surat] { border-color:#e2e8f0; background:#f8fafc; }
    [data-surat-generator] [data-panel-surat] > h3 { border-bottom:1px solid #e2e8f0; padding-bottom:.8rem; color:#0f172a; font-size:.95rem; }
</style>
<div class="sim-page mx-auto max-w-5xl space-y-6" data-surat-generator>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/80 px-6 py-5 sm:px-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[.16em] text-blue-700">Surat keluar</p>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Buat surat</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">Pilih jenis surat, lalu isi data yang diperlukan. Kop dan bagian tetap surat dibuat otomatis; nomor surat dapat diisi sendiri atau dibuat saat persetujuan.</p>
        </div>
        @if($errors->any())
            <div id="form-errors" role="alert" tabindex="-1" class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-8">
                <strong>Surat belum dikirim. Periksa data berikut:</strong>
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form action="{{ route('surat-keluar.store') }}" method="POST" class="space-y-6 p-6 sm:p-8">
            @csrf
            <section class="grid gap-4 rounded-xl border border-blue-100 bg-blue-50/50 p-5 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] sm:items-end">
                <label>Jenis surat
                    <select name="jenis_surat" id="jenis_surat" @if($kategori) data-selected="{{ strtolower($kategori) }}" @endif>
                        <option value="undangan" @selected(old('jenis_surat', $kategori ?? 'undangan') === 'undangan')>Surat Undangan</option>
                        <option value="mutasi" @selected(old('jenis_surat', $kategori ?? '') === 'mutasi')>Rekomendasi Mutasi Siswa</option>
                    </select>
                </label>
                <p class="text-xs leading-5 text-slate-500">Bagian isian menyesuaikan jenis surat. Surat Keputusan dan Surat Perintah Tugas dibuat melalui halaman Surat Dinas.</p>
            </section>
            <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
                <label class="text-sm">Nomor surat (manual, opsional)<input name="no_surat" value="{{ old('no_surat') }}" class="mt-1 w-full rounded-xl border-slate-300"><span class="mt-1 block text-xs text-slate-500">Kosongkan agar nomor dibuat otomatis saat surat disetujui.</span></label>
                <label class="text-sm">Tanggal surat / penetapan<input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', now()->toDateString()) }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
            </div>
            <aside class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-950">
                <strong>Diisi otomatis pada PDF:</strong> kop dan logo sekolah, alamat dan kontak sekolah, sifat surat, lampiran, susunan/kalimat baku, tempat penerbitan Tanjungpinang, serta nama, jabatan, pangkat, dan NIP Kepala Sekolah.
            </aside>
            <div data-panel-surat="undangan" class="space-y-4 rounded-xl border p-5">
                <h3 class="font-semibold">Data undangan</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm">Nama penerima/orang tua/wali<input name="tujuan" value="{{ old('tujuan') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">Hal surat otomatis: <strong>Undangan</strong></div>
                    <textarea name="isi_surat" class="hidden" aria-hidden="true"></textarea>
                    <label class="text-sm">Nama siswa<input name="nama_siswa" value="{{ old('nama_siswa') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Kelas<input name="kelas_siswa" value="{{ old('kelas_siswa') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Tahun pelajaran<input name="tahun_pelajaran" value="{{ old('tahun_pelajaran') }}" placeholder="2026/2027" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm sm:col-span-2">Alamat penerima<textarea name="alamat_penerima" required class="mt-1 w-full rounded-xl border-slate-300">{{ old('alamat_penerima') }}</textarea></label>
                    <label class="text-sm">Nama kegiatan<input name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" placeholder="Contoh: pengambilan rapor" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Keperluan<input name="keperluan" value="{{ old('keperluan') }}" placeholder="Contoh: telah dilaksanakannya PAS Ganjil" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Hari/tanggal kegiatan<input type="date" name="tgl_pelaksanaan" value="{{ old('tgl_pelaksanaan') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Waktu<input name="waktu" value="{{ old('waktu') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm sm:col-span-2">Tempat<input name="tempat" value="{{ old('tempat') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                </div>
            </div>
            <div data-panel-surat="mutasi" hidden inert class="space-y-4 rounded-xl border p-5">
                <h3 class="font-semibold">Data rekomendasi mutasi siswa</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm">Nama siswa<input name="nama_siswa" value="{{ old('nama_siswa') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">NIP/NISN<input name="nisn" value="{{ old('nisn') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Kelas<input name="kelas_siswa" value="{{ old('kelas_siswa') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Semester<input name="semester" value="{{ old('semester') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Tahun pelajaran<input name="tahun_pelajaran" value="{{ old('tahun_pelajaran') }}" placeholder="2026/2027" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Nama orang tua/wali<input name="nama_orang_tua" value="{{ old('nama_orang_tua') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Alamat orang tua/wali<textarea name="alamat_orang_tua" required class="mt-1 w-full rounded-xl border-slate-300">{{ old('alamat_orang_tua') }}</textarea></label>
                    <label class="text-sm">Tempat lahir siswa<input name="tempat_lahir" value="{{ old('tempat_lahir') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Tanggal lahir siswa<input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Nama ayah (opsional)<input name="nama_ayah" value="{{ old('nama_ayah') }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Pekerjaan ayah (opsional)<input name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah') }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Nama ibu (opsional)<input name="nama_ibu" value="{{ old('nama_ibu') }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Pekerjaan ibu (opsional)<input name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu') }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Sekolah asal<input name="sekolah_asal" value="{{ old('sekolah_asal') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm">Alamat sekolah asal<textarea name="alamat_sekolah_asal" required class="mt-1 w-full rounded-xl border-slate-300">{{ old('alamat_sekolah_asal') }}</textarea></label>
                    <label class="text-sm">Sekolah tujuan<input name="sekolah_tujuan" value="{{ old('sekolah_tujuan') }}" required class="mt-1 w-full rounded-xl border-slate-300"></label>
                    <label class="text-sm sm:col-span-2">Alasan mutasi<textarea name="alasan_mutasi" required class="mt-1 w-full rounded-xl border-slate-300">{{ old('alasan_mutasi') }}</textarea></label>
                    <label class="text-sm sm:col-span-2">Dokumen lampiran<textarea name="dokumen_lampiran" required class="mt-1 w-full rounded-xl border-slate-300">{{ old('dokumen_lampiran') }}</textarea></label>
                </div>
            </div>
            <div class="page-form-footer flex justify-end border-t border-slate-200 pt-6">
                <button type="submit" class="rounded-xl bg-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 active:scale-[.98] ui-action"><x-ui.icon name="send" /> Buat Surat &amp; Ajukan ke Kepala TU</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('jenis_surat');
    if (!select) return;
    if (select.dataset.selected) select.value = select.dataset.selected;
    const updatePanels = () => {
        document.querySelectorAll('[data-panel-surat]').forEach((panel) => {
            const aktif = panel.dataset.panelSurat === select.value;
            panel.hidden = !aktif;
            panel.inert = !aktif;
            panel.querySelectorAll('input, select, textarea, button').forEach((field) => {
                if (!field.name) return;
                field.disabled = !aktif;
            });
        });
    };
    select.addEventListener('change', updatePanels);
    updatePanels();
    const errors = document.getElementById('form-errors');
    if (errors) {
        errors.focus({ preventScroll: true });
        errors.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endsection
