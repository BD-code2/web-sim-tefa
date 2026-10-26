<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Surat Rekomendasi Mutasi</title>
<style>
@page{margin:26px 48px 40px}body{font:11pt/1.3 "Times New Roman",serif;color:#111}.kop{width:100%;border-collapse:collapse;border-bottom:4px double #000;margin-bottom:14px}.kop td{text-align:center;vertical-align:middle;padding:0 3px 7px}.kop .logo{width:112px}.kop img{width:auto;height:110px;max-width:110px}.kop h2,.kop h1,.kop p{margin:0}.kop h2{font-size:11.5pt}.kop h1{font-size:14pt;margin:2px 0}.kop p{font-size:8.5pt}.judul{text-align:center;font-size:12pt;text-decoration:underline;font-weight:bold;margin:17px 0 3px}.nomor{text-align:left;margin:0 0 12px}.isi{text-align:justify}.data{border-collapse:collapse;margin:3px 0 8px}.data td{vertical-align:top;padding:1px 0}.data .label{width:142px}.data .colon{width:11px}.daftar{margin:3px 0 7px 25px;padding-left:13px}.ttd{width:245px;margin:18px 0 0 auto;text-align:left}.ttd p{margin:0}.ttd .nama{margin-top:48px;font-weight:bold;text-decoration:underline}
</style></head><body>
@php($d = $suratKeluar->detail_json ?? [])
@php($logoProvinsi = file_exists(public_path('preference/kepri.jpg')) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('preference/kepri.jpg'))) : null)
@php($logoSekolah = file_exists(public_path('preference/image.jpg')) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('preference/image.jpg'))) : null)
<table class="kop"><tr>
<td class="logo">@if($logoProvinsi)<img src="{{ $logoProvinsi }}">@endif</td>
<td><h2>PEMERINTAH PROVINSI KEPULAUAN RIAU</h2><h2>DINAS PENDIDIKAN</h2><h1>SMK NEGERI 4 TANJUNGPINANG</h1><p>Jalan Nusantara Km. 14, Batu IX, Tanjungpinang, Kepulauan Riau 29125</p><p>Telepon (0771) 4440844</p><p>Laman smkn4-tanjungpinang.sch.id &nbsp; Pos-el smkn4tpi4@gmail.com</p></td>
<td class="logo">@if($logoSekolah)<img src="{{ $logoSekolah }}">@endif</td>
</tr></table>
<p class="judul">SURAT REKOMENDASI MUTASI/PINDAH SISWA</p>
<p class="nomor">Nomor: {{ $suratKeluar->no_surat ?? '-' }}</p>
<div class="isi">
<p>Yang bertanda tangan di bawah ini:</p>
<table class="data">
<tr><td class="label">Nama</td><td class="colon">:</td><td>{{ $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.' }}</td></tr>
<tr><td class="label">NIP</td><td class="colon">:</td><td>{{ $kepsek?->nip ?? '19770421 200502 2 011' }}</td></tr>
<tr><td class="label">Pangkat/gol.</td><td class="colon">:</td><td>Pembina Utama Muda/IV/c</td></tr>
<tr><td class="label">Jabatan</td><td class="colon">:</td><td>Kepala SMK Negeri 4 Tanjungpinang</td></tr>
</table>
<p>Berdasarkan pengajuan permohonan mutasi/pindah sekolah dari orang tua/wali murid atas nama:</p>
<table class="data">
<tr><td class="label">Nama siswa</td><td class="colon">:</td><td>{{ $d['nama_siswa'] ?? '-' }}</td></tr>
<tr><td class="label">Sekolah asal</td><td class="colon">:</td><td>{{ $d['sekolah_asal'] ?? '-' }}</td></tr>
<tr><td class="label">NISN</td><td class="colon">:</td><td>{{ $d['nisn'] ?? '-' }}</td></tr>
<tr><td class="label">Kelas/Semester</td><td class="colon">:</td><td>{{ $d['kelas_siswa'] ?? '-' }}/{{ $d['semester'] ?? '-' }}/{{ $d['tahun_pelajaran'] ?? '-' }}</td></tr>
<tr><td class="label">Alamat sekolah asal</td><td class="colon">:</td><td>{{ $d['alamat_sekolah_asal'] ?? '-' }}</td></tr>
<tr><td class="label">Tempat, tgl. lahir</td><td class="colon">:</td><td>{{ $d['tempat_lahir'] ?? '-' }}, {{ ($d['tanggal_lahir'] ?? null) ? \Illuminate\Support\Carbon::parse($d['tanggal_lahir'])->locale('id')->translatedFormat('d F Y') : '-' }}</td></tr>
@if(($d['nama_ayah'] ?? null) || ($d['nama_ibu'] ?? null))
<tr><td class="label">Nama ayah</td><td class="colon">:</td><td>{{ $d['nama_ayah'] ?? '-' }}</td></tr>
<tr><td class="label">Pekerjaan ayah</td><td class="colon">:</td><td>{{ $d['pekerjaan_ayah'] ?? '-' }}</td></tr>
<tr><td class="label">Nama ibu</td><td class="colon">:</td><td>{{ $d['nama_ibu'] ?? '-' }}</td></tr>
<tr><td class="label">Pekerjaan ibu</td><td class="colon">:</td><td>{{ $d['pekerjaan_ibu'] ?? '-' }}</td></tr>
@else
<tr><td class="label">Nama orang tua/wali</td><td class="colon">:</td><td>{{ $d['nama_orang_tua'] ?? '-' }}</td></tr>
@endif
<tr><td class="label">Alamat orang tua/wali</td><td class="colon">:</td><td>{{ $d['alamat_orang_tua'] ?? '-' }}</td></tr>
</table>
<p>Setelah melihat dan memverifikasi dokumen akademik serta alasan tujuan mutasi/pindah sekolah, dengan ini kami merekomendasikan siswa yang bersangkutan mutasi/pindah ke {{ $d['sekolah_tujuan'] ?? '-' }}{{ ($d['alasan_mutasi'] ?? null) ? ' dengan alasan: '.$d['alasan_mutasi'] : '' }}, dengan melengkapi:</p>
@php($dokumen = preg_split('/\r\n|\r|\n/', (string) ($d['dokumen_lampiran'] ?? '')))
<ul class="daftar">@foreach($dokumen as $item)@if(trim($item) !== '')<li>{{ trim($item) }}</li>@endif @endforeach</ul>
<p>Dan hal lainnya sehubungan dengan kepindahan siswa yang bersangkutan kami serahkan ke dinas terkait.</p>
<p>Demikian surat rekomendasi ini kami sampaikan, atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
</div>
<div class="ttd"><p>Tanjungpinang, {{ \Illuminate\Support\Carbon::parse($d['tanggal_surat'] ?? $suratKeluar->approved_at ?? now())->locale('id')->translatedFormat('d F Y') }}</p><p>Kepala Sekolah,</p><p class="nama">{{ $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.' }}</p><p>Pembina Utama Muda</p><p>NIP {{ $kepsek?->nip ?? '19770421 200502 2 011' }}</p></div>
</body></html>
