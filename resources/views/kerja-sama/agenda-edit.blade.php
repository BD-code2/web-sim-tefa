@extends('layouts.sim')

@section('title', 'Edit Agenda Industri')
@section('page-title', 'Edit Agenda Industri')

@section('content')
<div class="sim-page max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Edit Agenda</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $kerjaSama->nama_industri }}</p>
            </div>
            <a href="{{ route('kerja-sama.agenda.show', [$kerjaSama, $agenda]) }}" class="page-button border border-slate-300 text-slate-600 hover:bg-slate-50 ui-action"><x-ui.icon name="back" /> Kembali</a>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('kerja-sama.agenda.update', [$kerjaSama, $agenda]) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            @method('PATCH')
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Agenda *</label>
                <input type="text" name="judul" value="{{ old('judul', $agenda->judul) }}" required maxlength="255" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai *</label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $agenda->tanggal_mulai->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $agenda->tanggal_selesai?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu</label>
                <input type="time" name="waktu" value="{{ old('waktu', $agenda->waktu ? substr($agenda->waktu, 0, 5) : '') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi</label>
                <input type="text" name="lokasi" value="{{ old('lokasi', $agenda->lokasi) }}" maxlength="255" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Agenda *</label>
                <select name="tipe" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach(['rapat' => 'Rapat', 'kegiatan' => 'Kegiatan', 'deadline' => 'Deadline', 'lainnya' => 'Lainnya'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('tipe', $agenda->tipe) === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 self-end min-h-11 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_penting" value="1" @checked(old('is_penting', $agenda->is_penting)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Tandai sebagai agenda penting
            </label>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4" maxlength="2000" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('deskripsi', $agenda->deskripsi) }}</textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('kerja-sama.agenda.show', [$kerjaSama, $agenda]) }}" class="page-button border border-slate-300 text-slate-600 hover:bg-slate-50 ui-action"><x-ui.icon name="close" /> Batal</a>
                <button type="submit" class="page-button bg-blue-600 hover:bg-blue-500 text-white ui-action"><x-ui.icon name="save" /> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
