@php
    $isDinas = $kind === 'dinas';
    $approveRoute = $isDinas ? 'surat-dinas.setujui' : 'disposisi.surat-keluar.setujui';
    $revisionRoute = $isDinas ? 'surat-dinas.revisi' : 'disposisi.surat-keluar.revisi';
    $detailRoute = $isDinas ? 'disposisi.surat-dinas.show' : 'surat-keluar.show';
    $revisionId = 'revision-'.$kind.'-'.$letter->id;
    $typeLabel = $isDinas ? ($letter->jenis_surat === 'SK' ? 'Surat Keputusan' : 'Surat Perintah Tugas') : ($letter->jenis_surat === 'mutasi' ? 'Mutasi siswa' : 'Surat undangan');
    $letterDate = $isDinas ? $letter->tgl_ditetapkan : (($letter->detail_json['tanggal_surat'] ?? null) ? \Illuminate\Support\Carbon::parse($letter->detail_json['tanggal_surat']) : $letter->created_at);
@endphp
<tbody x-data="{ revisionOpen: false }">
    <tr>
        <td class="min-w-[220px] max-w-80">
            <span class="text-xs text-slate-500">{{ $typeLabel }}</span>
            <p class="mt-1 font-semibold text-slate-900">{{ $isDinas ? $letter->tentang_judul : $letter->perihal }}</p>
            <p class="mt-1 break-words text-xs text-slate-500">{{ $letter->no_surat ?: 'Nomor otomatis saat disetujui' }}</p>
        </td>
        <td class="min-w-[150px]"><p class="font-medium text-slate-800">{{ $letter->creator?->role_label ?? 'TU' }}</p>@unless($isDinas)<p class="mt-1 text-xs text-slate-500">Kepada: {{ $letter->tujuan }}</p>@endunless</td>
        <td class="whitespace-nowrap">{{ $letterDate?->format('d/m/Y') ?? '-' }}</td>
        <td class="min-w-[168px]">
            <div class="page-actions">
                <x-ui.action icon="eye" label="Lihat rincian" tone="info" href="{{ route($detailRoute, $letter) }}" />
                <form action="{{ route($approveRoute, $letter) }}" method="POST" onsubmit="return confirm('Setujui surat ini dan terbitkan nomor surat?')">@csrf<x-ui.action icon="check" label="Setujui" tone="success" type="submit" /></form>
                <x-ui.action icon="revision" label="Minta revisi" tone="warning" type="button" @click="revisionOpen = !revisionOpen; if (revisionOpen) $nextTick(() => $refs.revision.focus())" x-bind:aria-expanded="revisionOpen" aria-controls="{{ $revisionId }}" />
            </div>
        </td>
    </tr>
    <tr id="{{ $revisionId }}" x-show="revisionOpen" x-cloak>
        <td colspan="4" class="bg-slate-50">
            <form action="{{ route($revisionRoute, $letter) }}" method="POST" class="space-y-3">@csrf
                <label for="{{ $revisionId }}-note" class="block">Catatan revisi untuk {{ $isDinas ? $letter->tentang_judul : $letter->perihal }} <span class="text-rose-600">*</span></label>
                <textarea id="{{ $revisionId }}-note" x-ref="revision" name="catatan_revisi" rows="3" required maxlength="1000" class="block w-full" placeholder="Tuliskan bagian yang perlu diperbaiki oleh TU."></textarea>
                <div class="page-actions"><x-ui.action icon="close" label="Batal" type="button" @click="revisionOpen = false" /><x-ui.action icon="send" label="Kirim catatan revisi" tone="info" type="submit" /></div>
            </form>
        </td>
    </tr>
</tbody>
