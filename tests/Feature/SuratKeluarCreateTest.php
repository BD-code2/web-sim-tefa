<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_surat_undangan_render(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)
            ->get('/surat-keluar/create')
            ->assertOk()
            ->assertSee('name="tujuan"', false)
            ->assertSee('name="perihal"', false)
            ->assertSee('name="isi_surat"', false);
    }

    public function test_link_lama_dengan_referensi_tetap_error_free(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)
            ->get(route('surat-keluar.create', ['referensi_id' => 99]))
            ->assertOk();
    }

    public function test_kategori_sk_dialihkan_ke_modul_surat_dinas(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)
            ->get('/surat-keluar/create/SK')
            ->assertRedirect(route('surat-dinas.create-sk'));
    }

    public function test_kategori_spt_dialihkan_ke_modul_surat_dinas(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)
            ->get('/surat-keluar/create/SPT')
            ->assertRedirect(route('surat-dinas.create-spt'));
    }

    public function test_kategori_tidak_valid_kembali_ke_form_surat_keluar(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)
            ->get('/surat-keluar/create/XXX')
            ->assertRedirect(route('surat-keluar.create'));
    }

    public function test_store_surat_undangan_sebagai_draft(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)->post('/surat-keluar', [
            'tujuan' => 'Kepala Dinas Pendidikan Provinsi Kepulauan Riau',
            'perihal' => 'Undangan Rapat Koordinasi UKK SMK 2026',
            'acara' => 'Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau',
            'tgl_pelaksanaan' => '2026-10-15',
            'waktu' => '08.00 WIB s.d. selesai',
            'tempat' => 'Aula SMK Negeri 4 Tanjungpinang',
            'isi_surat' => 'Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri rapat koordinasi.',
            'action' => 'draft',
        ])->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('surat_keluars', [
            'tujuan' => 'Kepala Dinas Pendidikan Provinsi Kepulauan Riau',
            'perihal' => 'Undangan Rapat Koordinasi UKK SMK 2026',
            'acara' => 'Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $this->assertSame('2026-10-15', SuratKeluar::sole()->tgl_pelaksanaan->toDateString());
    }

    public function test_store_surat_undangan_diajukan(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $response = $this->actingAs($user)->post('/surat-keluar', [
            'tujuan' => 'Kepala Sekolah',
            'perihal' => 'Undangan Apel Pagi',
            'action' => 'ajukan',
        ])->assertSessionHasNoErrors();

        $surat = SuratKeluar::sole();

        $response->assertRedirect(route('surat-keluar.show', $surat));
        $this->assertSame('menunggu_persetujuan', $surat->status);
    }

    public function test_store_wajib_tujuan_dan_perihal(): void
    {
        $user = User::factory()->create(['role' => 'tu']);

        $this->actingAs($user)->post('/surat-keluar', [
            'acara' => 'Tanpa Tujuan',
            'action' => 'draft',
        ])->assertSessionHasErrors(['tujuan', 'perihal']);

        $this->assertSame(0, SuratKeluar::count());
    }

    public function test_generator_menyimpan_semua_jenis_surat_dan_mendukung_cetak_pdf(): void
    {
        $user = User::factory()->create(['role' => 'tu']);
        $dataPerJenis = [
            'undangan' => [
                'tanggal_surat' => '2026-09-29', 'tujuan' => 'Orang Tua Siswa', 'perihal' => 'Undangan Kegiatan',
                'nama_siswa' => 'Budi', 'kelas_siswa' => 'XII RPL 1', 'tahun_pelajaran' => '2026/2027',
                'alamat_penerima' => 'Jl. Merdeka 1', 'nama_kegiatan' => 'Pentas Seni', 'keperluan' => 'Koordinasi',
                'tgl_pelaksanaan' => '2026-10-10', 'waktu' => '08.00 WIB', 'tempat' => 'Aula Sekolah',
            ],
            'spt' => [
                'tanggal_surat' => '2026-09-29', 'dasar' => 'Surat tugas kepala sekolah', 'pegawai_nama' => 'Ani',
                'pegawai_pangkat_golongan' => 'III/a', 'pegawai_nip' => '198001012005012001', 'pegawai_jabatan' => 'Guru',
                'untuk_tugas' => 'Mendampingi siswa', 'tempat' => 'Jakarta', 'tgl_pelaksanaan' => '2026-10-10',
                'tempat_ditetapkan' => 'Tanjungpinang', 'daftar_siswa_teks' => 'Budi|XII RPL 1',
            ],
            'sk' => [
                'tanggal_surat' => '2026-09-29', 'tahun_pelajaran' => '2026/2027', 'tentang_judul' => 'Pembina OSIS',
                'pegawai_nama' => 'Ani', 'pegawai_jabatan' => 'Guru', 'diktum_teks' => 'Mengangkat sebagai pembina',
                'tanggal_mulai_berlaku' => '2026-10-01', 'tempat_ditetapkan' => 'Tanjungpinang',
            ],
            'mutasi' => [
                'tanggal_surat' => '2026-09-29', 'tujuan' => 'Kepala Sekolah Tujuan', 'nama_siswa' => 'Budi',
                'nisn' => '1234567890', 'kelas_siswa' => 'XII RPL 1', 'semester' => 'Ganjil',
                'nama_orang_tua' => 'Orang Tua', 'alamat_orang_tua' => 'Jl. Merdeka', 'sekolah_asal' => 'SMKN 4',
                'alamat_sekolah_asal' => 'Jl. Nusantara', 'sekolah_tujuan' => 'SMKN 1', 'alasan_mutasi' => 'Pindah domisili',
                'dokumen_lampiran' => 'Surat permohonan',
            ],
        ];

        foreach ($dataPerJenis as $jenis => $data) {
            $response = $this->actingAs($user)->post(route('surat-keluar.store'), [
                'jenis_surat' => $jenis,
                ...$data,
            ]);

            $response->assertSessionHasNoErrors()->assertRedirect();
            $surat = SuratKeluar::latest('id')->firstOrFail();
            $this->assertSame($jenis, $surat->jenis_surat);
            $this->assertSame('menunggu_persetujuan', $surat->status);

            $this->actingAs($user)->get(route('surat-keluar.pdf', $surat))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');
        }
    }

    public function test_persetujuan_kepala_sekolah_menerbitkan_nomor_dan_bisa_diunduh(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $surat = SuratKeluar::create([
            'jenis_surat' => 'undangan', 'tujuan' => 'Orang Tua', 'perihal' => 'Undangan',
            'status' => 'menunggu_persetujuan', 'created_by' => $tu->id,
            'diperiksa_kepala_tu_at' => now(),
            'diperiksa_kepala_tu_oleh' => User::factory()->create(['role' => 'kepala_tu'])->id,
            'diteruskan_ke_kepala_sekolah_at' => now(),
            'diteruskan_ke_kepala_sekolah_oleh' => $tu->id,
        ]);

        $this->actingAs($kepsek)->post(route('disposisi.surat-keluar.setujui', $surat))
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $surat->refresh();
        $this->assertSame('disetujui', $surat->status);
        $this->assertNotEmpty($surat->no_surat);
        $this->actingAs($tu)->get(route('surat-keluar.pdf', $surat))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_index_dan_show_render(): void
    {
        $user = User::factory()->create(['role' => 'tu']);
        $surat = SuratKeluar::create([
            'tujuan' => 'Dinas Pendidikan Provinsi Kepulauan Riau',
            'perihal' => 'Undangan Rapat Koordinasi',
            'acara' => 'Rapat Koordinasi',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('surat-keluar.index'))
            ->assertOk()
            ->assertSee('Undangan Rapat Koordinasi');

        $this->actingAs($user)
            ->get(route('surat-keluar.show', $surat))
            ->assertOk()
            ->assertSee('Undangan Rapat Koordinasi')
            ->assertSee('Dinas Pendidikan Provinsi Kepulauan Riau');
    }

    public function test_edit_dan_update_draft(): void
    {
        $user = User::factory()->create(['role' => 'tu']);
        $surat = SuratKeluar::create([
            'tujuan' => 'Kepala Sekolah',
            'perihal' => 'Undangan Lama',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('surat-keluar.edit', $surat))
            ->assertOk()
            ->assertSee('Undangan Lama');

        $this->actingAs($user)->put(route('surat-keluar.update', $surat), [
            'tujuan' => 'Wakil Kepala Sekolah Bidang Kurikulum',
            'perihal' => 'Undangan Baru',
            'action' => 'draft',
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('surat-keluar.show', $surat));

        $this->assertDatabaseHas('surat_keluars', [
            'id' => $surat->id,
            'perihal' => 'Undangan Baru',
            'tujuan' => 'Wakil Kepala Sekolah Bidang Kurikulum',
        ]);
    }

    public function test_surat_disetujui_tidak_bisa_diedit(): void
    {
        $user = User::factory()->create(['role' => 'tu']);
        $surat = SuratKeluar::create([
            'tujuan' => 'Kepala Sekolah',
            'perihal' => 'Undangan Terbit',
            'no_surat' => '421.5/SMKN4/09/2026/0001',
            'status' => 'disetujui',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('surat-keluar.edit', $surat))
            ->assertRedirect(route('surat-keluar.show', $surat));
    }

    public function test_surat_keluar_ditolak_untuk_role_lain(): void
    {
        foreach (['guru', 'humas', 'siswa'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('surat-keluar.index'))
                ->assertForbidden();
        }
    }
}
