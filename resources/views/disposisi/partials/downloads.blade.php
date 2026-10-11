<div class="flex flex-wrap gap-2 {{ ($align ?? 'right') === 'right' ? 'justify-end' : '' }}">
    @if($disposisi->suratMasuk?->lampiran_path)
        <x-ui.action icon="download" label="Unduh surat masuk" tone="info" href="{{ route('disposisi.unduh-surat', $disposisi) }}" />
    @endif
    <x-ui.action icon="clipboard" label="Unduh lembar perintah disposisi (PDF)" tone="info" href="{{ route('disposisi.unduh-lembar', $disposisi) }}" />
    @if($showDetail ?? true)
        <x-ui.action icon="eye" label="Buka detail & proses" tone="info" href="{{ route('disposisi.show', $disposisi) }}" />
    @endif
</div>
@unless($disposisi->suratMasuk?->lampiran_path)
    <p class="mt-2 text-xs text-slate-500 {{ ($align ?? 'right') === 'right' ? 'text-right' : '' }}">Surat masuk belum diunggah oleh TU.</p>
@endunless
