@extends('layouts.sim')

@section('title', 'Buat Agenda / Periode PKL')
@section('page-title', 'Buat Agenda / Periode PKL')

@section('content')
<div class="sim-page mx-auto max-w-3xl">
    <form method="POST" action="{{ route('data-siswa.periode.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8 shadow-sm">
        @csrf
        <div>
            <h2 class="text-lg font-bold text-slate-800">Agenda dan Periode PKL Baru</h2>
            <p class="mt-1 text-sm text-slate-500">Buat periode terlebih dahulu. Data siswa Excel nantinya akan dimasukkan ke periode ini.</p>
        </div>
        @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <label class="block text-sm font-semibold text-slate-700">Nama Periode PKL *<input name="nama" value="{{ old('nama') }}" required maxlength="150" class="mt-1 block w-full rounded-xl border-slate-300" placeholder="Contoh: PKL Semester Ganjil 2026/2027"></label>
        <label class="block text-sm font-semibold text-slate-700">Nama / Agenda PKL<textarea name="agenda" rows="3" class="mt-1 block w-full rounded-xl border-slate-300" placeholder="Agenda atau keterangan kegiatan PKL">{{ old('agenda') }}</textarea></label>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700">Tanggal Mulai *<input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required class="mt-1 block w-full rounded-xl border-slate-300"></label>
            <label class="block text-sm font-semibold text-slate-700">Tanggal Selesai *<input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required class="mt-1 block w-full rounded-xl border-slate-300"></label>
        </div>
        <div class="page-form-footer flex justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('data-siswa.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 ui-action"><x-ui.icon name="close" /> Batal</a>
            <button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 ui-action"><x-ui.icon name="save" /> Simpan Periode PKL</button>
        </div>
    </form>
</div>
@endsection
