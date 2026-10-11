<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keputusan - {{ $suratDinas->no_surat ?: $suratDinas->tentang_judul }}</title>
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

        /* Label : isi (Menimbang, Mengingat, TENTANG) */
        .label-tabel { width: 100%; }
        .label-tabel td { vertical-align: top; padding: 1px 0; }
        .label-tabel .lbl { width: 95px; }
        .label-tabel .colon { width: 10px; }
        .poin { margin: 0 0 4px 0; text-align: justify; }

        .isi { margin-top: 10px; text-align: justify; }
        .section-title { font-weight: bold; margin-top: 12px; }

        /* Dictum KESATU - KEEMPAT */
        .dictum { width: 100%; margin-top: 4px; }
        .dictum td { vertical-align: top; padding: 3px 0; }
        .dictum .lbl { width: 95px; }
        .dictum .colon { width: 10px; }
        .dictum .teks { text-align: justify; }

        /* Penetapan & TTD */
        .ttd-table { width: 100%; margin-top: 34px; }
        .ttd-table td { vertical-align: top; }
        .ttd { width: 50%; text-align: center; }
        .ttd .nama { font-weight: bold; text-decoration: underline; margin-top: 58px; }
        .ttd .nip { margin-top: 2px; }

        .tembusan { margin-top: 18px; }
        .tembusan .poin { margin-left: 0; }
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

<!-- Isi Surat Keputusan -->
<div class="isi">
    <p style="margin-top: 12px;">Kepala SMK Negeri 4 Tanjungpinang, dalam hal ini bertindak untuk dan atas nama sendiri, berdasarkan hal-hal sebagai berikut :</p>
</div>

<table class="label-tabel">
    @if($daftarBaris($detail['menimbang'] ?? ''))
        <tr>
            <td class="lbl">Menimbang</td>
            <td class="colon">:</td>
            <td>
                @foreach($daftarBaris($detail['menimbang']) as $poin)
                    <p class="poin">{{ $poin }}</p>
                @endforeach
            </td>
        </tr>
    @endif
    @if($daftarBaris($detail['mengingat'] ?? ''))
        <tr>
            <td class="lbl">Mengingat</td>
            <td class="colon">:</td>
            <td>
                @foreach($daftarBaris($detail['mengingat']) as $poin)
                    <p class="poin">{{ $poin }}</p>
                @endforeach
            </td>
        </tr>
    @endif
</table>

<div class="isi">
    <p>Berdasarkan pertimbangan dan hal-hal sebagaimana tersebut di atas, dengan ini <strong>MEMUTUSKAN</strong> :</p>
</div>

<table class="label-tabel">
    <tr>
        <td class="lbl">TENTANG</td>
        <td class="colon">:</td>
        <td><strong>{{ $suratDinas->tentang_judul }}</strong><br>
            @if($detail['pegawai_nama'] ?? null)
                Penerima tugas: {{ $detail['pegawai_nama'] }}@if($detail['pegawai_jabatan'] ?? null), {{ $detail['pegawai_jabatan'] }}@endif
            @endif
            @if($detail['tanggal_mulai_berlaku'] ?? null)<br>Berlaku mulai: {{ $tglIndo($detail['tanggal_mulai_berlaku']) }}@endif
        </td>
    </tr>
</table>

<p class="section-title">Menetapkan :</p>
<table class="dictum">
    @forelse($detail['dictum_list'] ?? [] as $i => $teks)
        @if(trim((string) $teks) !== '')
            <tr>
                <td class="lbl">{{ \App\Models\SuratDinas::labelDiktum($i) }}</td>
                <td class="colon">:</td>
                <td class="teks">{{ $teks }}</td>
            </tr>
        @endif
    @empty
        <tr>
            <td class="lbl">KESATU</td>
            <td class="colon">:</td>
            <td class="teks">.................................................................</td>
        </tr>
    @endforelse
</table>

<!-- Penetapan & Tanda Tangan -->
<table class="ttd-table">
    <tr>
        <td style="width: 50%;">
            <p>Ditetapkan di {{ $detail['tempat_ditetapkan'] ?? 'Tanjungpinang' }}<br>
            pada tanggal {{ $tglIndo($suratDinas->tgl_ditetapkan) }}
            @if($detail['tahun_pelajaran'] ?? null)<br>Tahun Pelajaran {{ $detail['tahun_pelajaran'] }}@endif</p>
        </td>
        <td class="ttd">
            <p>Kepala SMK Negeri 4 Tanjungpinang,</p>
            <p class="nama">{{ $namaPenandatangan }}</p>
            <p class="nip">NIP. {{ $nipPenandatangan }}</p>
        </td>
    </tr>
</table>

<!-- Tembusan -->
@if($daftarBaris($detail['tembusan'] ?? ''))
    <div class="tembusan">
        <p><strong>Tembusan :</strong></p>
        @foreach($daftarBaris($detail['tembusan']) as $baris)
            <p class="poin">{{ $baris }}</p>
        @endforeach
    </div>
@endif
</body>
</html>
