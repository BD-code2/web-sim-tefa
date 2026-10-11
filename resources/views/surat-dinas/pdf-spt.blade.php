<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Perintah Tugas - {{ $suratDinas->no_surat ?: $suratDinas->tentang_judul }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.45; margin: 28px; color: #000; }

        /* Kop Surat */
        .kop { width: 100%; border-bottom: 3px double #000; padding-bottom: 6px; margin-bottom: 16px; }
        .kop td { vertical-align: middle; }
        .kop-logo { width: 70px; text-align: center; }
        .kop-logo img { width: 56px; height: auto; }
        .kop-teks { text-align: center; }
        .kop-1, .kop-2 { font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop-3 { font-size: 15pt; font-weight: bold; text-transform: uppercase; margin: 1px 0; letter-spacing: 1px; }
        .kop-alamat { font-size: 9.5pt; margin: 0; }

        /* Tabel meta (Nomor, Perihal) */
        .meta { width: 100%; }
        .meta td { vertical-align: top; padding: 1px 0; }
        .meta .lbl { width: 85px; }
        .meta .colon { width: 10px; }
        .meta .tgl { text-align: right; width: 240px; vertical-align: top; }

        .isi { margin-top: 12px; text-align: justify; }
        .section-title { font-weight: bold; margin-top: 14px; margin-bottom: 4px; }

        /* Label : isi (DASAR, Kepada, Untuk) */
        .label-tabel { width: 100%; }
        .label-tabel td { vertical-align: top; padding: 1px 0; }
        .label-tabel .lbl { width: 145px; }
        .label-tabel .colon { width: 10px; }
        .poin { margin: 0 0 4px 0; text-align: justify; }
        .nilai { text-align: justify; }

        /* Daftar siswa / peserta */
        .peserta-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11pt; }
        .peserta-table th, .peserta-table td { border: 1px solid #000; padding: 4px 8px; }
        .peserta-table th { font-weight: bold; text-align: center; }
        .peserta-table .no { width: 35px; text-align: center; }
        .peserta-table .nama { text-align: left; }
        .peserta-table .kelas { width: 90px; text-align: center; }

        /* Penutup & TTD */
        .penutup { margin-top: 16px; text-align: justify; }
        .ttd-table { width: 100%; margin-top: 34px; }
        .ttd-table td { vertical-align: top; }
        .ttd { width: 50%; text-align: center; }
        .ttd .nama { font-weight: bold; text-decoration: underline; margin-top: 58px; }
        .ttd .nip { margin-top: 2px; }
    </style>
</head>
<body>
@php
    $detail = $suratDinas->detail_json ?? [];

    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $tglIndo = function ($tanggal) use ($bulanIndo) {
        if (! $tanggal) {
            return '.............................................';
        }
        $d = \Illuminate\Support\Carbon::parse($tanggal);
        return $d->format('d').' '.$bulanIndo[(int) $d->format('m')].' '.$d->format('Y');
    };

    $daftarBaris = function ($teks) {
        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $teks)),
            fn ($baris) => $baris !== ''
        ));
    };

    $logoKiri = @file_get_contents(public_path('images/logo-kepri.png'));
    $logoKanan = @file_get_contents(public_path('images/logo-smkn4.png'));

    $pesertaList = $detail['peserta_list'] ?? [];

    $namaPenandatangan = $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.';
    $nipPenandatangan = $kepsek?->nip ?? '19770421 200502 2 011';
@endphp

<!-- Kop Surat -->
<table class="kop">
    <tr>
        <td class="kop-logo">
            @if($logoKiri)
                <img src="data:image/png;base64,{{ base64_encode($logoKiri) }}" alt="">
            @endif
        </td>
        <td class="kop-teks">
            <p class="kop-1">Pemerintah Provinsi Kepulauan Riau</p>
            <p class="kop-2">Dinas Pendidikan</p>
            <p class="kop-3">SMK Negeri 4 Tanjungpinang</p>
            <p class="kop-alamat">Jl. Nusantara Km. 14 Tanjungpinang, Kepulauan Riau</p>
            <p class="kop-alamat">Telepon: (0771) 123456 | Website: smkn4tanjungpinang.sch.id | Email: info@smkn4tanjungpinang.sch.id</p>
        </td>
        <td class="kop-logo">
            @if($logoKanan)
                <img src="data:image/png;base64,{{ base64_encode($logoKanan) }}" alt="">
            @endif
        </td>
    </tr>
</table>

<!-- Identitas Surat -->
<table class="meta">
    <tr>
        <td class="lbl">Nomor</td>
        <td class="colon">:</td>
        <td><strong>{{ $suratDinas->no_surat ?: '.....................................' }}</strong></td>
        <td class="tgl">Tanjungpinang, {{ $tglIndo($suratDinas->tgl_ditetapkan) }}</td>
    </tr>
    <tr>
        <td class="lbl">Perihal</td>
        <td class="colon">:</td>
        <td colspan="2"><strong>{{ $suratDinas->tentang_judul }}</strong></td>
    </tr>
</table>

<!-- Pembuka -->
<div class="isi">
    <p>Bersama Surat Perintah Tugas ini, Kepala SMK Negeri 4 Tanjungpinang memberikan perintah berdasarkan hal-hal sebagai berikut :</p>
</div>

<!-- Dasar -->
@if($daftarBaris($detail['dasar'] ?? ''))
    <p class="section-title">DASAR :</p>
    @foreach($daftarBaris($detail['dasar']) as $baris)
        <p class="poin">{{ $baris }}</p>
    @endforeach
@endif

<!-- Memerintahkan -->
<p class="section-title">MEMERINTAHKAN :</p>

<p class="isi" style="margin-top: 2px;">Kepada :</p>
<table class="label-tabel">
    <tr>
        <td class="lbl">Nama</td>
        <td class="colon">:</td>
        <td class="nilai"><strong>{{ $detail['pegawai_nama'] ?? '-' }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Pangkat / Golongan</td>
        <td class="colon">:</td>
        <td class="nilai">{{ $detail['pegawai_pangkat_golongan'] ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">NIP / NIPPPK</td>
        <td class="colon">:</td>
        <td class="nilai">{{ $detail['pegawai_nip'] ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Jabatan</td>
        <td class="colon">:</td>
        <td class="nilai">{{ $detail['pegawai_jabatan'] ?? '-' }}</td>
    </tr>
</table>

<p class="section-title">Untuk :</p>
<table class="label-tabel">
    <tr>
        <td class="lbl">Melaksanakan Tugas</td>
        <td class="colon">:</td>
        <td class="nilai">{{ $detail['untuk_tugas'] ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Tempat</td>
        <td class="colon">:</td>
        <td class="nilai">{{ $detail['untuk_tempat'] ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Waktu</td>
        <td class="colon">:</td>
        <td class="nilai">
            @if($detail['untuk_tanggal_mulai'] ?? null)
                Mulai {{ $tglIndo($detail['untuk_tanggal_mulai']) }}
                @if($detail['untuk_tanggal_selesai'] ?? null)
                    s.d. {{ $tglIndo($detail['untuk_tanggal_selesai']) }}
                @endif
            @else
                -
            @endif
        </td>
    </tr>
</table>

<!-- Daftar Siswa / Peserta -->
@if(count($pesertaList) > 0)
    <p class="section-title">Daftar Siswa / Peserta yang Ikut Tugas :</p>
    <table class="peserta-table">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama Lengkap</th>
                <th class="kelas">Kelas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesertaList as $i => $peserta)
                @if(is_array($peserta) && trim((string) ($peserta['nama'] ?? '')) !== '')
                    <tr>
                        <td class="no">{{ $i + 1 }}</td>
                        <td class="nama">{{ $peserta['nama'] }}</td>
                        <td class="kelas">{{ $peserta['kelas'] ?? '-' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif

<!-- Penutup -->
<p class="penutup">Demikian Surat Perintah Tugas ini dibuat untuk digunakan sebagaimana mestinya.</p>

<!-- Penetapan & Tanda Tangan -->
<table class="ttd-table">
    <tr>
        <td style="width: 50%;">
            <p>Ditetapkan di {{ $detail['tempat_ditetapkan'] ?? 'Tanjungpinang' }}<br>
            pada tanggal {{ $tglIndo($suratDinas->tgl_ditetapkan) }}</p>
        </td>
        <td class="ttd">
            <p>Kepala SMK Negeri 4 Tanjungpinang,</p>
            <p class="nama">{{ $namaPenandatangan }}</p>
            <p class="nip">NIP. {{ $nipPenandatangan }}</p>
            <p style="font-size:9pt">Dokumen ini dibuat secara elektronik melalui SIM Sekolah.</p>
        </td>
    </tr>
</table>
</body>
</html>
