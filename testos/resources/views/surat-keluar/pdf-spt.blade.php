@php
    $detailSpt = $suratKeluar->detail_json ?? [];
    $suratDinas = (object) [
        'no_surat' => $suratKeluar->no_surat,
        'tentang_judul' => $suratKeluar->perihal,
        'tgl_ditetapkan' => $detailSpt['tanggal_surat'] ?? null,
        'detail_json' => $detailSpt,
    ];
@endphp
@include('surat-dinas.pdf-spt')
