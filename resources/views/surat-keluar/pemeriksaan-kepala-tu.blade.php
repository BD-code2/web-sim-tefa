@extends('layouts.sim')

@section('title', 'Pemeriksaan Surat')
@section('page-title', 'Pemeriksaan Surat')

@section('content')
<div class="sim-page space-y-5">
    <section class="page-panel">
        <div class="page-panel-heading">
            <div><h2 class="page-title">Antrean pemeriksaan Kepala TU</h2><p class="page-description">Periksa Surat Keluar, SK, dan SPT yang diajukan TU. Setelah diperiksa, TU dapat meneruskannya ke Kepala Sekolah.</p></div>
            <span class="page-badge page-badge-warning">{{ $suratKeluar->total() + $suratDinas->count() }} menunggu</span>
        </div>
        <div class="page-table-scroll">
            <table class="page-table-actions">
                <thead><tr><th>Surat</th><th>Pengajuan</th><th>Tujuan</th><th>Perihal</th><th class="text-right">Tindakan pemeriksaan</th></tr></thead>
                <tbody>
                    @forelse($suratKeluar as $surat)
                        <tr>
                            <td class="min-w-48"><a href="{{ route('surat-keluar.show', $surat) }}" class="font-semibold text-blue-700 hover:underline">{{ $surat->no_surat ?? 'Belum bernomor' }}</a><p class="mt-1 text-xs text-slate-500">{{ ['undangan' => 'Undangan', 'mutasi' => 'Rekomendasi Mutasi'][$surat->jenis_surat] ?? 'Surat Keluar' }}</p></td>
                            <td>{{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}<p class="mt-1 text-xs text-slate-500">{{ $surat->creator?->name ?? 'TU' }}</p></td>
                            <td>{{ $surat->tujuan }}</td>
                            <td>{{ $surat->perihal }}</td>
                            <td class="min-w-72">
                                <div x-data="{ revisiOpen: false }">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <x-ui.action icon="eye" label="Lihat rincian" tone="info" href="{{ route('surat-keluar.show', $surat) }}" />
                                    <form action="{{ route('surat-keluar.periksa-kepala-tu', $surat) }}" method="POST" onsubmit="return confirm('Selesaikan pemeriksaan dan kembalikan surat ke TU?')">
                                        @csrf
                                        <input type="hidden" name="keputusan" value="periksa">
                                        <x-ui.action icon="check" label="Selesai diperiksa" tone="success" type="submit" />
                                    </form>
                                    <button type="button" class="page-button page-button-small border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300 hover:bg-amber-100 ui-action" @click="revisiOpen = !revisiOpen; if (revisiOpen) $nextTick(() => $refs.catatan.focus())" :aria-expanded="revisiOpen" aria-controls="catatan-keluar-{{ $surat->id }}">
                                        <x-ui.icon name="revision" /> Kembalikan
                                    </button>
                                </div>
                                <form x-cloak x-show="revisiOpen" id="catatan-keluar-{{ $surat->id }}" action="{{ route('surat-keluar.periksa-kepala-tu', $surat) }}" method="POST" onsubmit="return confirm('Kembalikan surat kepada TU dengan catatan revisi ini?')" class="mt-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-left">
                                        @csrf
                                        <input type="hidden" name="keputusan" value="revisi">
                                        <label for="catatan-keluar-input-{{ $surat->id }}" class="block text-xs font-semibold text-amber-900">Keterangan revisi</label>
                                        <p class="mt-1 text-[11px] leading-5 text-amber-800">Jelaskan bagian yang perlu diperbaiki. Catatan ini akan terlihat oleh TU.</p>
                                        <textarea x-ref="catatan" id="catatan-keluar-input-{{ $surat->id }}" name="catatan_revisi" rows="3" required maxlength="1000" class="mt-2 block w-full rounded-lg border-amber-300 text-xs focus:border-amber-500 focus:ring-amber-200" placeholder="Tuliskan keterangan revisi untuk TU"></textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="page-button page-button-small" @click="revisiOpen = false">Batal</button>
                                            <button type="submit" class="page-button page-button-small border-amber-700 bg-amber-600 text-white hover:border-amber-800 hover:bg-amber-700 ui-action"><x-ui.icon name="revision" /> Kirim pengembalian</button>
                                        </div>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    @foreach($suratDinas as $surat)
                        <tr>
                            <td class="min-w-48"><a href="{{ route('surat-dinas.pemeriksaan.show', $surat) }}" class="font-semibold text-blue-700 hover:underline">{{ $surat->jenis_surat }} · {{ $surat->tentang_judul }}</a><p class="mt-1 text-xs text-slate-500">Surat Dinas</p></td>
                            <td>{{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}<p class="mt-1 text-xs text-slate-500">{{ $surat->creator?->name ?? 'TU' }}</p></td>
                            <td>{{ $surat->jenis_surat === 'SK' ? 'Surat Keputusan' : 'Surat Perintah Tugas' }}</td>
                            <td>{{ $surat->tentang_judul }}</td>
                            <td class="min-w-72">
                                <div x-data="{ revisiOpen: false }">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <x-ui.action icon="eye" label="Lihat rincian" tone="info" href="{{ route('surat-dinas.pemeriksaan.show', $surat) }}" />
                                        <form action="{{ route('surat-dinas.periksa-kepala-tu', $surat) }}" method="POST" onsubmit="return confirm('Selesaikan pemeriksaan dan kembalikan surat ke TU?')">
                                            @csrf
                                            <input type="hidden" name="keputusan" value="periksa">
                                            <x-ui.action icon="check" label="Selesai diperiksa" tone="success" type="submit" />
                                        </form>
                                        <button type="button" class="page-button page-button-small border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300 hover:bg-amber-100 ui-action" @click="revisiOpen = !revisiOpen; if (revisiOpen) $nextTick(() => $refs.catatan.focus())" :aria-expanded="revisiOpen" aria-controls="catatan-dinas-{{ $surat->id }}">
                                            <x-ui.icon name="revision" /> Kembalikan
                                        </button>
                                    </div>
                                    <form x-cloak x-show="revisiOpen" id="catatan-dinas-{{ $surat->id }}" action="{{ route('surat-dinas.periksa-kepala-tu', $surat) }}" method="POST" onsubmit="return confirm('Kembalikan Surat Dinas kepada TU dengan catatan revisi ini?')" class="mt-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3">
                                        @csrf
                                        <input type="hidden" name="keputusan" value="revisi">
                                        <label for="catatan-dinas-input-{{ $surat->id }}" class="block text-xs font-semibold text-amber-900">Keterangan revisi</label>
                                        <p class="mt-1 text-[11px] leading-5 text-amber-800">Jelaskan bagian yang perlu diperbaiki. Catatan ini akan terlihat oleh TU.</p>
                                        <textarea x-ref="catatan" id="catatan-dinas-input-{{ $surat->id }}" name="catatan_revisi" rows="3" required maxlength="1000" class="mt-2 block w-full rounded-lg border-amber-300 text-xs focus:border-amber-500 focus:ring-amber-200" placeholder="Tuliskan keterangan revisi untuk TU"></textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="page-button page-button-small" @click="revisiOpen = false">Batal</button>
                                            <button type="submit" class="page-button page-button-small border-amber-700 bg-amber-600 text-white hover:border-amber-800 hover:bg-amber-700 ui-action"><x-ui.icon name="revision" /> Kirim pengembalian</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($suratKeluar->isEmpty() && $suratDinas->isEmpty())
                        <tr><td colspan="5" class="page-empty">Tidak ada surat yang menunggu pemeriksaan.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($suratKeluar->hasPages())<div class="border-t border-slate-200 p-5">{{ $suratKeluar->links() }}</div>@endif
    </section>
</div>
@endsection
