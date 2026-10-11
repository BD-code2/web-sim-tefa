@php
    $detailSk = $suratKeluar->detail_json ?? [];
    $suratDinas = (object) [
        'no_surat' => $suratKeluar->no_surat,
        'tentang_judul' => $suratKeluar->perihal,
        'tgl_ditetapkan' => $detailSk['tanggal_surat'] ?? null,
        'detail_json' => $detailSk,
    ];
@endphp
@include('surat-dinas.pdf-sk')
