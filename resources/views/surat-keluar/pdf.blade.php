<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Undangan - {{ $suratKeluar->no_surat ?? $suratKeluar->perihal }}</title>
    <style>
        @page { margin: 28px 48px 42px; }
        body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.35; color: #111; }
        .kop { width: 100%; border-collapse: collapse; border-bottom: 4px double #000; margin-bottom: 17px; }
        .kop td { text-align: center; vertical-align: middle; padding: 0 3px 8px; }
        .kop .logo { width: 76px; }
        .kop img { width: 63px; height: 63px; object-fit: contain; }
        .kop h2, .kop h1, .kop p { margin: 0; }
        .kop h2 { font-size: 12pt; font-weight: bold; }
        .kop h1 { font-size: 15pt; margin: 2px 0; }
        .kop p { font-size: 9pt; }
        .tanggal { text-align: right; margin: 0 0 12px; }
        .meta { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta .label { width: 65px; }
        .meta .colon { width: 12px; }
        .penerima { margin: 0 0 16px; }
        .isi { text-align: justify; }
        .agenda { border-collapse: collapse; margin: 8px 0 12px 24px; }
        .agenda td { vertical-align: top; padding: 1px 0; }
        .agenda .label { width: 105px; }
        .agenda .colon { width: 12px; }
        .ttd { width: 245px; margin: 28px 0 0 auto; text-align: center; }
        .ttd p { margin: 0; }
        .ttd .nama { margin-top: 56px; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
@php($d = $suratKeluar->detail_json ?? [])
@php($logoProvinsi = file_exists(public_path('preference/kepri.jpg')) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('preference/kepri.jpg'))) : null)
@php($logoSekolah = file_exists(public_path('preference/image.jpg')) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('preference/image.jpg'))) : null)
<table class="kop"><tr>
    <td class="logo">@if($logoProvinsi)<img src="{{ $logoProvinsi }}">@endif</td>
    <td>
        <h2>PEMERINTAH PROVINSI KEPULAUAN RIAU</h2>
        <h2>DINAS PENDIDIKAN</h2>
        <h1>SMK NEGERI 4 TANJUNGPINANG</h1>
        <p>Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125</p>
        <p>Telepon (0771) 4440844</p>
        <p>Laman smkn4-tanjungpinang.sch.id &nbsp; Pos-el smkn4tpi4@gmail.com</p>
    </td>
    <td class="logo">@if($logoSekolah)<img src="{{ $logoSekolah }}">@endif</td>
</tr></table>
<p class="tanggal">Tanjungpinang, {{ \Illuminate\Support\Carbon::parse($d['tanggal_surat'] ?? $suratKeluar->approved_at ?? now())->locale('id')->translatedFormat('d F Y') }}</p>
<table class="meta">
    <tr><td class="label">Nomor</td><td class="colon">:</td><td>{{ $suratKeluar->no_surat ?? '—' }}</td></tr>
    <tr><td class="label">Sifat</td><td class="colon">:</td><td>Biasa</td></tr>
    <tr><td class="label">Lampiran</td><td class="colon">:</td><td>—</td></tr>
    <tr><td class="label">Hal</td><td class="colon">:</td><td>{{ $suratKeluar->perihal ?: 'Undangan' }}</td></tr>
</table>
<div class="penerima">Yth. Bapak/Ibu {{ $suratKeluar->tujuan ?: 'orang tua/wali murid' }}<br>
    Orang tua/wali murid kelas {{ $d['kelas_siswa'] ?? '-' }}<br>
    di<br>{{ $d['alamat_penerima'] ?? 'Tanjungpinang' }}
</div>
<div class="isi">
    <p>Dengan hormat,</p>
    <p>Sehubungan {{ $d['keperluan'] ?? 'akan dilaksanakannya kegiatan' }} bagi siswa kelas {{ $d['kelas_siswa'] ?? '-' }} Tahun Pelajaran {{ $d['tahun_pelajaran'] ?? '-' }}, dengan ini kami mengundang Bapak/Ibu untuk {{ $d['nama_kegiatan'] ?? $suratKeluar->acara ?? 'menghadiri kegiatan' }} ananda pada:</p>
    <table class="agenda">
        <tr><td class="label">Hari, tanggal</td><td class="colon">:</td><td>{{ $suratKeluar->tgl_pelaksanaan?->locale('id')->translatedFormat('l, d F Y') ?? '-' }}</td></tr>
        <tr><td class="label">Pukul</td><td class="colon">:</td><td>{{ $suratKeluar->waktu ?? '—' }}</td></tr>
        <tr><td class="label">Tempat</td><td class="colon">:</td><td>{{ $suratKeluar->tempat ?? '—' }}</td></tr>
    </table>
    <p>Atas perhatian dan kehadiran Bapak/Ibu diucapkan terima kasih.</p>
</div>
<div class="ttd">
    <p>Kepala Sekolah,</p>
    <p class="nama">{{ $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.' }}</p>
    <p>Pembina Tk.I</p>
    <p>NIP {{ $kepsek?->nip ?? '19770421 200502 2 011' }}</p>
</div>
</body>
</html>
