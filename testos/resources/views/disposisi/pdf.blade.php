<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi - {{ $disposisi->suratMasuk?->no_agenda ?? '-' }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 14mm; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.15; color: #000; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .lembar > tbody > tr > td { border: 1px solid #000; padding: 4px 8px; vertical-align: top; }
        .kop td { border: 0; padding: 0; vertical-align: middle; text-align: center; }
        .kop .logo { width: 100px; }
        .kop img { width: 84px; height: 93px; }
        .instansi { font-size: 12pt; }
        .sekolah { font-size: 14pt; font-weight: bold; }
        .alamat { font-size: 8pt; line-height: 1.3; }
        .judul { text-align: center; font-size: 12pt; font-weight: bold; }
        .rincian td { border: 0; padding: 1px 0; vertical-align: top; }
        .rincian .label { width: 92px; }
        .rincian .titik { width: 12px; }
        .kotak { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; text-align: center; font-size: 9px; line-height: 10px; margin-right: 5px; }
        .pilihan { margin: 2px 0; }
        .sifat { font-size: 9pt; white-space: nowrap; margin-right: 9px; }
        .isi { white-space: pre-line; overflow-wrap: break-word; }
        .garis { border-bottom: 1px dotted #888; height: 17px; }
        .catatan td { border: 0; vertical-align: top; padding: 3px 0; }
        .tanda-tangan { width: 38%; padding-left: 25px !important; }
        .nama { padding-top: 28px; font-weight: bold; }
    </style>
</head>
<body>
@php
    $surat = $disposisi->suratMasuk;
    $tujuan = $disposisi->tujuan_disposisi ?? [];
    $harapan = $disposisi->harapan_instruksi ?? [];
    $sifat = $disposisi->sifat_surat ?: [$surat?->sifat ?? 'biasa'];
    $tujuanList = ['Waka Kurikulum', 'Waka Kesiswaan', 'Waka Sarpras', 'Waka Humas', 'Pembina OSIS', 'Koordinator TU', 'Bendahara Sekolah/BOS', 'Bendahara SPP'];
@endphp
<table class="lembar">
    <tbody>
        <tr><td colspan="2">
            <table class="kop"><tr>
                <td class="logo"><img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo-kepri.png'))) }}" alt="Logo Kepulauan Riau"></td>
                <td>
                    <div class="instansi">PEMERINTAH PROVINSI KEPULAUAN RIAU</div>
                    <div class="instansi">DINAS PENDIDIKAN</div>
                    <div class="sekolah">SMKN 4 TANJUNGPINANG</div>
                    <div class="alamat">Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125<br>
                    Telepon (0771) 4440844 &nbsp; NPSN 11002495 &nbsp; NSS 401316102010<br>
                    Laman: smkn4-tanjungpinang.sch.id &nbsp; Pos-el: smknpt4@gmail.com</div>
                </td>
                <td class="logo"><img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo-smkn4.png'))) }}" alt="Logo SMKN 4"></td>
            </tr></table>
        </td></tr>
        <tr><td colspan="2" class="judul">LEMBAR DISPOSISI</td></tr>
        <tr>
            <td>
                <table class="rincian">
                    <tr><td class="label">Surat dari</td><td class="titik">:</td><td>{{ $surat?->pengirim ?? '-' }}</td></tr>
                    <tr><td class="label">No. Surat</td><td class="titik">:</td><td>{{ $surat?->no_surat ?? '-' }}</td></tr>
                    <tr><td class="label">Tgl. Surat</td><td class="titik">:</td><td>{{ $surat?->tanggal_surat?->format('d/m/Y') ?? '-' }}</td></tr>
                </table>
            </td>
            <td>
                <table class="rincian">
                    <tr><td class="label">Diterima Tgl.</td><td class="titik">:</td><td>{{ $surat?->tanggal_diterima?->format('d/m/Y') ?? '-' }}</td></tr>
                    <tr><td class="label">No. Agenda</td><td class="titik">:</td><td>{{ $surat?->no_agenda ?? '-' }}</td></tr>
                </table>
                <div>Sifat:</div>
                @foreach(['sangat_segera' => 'Sangat segera', 'segera' => 'Segera', 'rahasia' => 'Rahasia', 'biasa' => 'Biasa'] as $value => $label)
                    <span class="sifat"><span class="kotak">{{ in_array($value, $sifat) ? 'X' : '' }}</span>{{ $label }}</span>
                @endforeach
            </td>
        </tr>
        <tr><td colspan="2"><table class="rincian"><tr><td class="label">Hal</td><td class="titik">:</td><td>{{ $surat?->perihal ?? '-' }}</td></tr></table></td></tr>
        <tr>
            <td>
                <div>Diteruskan kepada Sdr.</div>
                @foreach($tujuanList as $item)
                    <div class="pilihan"><span class="kotak">{{ in_array($item, $tujuan) ? 'X' : '' }}</span>{{ $item }}</div>
                @endforeach
                <div class="pilihan"><span class="kotak">{{ $disposisi->penerima_lain ? 'X' : '' }}</span>Sdr. {{ $disposisi->penerima_lain ?: '........................................' }}</div>
            </td>
            <td>
                <div>Dengan hormat harap:</div>
                @foreach(['Tanggapan dan saran', 'Proses lebih lanjut', 'Koordinasi/konfirmasi'] as $item)
                    <div class="pilihan"><span class="kotak">{{ in_array($item, $harapan) ? 'X' : '' }}</span>{{ $item }}</div>
                @endforeach
                <div class="isi">{{ $disposisi->instruksi_tambahan ?: ($harapan === [] ? $disposisi->instruksi : '') }}</div>
                <div class="garis"></div><div class="garis"></div><div class="garis"></div>
            </td>
        </tr>
        <tr><td colspan="2">
            <table class="catatan"><tr>
                <td><div>Catatan:</div><div class="isi">{{ $disposisi->catatan }}</div></td>
                <td class="tanda-tangan">
                    <div>Kepala Sekolah,</div>
                    <div class="nama">{{ $kepsekData['nama'] ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.' }}</div>
                    <div>{{ $kepsekData['pangkat'] ?? 'Pembina Tk.I' }}</div>
                    <div>NIP {{ $kepsekData['nip'] ?? '19770421 200502 2 011' }}</div>
                </td>
            </tr></table>
        </td></tr>
    </tbody>
</table>
</body>
</html>
