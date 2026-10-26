@extends('layouts.sim')

@section('title', 'Import Data Siswa')
@section('page-title', 'Import Data Siswa ke Agenda PKL')

@section('content')
<div class="sim-page space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">Pilih file daftar siswa Excel untuk agenda yang sedang dibuka.</p>
        <a href="{{ route('data-siswa.agenda.show', $agendaTerpilih) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 ui-action"><x-ui.icon name="back" /> Kembali ke Agenda</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('data-siswa.import.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Agenda / Periode PKL</label>
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">{{ $agendaTerpilih->nama }} ({{ $agendaTerpilih->tanggal_mulai->format('d/m/Y') }} - {{ $agendaTerpilih->tanggal_selesai->format('d/m/Y') }})</p>
                <input type="hidden" name="periode_pkl_id" value="{{ $agendaTerpilih->id }}">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">File Data Siswa (.xlsx / .xls / .csv)</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500">Ukuran maksimal 2 MB.</p>
                @error('file')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-700">Format Excel yang diterima</p>
                <ul class="space-y-1 text-xs text-slate-600">
                    <li><span class="font-mono font-semibold text-blue-700">nis / NISN</span> untuk nomor siswa</li>
                    <li><span class="font-mono font-semibold text-blue-700">nama_lengkap / Nama Lengkap</span> untuk nama siswa</li>
                    <li><span class="font-mono font-semibold text-blue-700">kelas</span> untuk mengelompokkan siswa</li>
                    <li><span class="font-mono font-semibold text-blue-700">industri</span> untuk menentukan tempat PKL siswa</li>
                </ul>
                <p class="mt-2 text-xs text-slate-500">Kolom kelas dan industri wajib tersedia untuk proses PKL. Tambahkan kolom Tempat / Tanggal Lahir (gabungan), atau Tempat Lahir dan Tanggal Lahir (terpisah), agar data lahir tampil di sertifikat. Impor ulang di agenda yang sama memperbarui data siswa dan data lahir sertifikat yang sudah dibuat.</p>
            </div>
            <div class="page-form-footer">
                <button type="submit" class="rounded-xl bg-blue-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 ui-action"><x-ui.icon name="upload" /> Import Siswa</button>
                <a href="{{ route('data-siswa.agenda.show', $agendaTerpilih) }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 ui-action"><x-ui.icon name="close" /> Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
