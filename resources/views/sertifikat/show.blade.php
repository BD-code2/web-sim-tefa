@extends('layouts.sim')

@section('title', 'Detail Sertifikat PKL')
@section('page-title', 'Detail Sertifikat PKL')

@section('content')
<div class="sim-page max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('sertifikat.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 ui-action"><x-ui.icon name="back" /> Kembali ke Daftar</a>
        <div class="flex flex-wrap items-center gap-2">
            @if($sertifikat->status === 'diterbitkan')
                <a href="{{ route('sertifikat.pdf', $sertifikat) }}" target="_blank" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 ui-action">
                    <x-ui.icon name="printer" />
                    Cetak Sertifikat PDF
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-4">
            <div>
                <span class="font-mono text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">
                    {{ $sertifikat->no_sertifikat ?? 'Draf Sertifikat' }}
                </span>
                <h1 class="text-xl font-bold text-slate-800 mt-2">{{ $sertifikat->nama_siswa }}</h1>
                <p class="text-xs text-slate-500">NISN: {{ $sertifikat->nisn }} | Kelas: {{ $sertifikat->kelas }} ({{ $sertifikat->program_keahlian }})</p>
            </div>
            <div>
                @if($sertifikat->status === 'diterbitkan')
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Diterbitkan Resmi</span>
                @elseif($sertifikat->status === 'terverifikasi')
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Terverifikasi</span>
                @else
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Draft</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 text-sm">
            <div class="space-y-3">
                <div>
                    <span class="text-xs text-slate-400 block">Mitra Industri (DUDI)</span>
                    <span class="font-semibold text-slate-800">{{ $sertifikat->industri->nama_industri ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Periode Pelaksanaan PKL</span>
                    <span class="font-semibold text-slate-800">
                        {{ $sertifikat->tanggal_mulai_pkl->format('d M Y') }} s/d {{ $sertifikat->tanggal_selesai_pkl->format('d M Y') }}
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-xs text-slate-400 block">Nilai Praktik & Predikat</span>
                    <span class="text-lg font-extrabold text-blue-600">{{ $sertifikat->nilai ?? '-' }}</span>
                    <span class="text-xs text-slate-600 font-semibold block">{{ $sertifikat->predikat ?? '' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Diterbitkan Pada</span>
                    <span class="font-semibold text-slate-800">{{ $sertifikat->diterbitkan_pada ? $sertifikat->diterbitkan_pada->format('d F Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Verifikasi & Terbitkan (Humas / Kepsek / Admin) -->
    @if(!auth()->user()->isSiswa())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Tahap Pengesahan Sertifikat</h3>
            <p class="text-xs text-slate-500">Verifikasi kelengkapan nilai, lalu terbitkan secara resmi agar dapat dicetak.</p>
        </div>

        <div class="flex items-center gap-3">
            @if($sertifikat->status === 'draft')
                <form action="{{ route('sertifikat.status', $sertifikat) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="terverifikasi">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow transition ui-action"><x-ui.icon name="check" /> Verifikasi Berkas PKL</button>
                </form>
            @endif

            @if($sertifikat->status === 'terverifikasi')
                <form action="{{ route('sertifikat.status', $sertifikat) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="diterbitkan">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition ui-action"><x-ui.icon name="certificate" /> Terbitkan Sertifikat Resmi</button>
                </form>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
