@php
    $details = $suratKeluar->detail_json ?? [];
    $labels = $suratKeluar->jenis_surat === 'mutasi' ? [
        'tanggal_surat' => 'Tanggal surat', 'tahun_pelajaran' => 'Tahun pelajaran',
        'nama_siswa' => 'Nama siswa', 'nisn' => 'NIP / NISN', 'kelas_siswa' => 'Kelas',
        'semester' => 'Semester', 'tempat_lahir' => 'Tempat lahir', 'tanggal_lahir' => 'Tanggal lahir',
        'nama_orang_tua' => 'Nama orang tua / wali', 'alamat_orang_tua' => 'Alamat orang tua / wali',
        'nama_ayah' => 'Nama ayah', 'pekerjaan_ayah' => 'Pekerjaan ayah',
        'nama_ibu' => 'Nama ibu', 'pekerjaan_ibu' => 'Pekerjaan ibu',
        'sekolah_asal' => 'Sekolah asal', 'alamat_sekolah_asal' => 'Alamat sekolah asal',
        'sekolah_tujuan' => 'Sekolah tujuan', 'alasan_mutasi' => 'Alasan mutasi',
        'dokumen_lampiran' => 'Dokumen yang dilampirkan',
    ] : [
        'tanggal_surat' => 'Tanggal surat', 'tahun_pelajaran' => 'Tahun pelajaran',
        'nama_siswa' => 'Nama siswa', 'kelas_siswa' => 'Kelas',
        'alamat_penerima' => 'Alamat penerima', 'nama_kegiatan' => 'Nama kegiatan',
        'keperluan' => 'Keperluan',
    ];
@endphp
@if($details)
    <section class="page-panel">
        <div class="page-panel-heading"><div><h2 class="page-title">Rincian isian {{ $suratKeluar->jenis_surat === 'mutasi' ? 'mutasi siswa' : 'undangan' }}</h2><p class="page-description">Data yang digunakan dalam dokumen surat.</p></div></div>
        <dl class="page-facts page-section sm:grid-cols-2">
            @foreach($labels as $field => $label)
                @if(isset($details[$field]) && $details[$field] !== '')
                    <div class="{{ in_array($field, ['alamat_penerima', 'alamat_orang_tua', 'alamat_sekolah_asal', 'alasan_mutasi', 'dokumen_lampiran', 'keperluan']) ? 'sm:col-span-2' : '' }}">
                        <dt>{{ $label }}</dt><dd class="whitespace-pre-line">{{ $details[$field] }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>
@endif
