<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\Disposisi;
use App\Models\KerjaSamaIndustri;
use App\Models\SertifikatSiswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TemplateSertifikat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. Akun sekolah dan penerima disposisi. Password akun seed: password123.
        // ---------------------------------------------------------------------
        $kepsek = User::updateOrCreate(
            ['email' => 'kepsek@smkn4.com'],
            [
                'name' => 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
                'password' => Hash::make('password123'),
                'role' => 'kepala_sekolah',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $tu = User::updateOrCreate(
            ['email' => 'tu@smkn4.com'],
            [
                'name' => 'Rina Marlina, S.Kom. (Koordinator TU)',
                'password' => Hash::make('password123'),
                'role' => 'tu',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->call(KepalaTUSeeder::class);

        $humas = User::updateOrCreate(
            ['email' => 'humas@smkn4.com'],
            [
                'name' => 'Bambang Haryanto, S.Pd.',
                'password' => Hash::make('password123'),
                'role' => 'humas',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $akunPenerima = [
            ['kurikulum@smkn4.com', 'Dr. Andi Saputra, M.Pd.', 'waka_kurikulum'],
            ['wakakesiswaan@smkn4.com', 'Wakil Kepala Sekolah Bidang Kesiswaan', 'waka_kesiswaan'],
            ['wakasarpras@smkn4.com', 'Wakil Kepala Sekolah Bidang Sarana Prasarana', 'waka_sarpras'],
            ['wakahumas@smkn4.com', 'Wakil Kepala Sekolah Bidang Humas', 'waka_humas'],
            ['pembina.osis@smkn4.com', 'Pembina OSIS', 'pembina_osis'],
            ['bendahara.bos@smkn4.com', 'Bendahara Sekolah/BOS', 'bendahara_bos'],
            ['bendahara.spp@smkn4.com', 'Bendahara SPP', 'bendahara_spp'],
        ];

        foreach ($akunPenerima as [$email, $name, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => $role,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 2. Data Awal Agenda (3 Agenda)
        // ---------------------------------------------------------------------
        Agenda::updateOrCreate(
            ['judul' => 'Rapat Koordinasi Guru'],
            [
                'deskripsi' => 'Rapat koordinasi bulanan dewan guru membahas evaluasi pembelajaran semester ganjil dan persiapan program kerja kurikulum merdeka.',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
                'waktu' => '09:00:00',
                'lokasi' => 'Ruang Guru SMKN 4 Tanjungpinang',
                'tipe' => 'rapat',
                'is_penting' => true,
                'created_by' => $kepsek->id,
            ]
        );

        Agenda::updateOrCreate(
            ['judul' => 'Supervisi Kepala Sekolah'],
            [
                'deskripsi' => 'Pelaksanaan supervisi administrasi pembelajaran dan pemantauan kegiatan belajar mengajar di ruang kelas serta laboratorium kejuruan.',
                'tanggal_mulai' => now()->addDays(3)->toDateString(),
                'tanggal_selesai' => now()->addDays(5)->toDateString(),
                'waktu' => '08:00:00',
                'lokasi' => 'Ruang Kelas & Lab Komputer',
                'tipe' => 'kegiatan',
                'is_penting' => false,
                'created_by' => $kepsek->id,
            ]
        );

        Agenda::updateOrCreate(
            ['judul' => 'Ujian Sertifikasi PKL'],
            [
                'deskripsi' => 'Uji kompetensi, verifikasi portofolio, dan sidang laporan Praktik Kerja Lapangan (PKL) siswa kelas XII bersama tim penguji dari industri mitra.',
                'tanggal_mulai' => now()->addDays(10)->toDateString(),
                'tanggal_selesai' => now()->addDays(12)->toDateString(),
                'waktu' => '08:30:00',
                'lokasi' => 'Aula Utama & Lab RPL SMKN 4 Tanjungpinang',
                'tipe' => 'deadline',
                'is_penting' => true,
                'created_by' => $humas->id,
            ]
        );

        // ---------------------------------------------------------------------
        // 3. Data Awal Mitra Industri (3 Mitra Industri)
        // ---------------------------------------------------------------------
        $mitra1 = KerjaSamaIndustri::updateOrCreate(
            ['nama_industri' => 'KANWIL KEMENAG PROVINSI KEPULAUAN RIAU'],
            [
                'alamat' => 'Jl. Daeng Celak, Senggarang, Kota Tanjungpinang',
                'kontak' => '0771-456789',
                'email' => 'kanwilkepri@kemenag.go.id',
                'program_keahlian' => 'Rekayasa Perangkat Lunak',
                'tanggal_mulai' => '2024-01-10',
                'tanggal_berakhir' => '2027-01-10',
                'status' => 'aktif',
                'catatan' => 'Kerja sama penyediaan tempat PKL administrasi, jaringan, dan sistem informasi.',
                'created_by' => $humas->id,
            ]
        );

        $mitra2 = KerjaSamaIndustri::updateOrCreate(
            ['nama_industri' => 'PT. Sinar Digital'],
            [
                'alamat' => 'Jl. Basuki Rahmat No. 45, Tanjungpinang',
                'kontak' => '0813-6655-4433',
                'email' => 'info@sinardigital.com',
                'program_keahlian' => 'Teknik Komputer dan Jaringan',
                'tanggal_mulai' => '2024-06-15',
                'tanggal_berakhir' => '2026-12-15',
                'status' => 'aktif',
                'catatan' => 'Kerja sama praktik kerja industri bidang jaringan komputer, cloud computing, dan fiber optik.',
                'created_by' => $humas->id,
            ]
        );

        $mitra3 = KerjaSamaIndustri::updateOrCreate(
            ['nama_industri' => 'CV. Maritim Jaya'],
            [
                'alamat' => 'Jl. Pelabuhan Sri Bintan Pura No. 12, Tanjungpinang',
                'kontak' => '0811-2233-4455',
                'email' => 'kontak@maritimjaya.id',
                'program_keahlian' => 'Nautika Kapal Niaga',
                'tanggal_mulai' => '2023-08-01',
                'tanggal_berakhir' => '2025-08-01',
                'status' => 'berakhir',
                'catatan' => 'Kerja sama magang industri maritim, logistik pelabuhan, dan penyerapan lulusan.',
                'created_by' => $humas->id,
            ]
        );

        // ---------------------------------------------------------------------
        // 4. Data Awal Surat Masuk (2 Surat Masuk) & Disposisi (1 Disposisi)
        // ---------------------------------------------------------------------
        $suratMasuk1 = SuratMasuk::updateOrCreate(
            ['no_agenda' => 'AGD/09/2026/0001'],
            [
                'no_surat' => '421/DISDIK-KEPRI/IX/2026/089',
                'tanggal_surat' => now()->subDays(4)->toDateString(),
                'tanggal_diterima' => now()->subDays(2)->toDateString(),
                'pengirim' => 'Dinas Pendidikan Provinsi Kepulauan Riau',
                'perihal' => 'Undangan Rapat Koordinasi Penyelenggaraan Uji Kompetensi Keahlian (UKK) SMK Se-Kepri',
                'isi_ringkas' => 'Rapat koordinasi teknis, verifikasi kelayakan Tempat Uji Kompetensi (TUK), dan sinkronisasi penguji industri untuk pelaksanaan UKK tahun ajaran berjalan.',
                'sifat' => 'segera',
                'status' => 'didisposisi',
                'created_by' => $tu->id,
            ]
        );

        // Disposisi dari Kepala Sekolah ke TU
        Disposisi::updateOrCreate(
            [
                'surat_masuk_id' => $suratMasuk1->id,
                'dari_user_id' => $kepsek->id,
                'kepada_user_id' => $tu->id,
            ],
            [
                'instruksi' => 'Tindak lanjuti dan siapkan berkas koordinasi serta konfirmasi kehadiran ke Dinas Pendidikan.',
                'catatan' => 'Koordinasikan dengan Waka Kurikulum dan siapkan data kelayakan TUK sebelum rapat berlangsung.',
                'status' => 'diproses',
                'status_persetujuan' => 'setuju',
            ]
        );

        $suratMasuk2 = SuratMasuk::updateOrCreate(
            ['no_agenda' => 'AGD/09/2026/0002'],
            [
                'no_surat' => '045/B/PT-TM/VIII/2026',
                'tanggal_surat' => now()->subDays(6)->toDateString(),
                'tanggal_diterima' => now()->subDays(3)->toDateString(),
                'pengirim' => 'KANWIL KEMENAG PROVINSI KEPULAUAN RIAU',
                'perihal' => 'Konfirmasi Penerimaan Siswa Praktik Kerja Lapangan (PKL) Periode Ganjil',
                'isi_ringkas' => 'Pemberitahuan bahwa Kanwil Kemenag Kepri siap menerima siswa kompetensi keahlian Rekayasa Perangkat Lunak untuk melaksanakan PKL selama 6 bulan.',
                'sifat' => 'biasa',
                'status' => 'diterima',
                'created_by' => $tu->id,
            ]
        );

        // ---------------------------------------------------------------------
        // 5. Data Awal Surat Keluar (1 Surat Keluar - Surat Undangan)
        // ---------------------------------------------------------------------
        SuratKeluar::updateOrCreate(
            ['no_surat' => 'UND/09/2026/0001'],
            [
                'tujuan' => 'Kepala Dinas Pendidikan Provinsi Kepulauan Riau',
                'perihal' => 'Konfirmasi Kehadiran Rapat Koordinasi UKK SMK 2026',
                'tgl_pelaksanaan' => now()->addWeek()->toDateString(),
                'waktu' => '08.00 WIB s.d. selesai',
                'tempat' => 'Aula SMK Negeri 4 Tanjungpinang',
                'acara' => 'Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau',
                'isi_surat' => 'Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau. Kepala Sekolah dan Waka Kurikulum menyatakan kesediaan hadir pada acara tersebut.',
                'referensi_surat_masuk_id' => $suratMasuk1->id,
                'status' => 'disetujui',
                'created_by' => $tu->id,
                'approved_by' => $kepsek->id,
                'approved_at' => now(),
            ]
        );

        // ---------------------------------------------------------------------
        // 6. Template Sertifikat & Data Sertifikat Siswa PKL (Sesuai Fisik Asli)
        // ---------------------------------------------------------------------
        $template = TemplateSertifikat::firstOrCreate(
            ['nama' => 'Template Sertifikat PKL Standar SMKN 4'],
            [
                'deskripsi' => 'Format resmi sertifikat Praktik Kerja Lapangan SMKN 4 Tanjungpinang',
                'file_path' => null,
                'is_active' => true,
            ]
        );

        // Sertifikat 1: Diterbitkan (Sesuai data foto sertifikat fisik)
        SertifikatSiswa::updateOrCreate(
            ['no_sertifikat' => 'B/423.4/P.06.209/I-SMKN4/PKL/2025'],
            [
                'siswa_user_id' => null,
                'nama_siswa' => 'SYARIFAH NAZURA NAZALYA',
                'nisn' => '3684',
                'kelas' => 'XII',
                'program_keahlian' => 'Rekayasa Perangkat Lunak',
                'kerja_sama_industri_id' => $mitra1->id,
                'tanggal_mulai_pkl' => '2025-01-06',
                'tanggal_selesai_pkl' => '2025-06-28',
                'nilai' => 95.00,
                'predikat' => 'SANGAT BAIK',
                'template_id' => $template->id,
                'status' => 'diterbitkan',
                'diterbitkan_oleh' => $kepsek->id,
                'diterbitkan_pada' => now()->subMonths(6),
            ]
        );

        // Sertifikat 2: Terverifikasi
        SertifikatSiswa::updateOrCreate(
            ['no_sertifikat' => 'B/423.4/P.06.209/I-SMKN4/PKL/2026'],
            [
                'siswa_user_id' => null,
                'nama_siswa' => 'AHMAD FAUZI PRATAMA',
                'nisn' => '3685',
                'kelas' => 'XII',
                'program_keahlian' => 'Teknik Komputer dan Jaringan',
                'kerja_sama_industri_id' => $mitra2->id,
                'tanggal_mulai_pkl' => '2026-01-05',
                'tanggal_selesai_pkl' => '2026-06-30',
                'nilai' => 88.50,
                'predikat' => 'BAIK',
                'template_id' => $template->id,
                'status' => 'terverifikasi',
                'diterbitkan_oleh' => null,
                'diterbitkan_pada' => null,
            ]
        );
    }
}
