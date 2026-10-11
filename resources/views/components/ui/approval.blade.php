@props(['letter', 'kind' => 'keluar'])
@php
    $approveRoute = $kind === 'dinas' ? 'surat-dinas.setujui' : 'disposisi.surat-keluar.setujui';
    $revisionRoute = $kind === 'dinas' ? 'surat-dinas.revisi' : 'disposisi.surat-keluar.revisi';
    $revisionId = 'revision-'.$kind.'-'.$letter->id;
@endphp
<section class="page-panel" x-data="{ revisionOpen: {{ $errors->has('catatan_revisi') ? 'true' : 'false' }} }">
    <div class="page-panel-heading">
        <div>
            <h2 class="page-title">Persetujuan Kepala Sekolah</h2>
            <p class="page-description">Periksa rincian surat, lalu pilih keputusan di bawah ini.</p>
        </div>
        <x-ui.status value="menunggu_persetujuan" />
    </div>
    <div class="page-section space-y-5">
        <p class="text-sm text-slate-600">Persetujuan menerbitkan nomor surat jika belum terisi dan mengaktifkan PDF. Jika perlu perbaikan, sertakan catatan untuk TU.</p>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route($approveRoute, $letter) }}" method="POST" onsubmit="return confirm('Setujui surat ini dan terbitkan nomor surat?')">
                @csrf
                <button type="submit" class="page-button page-button-success ui-action"><x-ui.icon name="check" /> Setujui &amp; terbitkan nomor</button>
            </form>
            <button type="button" class="page-button page-button-danger ui-action" @click="revisionOpen = !revisionOpen; if (revisionOpen) $nextTick(() => $refs.revision.focus())" :aria-expanded="revisionOpen" aria-controls="{{ $revisionId }}"><x-ui.icon name="revision" /> Kembalikan untuk revisi</button>
        </div>
        <form id="{{ $revisionId }}" action="{{ route($revisionRoute, $letter) }}" method="POST" x-show="revisionOpen" x-cloak class="space-y-3 border-t border-slate-200 pt-5">
            @csrf
            <label for="{{ $revisionId }}-note" class="block">Catatan revisi <span class="text-rose-600">*</span></label>
            <textarea id="{{ $revisionId }}-note" x-ref="revision" name="catatan_revisi" rows="3" required maxlength="1000" class="block w-full" placeholder="Jelaskan bagian yang perlu diperbaiki oleh TU.">{{ old('catatan_revisi') }}</textarea>
            @error('catatan_revisi')<p role="alert" class="text-sm text-rose-700">{{ $message }}</p>@enderror
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" class="page-button ui-action" @click="revisionOpen = false"><x-ui.icon name="close" /> Batal revisi</button>
                <button type="submit" class="page-button page-button-danger ui-action"><x-ui.icon name="send" /> Kirim catatan ke TU</button>
            </div>
        </form>
    </div>
</section>
