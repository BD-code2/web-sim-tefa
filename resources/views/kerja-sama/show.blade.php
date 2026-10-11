@extends('layouts.sim')

@section('title', 'Detail Kerja Sama Industri')
@section('page-title', 'Detail Kerja Sama')

@section('content')
<div class="sim-page max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('kerja-sama.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 ui-action"><x-ui.icon name="back" /> Kembali ke Daftar Kemitraan</a>
        <div class="flex flex-wrap items-center justify-end gap-2">
            @if(auth()->user()->isHumas() && $kerjaSama->approval_status === 'pending')
                <form action="{{ route('kerja-sama.approve', $kerjaSama) }}" method="POST" onsubmit="return confirm('Setujui kemitraan ini?')">
                    @csrf
                    <x-ui.action icon="check" label="Setujui Kemitraan" tone="success" type="submit" />
                </form>
            @endif
            @if($periodePkl)
                <a href="{{ route('data-siswa.agenda.show', ['periode' => $periodePkl, 'search' => $kerjaSama->nama_industri]) }}" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="users" /> Kelola PKL Siswa</a>
            @endif
            <a href="{{ route('kerja-sama.edit', $kerjaSama) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="pencil" /> Edit Kemitraan</a>
            <form action="{{ route('kerja-sama.destroy', $kerjaSama) }}" method="POST" onsubmit="return confirm('Hapus kemitraan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="trash" /> Hapus</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-700 to-indigo-700 text-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider">Mitra DUDI SMKN 4</span>
                    <h1 class="text-2xl font-bold mt-2">{{ $kerjaSama->nama_industri }}</h1>
                    <p class="text-xs text-blue-100 mt-1">{{ $kerjaSama->alamat }}</p>
                </div>
                <div class="sm:text-right">
                    @if($kerjaSama->approval_status === 'pending')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-300 text-slate-900 shadow">Menunggu Persetujuan Humas</span>
                    @elseif($kerjaSama->approval_status === 'rejected')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-400 text-slate-900 shadow">Persetujuan Ditolak</span>
                    @elseif($kerjaSama->status === 'aktif')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-400 text-slate-900 shadow">Status: Aktif</span>
                    @elseif($kerjaSama->status === 'akan_berakhir')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-300 text-slate-900 shadow">Akan Berakhir</span>
                    @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-400 text-slate-900 shadow">Berakhir</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Informasi Kontak</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">Telepon / WhatsApp</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->kontak ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Email Perusahaan</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->email ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Masa Berlaku Perjanjian</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">Tanggal Mulai MoU</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->tanggal_mulai->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Tanggal Berakhir MoU</dt>
                        <dd class="font-medium text-slate-800">{{ $kerjaSama->tanggal_berakhir->format('d F Y') }}</dd>
                    </div>
                </dl>
            </div>

            @if($kerjaSama->catatan)
            <div class="md:col-span-2 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Catatan Tambahan</h3>
                <p class="text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl text-xs">{{ $kerjaSama->catatan }}</p>
            </div>
            @endif
        </div>
    </div>

    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="{ formAgenda: {{ $errors->any() ? 'true' : 'false' }} }">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800">Agenda Kerja Sama Industri</h2>
                <p class="mt-1 text-xs text-slate-500">Jadwal rapat, kegiatan, dan tenggat yang berkaitan dengan mitra ini.</p>
            </div>
            <button type="button" @click="formAgenda = !formAgenda" class="page-button bg-blue-600 hover:bg-blue-500 text-white ui-action">
                <x-ui.icon name="plus" /> Tambah Agenda
            </button>
        </div>

        <div x-show="formAgenda" x-cloak class="p-5 bg-slate-50 border-b border-slate-200">
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('kerja-sama.agenda.store', $kerjaSama) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Agenda *</label>
                    <input type="text" name="judul" value="{{ old('judul') }}" required maxlength="255" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Evaluasi pelaksanaan PKL">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu</label>
                    <input type="time" name="waktu" value="{{ old('waktu') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi') }}" maxlength="255" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Lokasi kegiatan">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Agenda *</label>
                    <select name="tipe" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="rapat" @selected(old('tipe') === 'rapat')>Rapat</option>
                        <option value="kegiatan" @selected(old('tipe', 'kegiatan') === 'kegiatan')>Kegiatan</option>
                        <option value="deadline" @selected(old('tipe') === 'deadline')>Deadline</option>
                        <option value="lainnya" @selected(old('tipe') === 'lainnya')>Lainnya</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 self-end min-h-11 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="is_penting" value="1" @checked(old('is_penting')) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Tandai sebagai agenda penting
                </label>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" maxlength="2000" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Keterangan atau tujuan agenda">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                    <button type="button" @click="formAgenda = false" class="page-button border border-slate-300 text-slate-600 hover:bg-white ui-action"><x-ui.icon name="close" /> Batal</button>
                    <button type="submit" class="page-button bg-blue-600 hover:bg-blue-500 text-white ui-action"><x-ui.icon name="save" /> Simpan Agenda</button>
                </div>
            </form>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($kerjaSama->agendas as $agenda)
                <article class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-800">{{ $agenda->judul }}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold capitalize">{{ $agenda->tipe }}</span>
                            @if($agenda->is_penting)<span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[11px] font-semibold">Penting</span>@endif
                        </div>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $agenda->tanggal_mulai->format('d/m/Y') }}
                            @if($agenda->tanggal_selesai) – {{ $agenda->tanggal_selesai->format('d/m/Y') }} @endif
                            @if($agenda->waktu) · {{ substr($agenda->waktu, 0, 5) }} @endif
                            @if($agenda->lokasi) · {{ $agenda->lokasi }} @endif
                        </p>
                        @if($agenda->deskripsi)<p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $agenda->deskripsi }}</p>@endif
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-ui.action icon="eye" label="Lihat Detail Agenda" tone="info" href="{{ route('kerja-sama.agenda.show', [$kerjaSama, $agenda]) }}" />
                        <x-ui.action icon="pencil" label="Edit Agenda" tone="warning" href="{{ route('kerja-sama.agenda.edit', [$kerjaSama, $agenda]) }}" />
                        <form action="{{ route('kerja-sama.agenda.destroy', [$kerjaSama, $agenda]) }}" method="POST" onsubmit="return confirm('Hapus agenda ini?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.action icon="trash" label="Hapus Agenda" tone="danger" type="submit" />
                        </form>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center text-sm text-slate-400">Belum ada agenda untuk kerja sama industri ini.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
