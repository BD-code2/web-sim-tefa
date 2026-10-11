@extends('layouts.sim')

@section('title', 'Edit Nama Agenda PKL')
@section('page-title', 'Edit Nama Agenda PKL')

@section('content')
<div class="sim-page mx-auto max-w-2xl">
    <form method="POST" action="{{ route('data-siswa.periode.update', $periode) }}" class="page-panel page-section space-y-5">
        @csrf
        @method('PATCH')
        <div>
            <h2 class="page-title">Ubah nama agenda</h2>
            <p class="page-description">Perubahan nama akan tampil pada arsip agenda dan dokumen sertifikat kelas berikutnya.</p>
        </div>
        <x-ui.form-errors />
        <div class="page-field">
            <label for="nama-agenda" class="block">Nama agenda PKL <span class="text-rose-600">*</span></label>
            <input id="nama-agenda" name="nama" value="{{ old('nama', $periode->nama) }}" required maxlength="150" class="block w-full" autofocus>
        </div>
        <div class="page-form-footer">
            <a href="{{ route('data-siswa.index') }}" class="page-button ui-action"><x-ui.icon name="close" /> Batal</a>
            <button type="submit" class="page-button page-button-primary ui-action"><x-ui.icon name="save" /> Simpan perubahan</button>
        </div>
    </form>
</div>
@endsection
