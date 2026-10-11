@props(['value', 'label' => null])
@php
    [$tone, $defaultLabel] = match ($value) {
        'setuju', 'disetujui' => ['success', 'Disetujui'],
        'selesai', 'Selesai' => ['success', 'Selesai'],
        'ditolak', 'tidak_setuju' => ['danger', 'Ditolak'],
        'minta_revisi' => ['danger', 'Perlu revisi'],
        'diproses' => ['info', 'Diproses'],
        'didisposisi' => ['info', 'Didisposisikan'],
        'dikirim' => ['info', 'Sudah dikirim'],
        'menunggu_persetujuan' => ['warning', 'Menunggu persetujuan'],
        'pending', 'Pending' => ['warning', 'Belum ditanggapi'],
        'menunggu' => ['warning', 'Menunggu'],
        'diterima' => ['info', 'Diterima'],
        'diarsipkan' => ['neutral', 'Diarsipkan'],
        'draft' => ['neutral', 'Draf'],
        default => ['neutral', ucfirst(str_replace('_', ' ', (string) $value))],
    };
@endphp
<span {{ $attributes->class(['page-badge', 'page-badge-'.$tone]) }}>{{ $label ?? $defaultLabel }}</span>
