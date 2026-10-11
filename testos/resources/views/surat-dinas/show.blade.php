@extends('layouts.sim')

@section('title', 'Detail Surat Dinas')
@section('page-title', 'Detail Surat Dinas')

@section('content')
@php
    $detail = $suratDinas->detail_json ?? [];
    $daftarBaris = fn ($teks) => array_values(array_filter(
        array_map('trim', preg_split('/\r\n|\r|\n/', (string) $teks)),
        fn ($baris) => $baris !== ''
    ));
@endphp

<div class="sim-page max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ auth()->user()->isTU() ? route('surat-dinas.index') : route('disposisi.antrian', ['tab' => 'persetujuan']) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 ui-action"><x-ui.icon name="back" /> {{ auth()->user()->isTU() ? 'Kembali ke Daftar Surat Dinas' : 'Kembali ke Pemantauan Disposisi' }}</a>
        @if(auth()->user()->isTU() && $suratDinas->status === 'disetujui')
            <a href="{{ route('surat-dinas.pdf', $suratDinas) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow transition ui-action">
                <x-ui.icon name="printer" />
                Cetak PDF
            </a>
        @elseif(auth()->user()->isTU())
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold ui-action" title="Cetak PDF aktif setelah Surat Dinas disetujui Kepala Sekolah." aria-disabled="true">
                <x-ui.icon name="printer" />
                Cetak PDF (setelah disetujui)
            </span>
        @endif
    </div>

    <!-- Identitas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex flex-wrap items-center gap-3 mb-4 pb-4 border-b border-slate-100">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $suratDinas->jenis_surat === 'SK' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $suratDinas->jenis_surat }}
            </span>
            <span class="text-xs font-semibold {{ $suratDinas->status === 'disetujui' ? 'text-emerald-600' : ($suratDinas->status === 'ditolak' ? 'text-rose-600' : 'text-slate-500') }}">
                {{ ucwords(str_replace('_', ' ', $suratDinas->status)) }}
            </span>
            <span class="text-xs text-slate-400 ml-auto">Dibuat {{ $suratDinas->created_at->format('d/m/Y H:i') }} oleh {{ $suratDinas->creator?->role_label ?? '-' }}</span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Surat</dt>
                <dd class="mt-1 font-mono font-semibold text-blue-700">{{ $suratDinas->no_surat ?: '(Belum bernomor)' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Ditetapkan</dt>
                <dd class="mt-1 text-slate-800">{{ $suratDinas->tgl_ditetapkan?->format('d F Y') ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tentang / Judul</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $suratDinas->tentang_judul }}</dd>
            </div>
            @if($suratDinas->catatan_revisi)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Catatan Persetujuan / Revisi</dt>
                    <dd class="mt-1 text-xs text-rose-800 bg-rose-50 border border-rose-200 p-3 rounded-xl">{{ $suratDinas->catatan_revisi }}</dd>
                </div>
            @endif
        </dl>
    </div>

    @if($suratDinas->jenis_surat === 'SK')
        <!-- Detail SK -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Rincian Surat Keputusan</h3>

            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tempat Ditetapkan</dt>
                    <dd class="mt-1 text-slate-800">{{ $detail['tempat_ditetapkan'] ?? '-' }}</dd>
                </div>

                @if($daftarBaris($detail['menimbang'] ?? ''))
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menimbang</dt>
                        <dd class="mt-1 text-slate-800 whitespace-pre-line">{{ $detail['menimbang'] }}</dd>
                    </div>
                @endif

                @if($daftarBaris($detail['mengingat'] ?? ''))
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mengingat</dt>
                        <dd class="mt-1 text-slate-800 whitespace-pre-line">{{ $detail['mengingat'] }}</dd>
                    </div>
                @endif

                @foreach($detail['dictum_list'] ?? [] as $i => $teks)
                    @if(trim((string) $teks) !== '')
                        <div>
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ \App\Models\SuratDinas::labelDiktum($i) }}</dt>
                            <dd class="mt-1 text-slate-800">{{ $teks }}</dd>
                        </div>
                    @endif
                @endforeach

                @if($daftarBaris($detail['tembusan'] ?? ''))
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tembusan</dt>
                        <dd class="mt-1 text-slate-800 whitespace-pre-line">{{ $detail['tembusan'] }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @else
        <!-- Detail SPT -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Rincian Surat Perintah Tugas</h3>

            @if($daftarBaris($detail['dasar'] ?? ''))
                <div class="mb-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Dasar</p>
                    <p class="text-sm text-slate-800 whitespace-pre-line">{{ $detail['dasar'] }}</p>
                </div>
            @endif

            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pegawai yang Ditugaskan</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-5">
                <div>
                    <dt class="text-xs font-semibold text-slate-400">Nama</dt>
                    <dd class="text-slate-800 font-medium">{{ $detail['pegawai_nama'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400">Pangkat / Golongan</dt>
                    <dd class="text-slate-800">{{ $detail['pegawai_pangkat_golongan'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400">NIP / NIPPPK</dt>
                    <dd class="text-slate-800">{{ $detail['pegawai_nip'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400">Jabatan</dt>
                    <dd class="text-slate-800">{{ $detail['pegawai_jabatan'] ?? '-' }}</dd>
                </div>
            </dl>

            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Untuk (Rincian Tugas)</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-5">
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-slate-400">Melaksanakan Tugas</dt>
                    <dd class="text-slate-800">{{ $detail['untuk_tugas'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400">Tempat</dt>
                    <dd class="text-slate-800">{{ $detail['untuk_tempat'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400">Waktu</dt>
                    <dd class="text-slate-800">
                        @if($detail['untuk_tanggal_mulai'] ?? null)
                            {{ \Illuminate\Support\Carbon::parse($detail['untuk_tanggal_mulai'])->format('d/m/Y') }}
                            @if($detail['untuk_tanggal_selesai'] ?? null)
                                s.d. {{ \Illuminate\Support\Carbon::parse($detail['untuk_tanggal_selesai'])->format('d/m/Y') }}
                            @endif
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>

            @if(count($detail['peserta_list'] ?? []) > 0)
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Daftar Siswa / Peserta</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border border-slate-200">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-left border-b border-slate-200 w-10">No</th>
                                <th scope="col" class="px-3 py-2 text-left border-b border-slate-200">Nama Lengkap</th>
                                <th scope="col" class="px-3 py-2 text-left border-b border-slate-200 w-40">Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($detail['peserta_list'] as $i => $peserta)
                                <tr>
                                    <td class="px-3 py-2">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2">{{ $peserta['nama'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $peserta['kelas'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    @if((auth()->user()->isAdmin() || auth()->user()->isKepalaSekolah()) && $suratDinas->status === 'menunggu_persetujuan')
        <x-ui.approval :letter="$suratDinas" kind="dinas" />
    @endif

</div>
@endsection
