<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Surat</title>
    <style>
        @page { margin: 20px 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 9px; }
        .header { text-align: center; border-bottom: 3px double #111; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 3px; }
        .header p { margin: 2px 0; }
        .periode { margin: 8px 0 14px; text-align: center; font-size: 10px; }
        .ringkasan { width: 100%; margin-bottom: 14px; border-spacing: 5px; }
        .ringkasan td { border: 1px solid #cbd5e1; padding: 7px; text-align: center; }
        .ringkasan strong { font-size: 15px; display: block; }
        h2 { font-size: 12px; margin: 14px 0 5px; padding: 6px 8px; background: #e2e8f0; }
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data th, .data td { border: 1px solid #94a3b8; padding: 5px; vertical-align: top; word-wrap: break-word; }
        .data th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; }
        .no { width: 4%; text-align: center; }
        .tanggal { width: 10%; }
        .status { width: 12%; }
        .kosong { text-align: center; color: #64748b; padding: 12px !important; }
        .page-break { page-break-before: always; }
        .footer { margin-top: 14px; text-align: right; font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
@php
    $jenisLabel = ['surat_masuk' => 'Surat Masuk', 'surat_keluar' => 'Surat Keluar', 'surat_dinas' => 'Surat Dinas', 'disposisi' => 'Disposisi'];
    $periode = ($filter['tanggal_mulai'] || $filter['tanggal_selesai'])
        ? (($filter['tanggal_mulai'] ? \Carbon\Carbon::parse($filter['tanggal_mulai'])->format('d/m/Y') : 'Awal').' s.d. '.($filter['tanggal_selesai'] ? \Carbon\Carbon::parse($filter['tanggal_selesai'])->format('d/m/Y') : 'Sekarang'))
        : 'Semua Periode';
@endphp

<div class="header">
    <h1>SMK NEGERI 4 TANJUNGPINANG</h1>
    <p><strong>LAPORAN ADMINISTRASI SURAT</strong></p>
    <p>Surat Masuk, Surat Keluar, Surat Dinas, dan Disposisi</p>
</div>
<div class="periode">Periode: <strong>{{ $periode }}</strong></div>

<table class="ringkasan"><tr>
    @foreach($filter['jenis'] as $jenis)
        <td><strong>{{ $laporan[$jenis]->count() }}</strong>{{ $jenisLabel[$jenis] }}</td>
    @endforeach
</tr></table>

@foreach($filter['jenis'] as $jenis)
    <div class="{{ !$loop->first ? 'page-break' : '' }}"></div>
    <h2>{{ strtoupper($jenisLabel[$jenis]) }} ({{ $laporan[$jenis]->count() }} DATA)</h2>
    <table class="data">
        <thead>
            @if($jenis === 'surat_masuk')
                <tr><th class="no">No</th><th class="tanggal">Tanggal Diterima</th><th>No. Surat / Agenda</th><th>Pengirim</th><th>Perihal</th><th class="status">Status</th></tr>
            @elseif($jenis === 'surat_keluar')
                <tr><th class="no">No</th><th class="tanggal">Tanggal</th><th>No. Surat</th><th>Tujuan</th><th>Perihal</th><th class="status">Status</th></tr>
            @elseif($jenis === 'surat_dinas')
                <tr><th class="no">No</th><th class="tanggal">Tanggal</th><th>Jenis</th><th>No. Surat</th><th>Tentang</th><th class="status">Status</th></tr>
            @else
                <tr><th class="no">No</th><th class="tanggal">Tanggal</th><th>Surat / Perihal</th><th>Dari</th><th>Penerima</th><th class="status">Status</th></tr>
            @endif
        </thead>
        <tbody>
            @forelse($laporan[$jenis] as $item)
                @if($jenis === 'surat_masuk')
                    <tr><td class="no">{{ $loop->iteration }}</td><td>{{ $item->tanggal_diterima?->format('d/m/Y') ?? '-' }}</td><td>{{ $item->no_surat }}<br><small>{{ $item->no_agenda }}</small></td><td>{{ $item->pengirim }}</td><td>{{ $item->perihal }}</td><td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td></tr>
                @elseif($jenis === 'surat_keluar')
                    <tr><td class="no">{{ $loop->iteration }}</td><td>{{ $item->tgl_pelaksanaan?->format('d/m/Y') ?? '-' }}</td><td>{{ $item->no_surat ?? '-' }}</td><td>{{ $item->tujuan }}</td><td>{{ $item->perihal }}</td><td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td></tr>
                @elseif($jenis === 'surat_dinas')
                    <tr><td class="no">{{ $loop->iteration }}</td><td>{{ $item->tgl_ditetapkan?->format('d/m/Y') ?? '-' }}</td><td>{{ $item->jenis_surat }}</td><td>{{ $item->no_surat ?? '-' }}</td><td>{{ $item->tentang_judul }}</td><td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td></tr>
                @else
                    <tr><td class="no">{{ $loop->iteration }}</td><td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td><td>{{ $item->suratMasuk?->no_surat ?? '-' }}<br><small>{{ $item->suratMasuk?->perihal ?? '-' }}</small></td><td>{{ $item->dariUser?->name ?? '-' }}</td><td>{{ $item->kepadaUser?->name ?? '-' }}</td><td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td></tr>
                @endif
            @empty
                <tr><td colspan="6" class="kosong">Tidak ada data pada periode yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>
@endforeach

<div class="footer">Dicetak pada {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</div>
</body>
</html>
