<?php

namespace Tests\Feature;

use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratDinasTest extends TestCase
{
    use RefreshDatabase;

    private function userTu(): User
    {
        return User::factory()->create(['role' => 'tu']);
    }

    private function userKepsek(): User
    {
        return User::factory()->create(['role' => 'kepala_sekolah']);
    }

    private function buatSuratDinas(array $overrides = []): SuratDinas
    {
        return SuratDinas::create(array_merge([
            'jenis_surat' => 'SK',
            'no_surat' => null,
            'tentang_judul' => 'Penetapan Panitia Ujian',
            'tgl_ditetapkan' => '2026-10-01',
            'status' => 'menunggu_persetujuan',
            'detail_json' => [
                'menimbang' => 'a. Alasan pertama.',
                'mengingat' => '1. Dasar hukum.',
                'dictum_list' => ['Menetapkan panitia ujian sekolah.'],
            ],
            'created_by' => $this->userTu()->id,
        ], $overrides));
    }

    public function test_index_dapat_diakses_tu(): void
    {
        $this->actingAs($this->userTu())
            ->get(route('surat-dinas.index'))
            ->assertOk()
            ->assertSee('Surat Dinas');
    }

    public function test_tu_dapat_menghapus_satu_surat_dinas(): void
    {
        $surat = $this->buatSuratDinas(['status' => 'disetujui']);
        $lain = $this->buatSuratDinas(['jenis_surat' => 'SPT']);

        $this->actingAs($this->userTu())
            ->get(route('surat-dinas.index'))
            ->assertSee(route('surat-dinas.destroy', $surat), false);

        $this->delete(route('surat-dinas.destroy', $surat))
            ->assertRedirect(route('surat-dinas.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($surat);
        $this->assertModelExists($lain);
    }

    public function test_role_selain_tu_tidak_dapat_menghapus_surat_dinas(): void
    {
        $surat = $this->buatSuratDinas();

        foreach (array_diff(array_keys(User::AVAILABLE_ROLES), ['tu']) as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->delete(route('surat-dinas.destroy', $surat))
                ->assertForbidden();
        }

        $this->assertModelExists($surat);
    }

    public function test_index_ditolak_untuk_role_lain(): void
    {
        foreach (['kepala_sekolah', 'bendahara_spp', 'humas', 'kepala_tu'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('surat-dinas.index'))
                ->assertForbidden();
        }
    }

    public function test_kepsek_tidak_memiliki_akses_modul_surat_dinas(): void
    {
        $surat = $this->buatSuratDinas();
        $this->actingAs($this->userKepsek());

        foreach (['index', 'create-sk', 'create-spt', 'show', 'pdf'] as $action) {
            $parameters = in_array($action, ['show', 'pdf'], true) ? [$surat] : [];
            $this->get(route('surat-dinas.'.$action, $parameters))->assertForbidden();
        }

        $this->post(route('surat-dinas.store'), [])->assertForbidden();
        $this->assertSame(1, SuratDinas::count());

        $this->get(route('disposisi.surat-dinas.show', $surat))
            ->assertOk()
            ->assertSee($surat->tentang_judul)
            ->assertSee(route('surat-dinas.setujui', $surat), false)
            ->assertSee(route('surat-dinas.revisi', $surat), false)
            ->assertDontSee('href="'.route('surat-dinas.index').'"', false)
            ->assertDontSee('href="'.route('surat-dinas.pdf', $surat).'"', false);
    }

    public function test_form_create_sk_dan_spt_render(): void
    {
        $user = $this->userTu();

        $this->actingAs($user)
            ->get(route('surat-dinas.create-sk'))
            ->assertOk()
            ->assertSee('name="jenis_surat" value="SK"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Ajukan ke Kepala Sekolah')
            ->assertSee('type="submit"', false)
            // State Alpine harus ter-escape sebagai atribut HTML (&quot;),
            // bukan kutip mentah yang memutus atribut x-data (bug teks bocor).
            ->assertSee('dictums: [&quot;', false)
            ->assertDontSee('dictums: ["', false)
            ->assertSee('getLabel', false)
            ->assertSee('dictum_list[', false)
            ->assertSee('Tambah Diktum');

        $this->actingAs($user)
            ->get(route('surat-dinas.create-spt'))
            ->assertOk()
            ->assertSee('name="jenis_surat" value="SPT"', false)
            ->assertSee('siswa: [{&quot;', false)
            ->assertDontSee('siswa: [{"', false)
            ->assertSee('peserta_list')
            ->assertSee('Tambah Peserta');
    }

    public function test_store_sk_menyimpan_detail_json_dan_diajukan(): void
    {
        $user = $this->userTu();

        $response = $this->actingAs($user)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SK',
            'no_surat' => '386 TAHUN 2026',
            'tentang_judul' => 'Penetapan Panitia Pelaksana Ujian Sekolah',
            'tgl_ditetapkan' => '2026-10-01',
            'tempat_ditetapkan' => 'Tanjungpinang',
            'menimbang' => 'a. Bahwa panitia perlu dibentuk.',
            'mengingat' => '1. Undang-Undang Nomor 20 Tahun 2003.',
            'dictum_list' => [
                'Menetapkan susunan panitia ujian sekolah.',
                'Menetapkan masa tugas panitia.',
            ],
            'tembusan' => '1. Arsip.',
        ])->assertSessionHasNoErrors();

        $surat = SuratDinas::sole();

        // Status awal saat TU membuat SK/SPT: menunggu persetujuan Kepsek.
        $response->assertRedirect(route('surat-dinas.show', $surat));
        $this->assertDatabaseHas('surat_dinas', [
            'jenis_surat' => 'SK',
            'no_surat' => '386 TAHUN 2026',
            'tentang_judul' => 'Penetapan Panitia Pelaksana Ujian Sekolah',
            'status' => 'menunggu_persetujuan',
            'created_by' => $user->id,
        ]);
        $this->assertSame('a. Bahwa panitia perlu dibentuk.', $surat->detail_json['menimbang']);
        $this->assertSame('Tanjungpinang', $surat->detail_json['tempat_ditetapkan']);
        $this->assertCount(2, $surat->detail_json['dictum_list']);
        $this->assertSame('Menetapkan susunan panitia ujian sekolah.', $surat->detail_json['dictum_list'][0]);
    }

    public function test_store_spt_menyimpan_detail_json_dan_peserta(): void
    {
        $user = $this->userTu();

        $this->actingAs($user)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SPT',
            'tentang_judul' => 'Pengawasan Ujian Sekolah',
            'tgl_ditetapkan' => '2026-10-05',
            'dasar' => '1. Surat Edaran Kepala Dinas.',
            'pegawai_nama' => 'Rina Wati, S.Pd.',
            'pegawai_nip' => '19850315 201001 2 004',
            'untuk_tugas' => 'Mengawasi pelaksanaan Ujian Sekolah kelas XII.',
            'untuk_tempat' => 'Gedung A SMK Negeri 4 Tanjungpinang',
            'untuk_tanggal_mulai' => '2026-10-05',
            'untuk_tanggal_selesai' => '2026-10-07',
            'peserta_list' => [
                ['nama' => 'Budi Santoso', 'kelas' => 'XII TKJ 1'],
                ['nama' => '', 'kelas' => ''],
                ['nama' => 'Siti Aminah', 'kelas' => 'XII TKJ 2'],
            ],
        ])->assertSessionHasNoErrors();

        $surat = SuratDinas::sole();

        $this->assertSame('menunggu_persetujuan', $surat->status);
        $this->assertSame('Rina Wati, S.Pd.', $surat->detail_json['pegawai_nama']);
        // Baris kosong otomatis dibuang.
        $this->assertCount(2, $surat->detail_json['peserta_list']);
        $this->assertSame('Budi Santoso', $surat->detail_json['peserta_list'][0]['nama']);
    }

    public function test_store_wajib_isi_field_wajib_per_jenis(): void
    {
        $user = $this->userTu();

        $this->actingAs($user)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SK',
            'tentang_judul' => 'Surat Tanpa Isi',
        ])->assertSessionHasErrors(['menimbang', 'mengingat', 'dictum_list']);

        // SK wajib punya minimal satu diktum terisi (baris kosong dibuang).
        $this->actingAs($user)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SK',
            'tentang_judul' => 'Surat Tanpa Diktum',
            'menimbang' => 'a. Alasan.',
            'mengingat' => '1. Dasar.',
            'dictum_list' => ['', '   '],
        ])->assertSessionHasErrors('dictum_list');

        $this->actingAs($user)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SPT',
            'tentang_judul' => 'Surat Tanpa Pegawai',
        ])->assertSessionHasErrors(['pegawai_nama', 'untuk_tugas']);

        $this->assertSame(0, SuratDinas::count());
    }

    public function test_show_dan_cetak_pdf(): void
    {
        $user = $this->userTu();

        $suratSk = SuratDinas::create([
            'jenis_surat' => 'SK',
            'no_surat' => '421.5/SMKN4/10/2026/0001',
            'tentang_judul' => 'Penetapan Panitia Ujian',
            'tgl_ditetapkan' => '2026-10-01',
            'status' => 'disetujui',
            'detail_json' => [
                'menimbang' => 'a. Alasan pertama.',
                'dictum_list' => ['Menetapkan panitia.', 'Masa tugas tiga bulan.'],
            ],
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('surat-dinas.show', $suratSk))
            ->assertOk()
            ->assertSee('Penetapan Panitia Ujian')
            ->assertSee('Menimbang')
            ->assertSee('KESATU')
            ->assertSee('KEDUA')
            ->assertSee(route('surat-dinas.pdf', $suratSk), false);

        $this->actingAs($user)
            ->get(route('surat-dinas.pdf', $suratSk))
            ->assertOk();

        $response = $this->actingAs($user)->get(route('surat-dinas.pdf', $suratSk));
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_cetak_pdf_spt(): void
    {
        $user = $this->userTu();

        $suratSpt = SuratDinas::create([
            'jenis_surat' => 'SPT',
            'no_surat' => '400.3.8/115/2026',
            'tentang_judul' => 'Pengawasan Ujian Sekolah',
            'tgl_ditetapkan' => '2026-10-05',
            'status' => 'disetujui',
            'detail_json' => [
                'pegawai_nama' => 'Rina Wati, S.Pd.',
                'untuk_tugas' => 'Mengawasi Ujian Sekolah.',
                'peserta_list' => [['nama' => 'Budi Santoso', 'kelas' => 'XII TKJ 1']],
            ],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('surat-dinas.pdf', $suratSpt));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_cetak_pdf_ditolak_sebelum_disetujui(): void
    {
        $user = $this->userTu();

        foreach (['draft', 'menunggu_persetujuan', 'ditolak'] as $status) {
            $surat = $this->buatSuratDinas(['status' => $status]);

            $this->actingAs($user)
                ->get(route('surat-dinas.pdf', $surat))
                ->assertForbidden();

            $surat->delete();
        }
    }

    public function test_tombol_pdf_pada_show_hanya_tampil_setelah_disetujui(): void
    {
        $user = $this->userTu();

        $belumDisetujui = $this->buatSuratDinas();
        $this->actingAs($user)
            ->get(route('surat-dinas.show', $belumDisetujui))
            ->assertOk()
            ->assertDontSee(route('surat-dinas.pdf', $belumDisetujui), false);

        $disetujui = $this->buatSuratDinas([
            'status' => 'disetujui',
            'no_surat' => '421.5/SMKN4/10/2026/0001',
        ]);
        $this->actingAs($user)
            ->get(route('surat-dinas.show', $disetujui))
            ->assertOk()
            ->assertSee(route('surat-dinas.pdf', $disetujui), false);
    }

    public function test_antrian_kepsek_menampilkan_surat_menunggu_persetujuan(): void
    {
        $tu = $this->userTu();

        $suratDinas = $this->buatSuratDinas(['tentang_judul' => 'Penetapan Panitia Ujian Sekolah']);
        $suratKeluar = SuratKeluar::create([
            'tujuan' => 'Dinas Pendidikan Provinsi Kepulauan Riau',
            'perihal' => 'Undangan Rapat Koordinasi Kepala Sekolah',
            'isi_surat' => 'Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri rapat koordinasi.',
            'status' => 'menunggu_persetujuan',
            'created_by' => $tu->id,
        ]);

        $this->actingAs($this->userKepsek())
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertSee('Surat menunggu persetujuan')
            ->assertSee('Penetapan Panitia Ujian Sekolah')
            ->assertSee('Undangan Rapat Koordinasi Kepala Sekolah')
            ->assertSee(route('disposisi.surat-dinas.show', $suratDinas), false)
            ->assertSee(route('surat-keluar.show', $suratKeluar), false);
    }

    public function test_kepsek_setujui_surat_dinas_menerbitkan_nomor(): void
    {
        $tu = $this->userTu();
        $kepsek = $this->userKepsek();

        $this->actingAs($tu)->post(route('surat-dinas.store'), [
            'jenis_surat' => 'SK',
            'tentang_judul' => 'Penetapan Panitia Pelaksana Ujian Sekolah',
            'tgl_ditetapkan' => '2026-10-01',
            'menimbang' => 'a. Bahwa panitia perlu dibentuk.',
            'mengingat' => '1. Undang-Undang Nomor 20 Tahun 2003.',
            'dictum_list' => ['Menetapkan susunan panitia ujian sekolah.'],
        ])->assertSessionHasNoErrors();

        $surat = SuratDinas::sole();
        $this->assertSame('menunggu_persetujuan', $surat->status);

        $this->actingAs($kepsek)
            ->post(route('surat-dinas.setujui', $surat))
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $surat->refresh();
        $this->assertSame('disetujui', $surat->status);
        $this->assertSame($kepsek->id, $surat->approved_by);
        $this->assertNotNull($surat->approved_at);
        $this->assertMatchesRegularExpression('#^421\.5/SMKN4/\d{2}/\d{4}/\d{4}$#', $surat->no_surat);

        // Setelah disetujui, cetak PDF terbuka untuk TU.
        $this->actingAs($tu)
            ->get(route('surat-dinas.pdf', $surat))
            ->assertOk();
    }

    public function test_setujui_surat_dinas_spt_memakai_prefix_nomor_berbeda(): void
    {
        $surat = $this->buatSuratDinas([
            'jenis_surat' => 'SPT',
            'detail_json' => [
                'pegawai_nama' => 'Rina Wati, S.Pd.',
                'untuk_tugas' => 'Mengawasi Ujian Sekolah.',
            ],
        ]);

        $this->actingAs($this->userKepsek())
            ->post(route('surat-dinas.setujui', $surat))
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $this->assertMatchesRegularExpression('#^421\.5\.1/SMKN4/\d{2}/\d{4}/\d{4}$#', $surat->fresh()->no_surat);
    }

    public function test_kepsek_tolak_surat_dinas_dengan_catatan(): void
    {
        $surat = $this->buatSuratDinas();
        $kepsek = $this->userKepsek();

        $this->actingAs($kepsek)
            ->post(route('surat-dinas.revisi', $surat), [])
            ->assertSessionHasErrors('catatan_revisi');

        $this->actingAs($kepsek)
            ->post(route('surat-dinas.revisi', $surat), [
                'catatan_revisi' => 'Daftar peserta belum dilampirkan.',
            ])
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $surat->refresh();
        $this->assertSame('ditolak', $surat->status);
        $this->assertSame('Daftar peserta belum dilampirkan.', $surat->catatan_revisi);
        $this->assertNull($surat->approved_by);
    }

    public function test_persetujuan_surat_dinas_hanya_kepsek_dan_admin(): void
    {
        $surat = $this->buatSuratDinas();

        foreach (['waka_kurikulum', 'guru'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->post(route('surat-dinas.setujui', $surat))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('surat-dinas.revisi', $surat), ['catatan_revisi' => 'Revisi.'])
                ->assertForbidden();
        }

        $this->assertSame('menunggu_persetujuan', $surat->fresh()->status);

        $tu = User::factory()->create(['role' => 'tu']);
        $this->actingAs($tu)
            ->post(route('surat-dinas.setujui', $surat))
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $this->assertSame('disetujui', $surat->fresh()->status);
    }

    public function test_pdf_sk_menampilkan_seluruh_diktum_bergaris_otomatis(): void
    {
        $teksDiktum = [
            'Menetapkan susunan panitia ujian sekolah.',
            'Menetapkan masa tugas panitia tiga bulan.',
            'Menetapkan tanggung jawab sekretariat panitia.',
            'Menetapkan pembiayaan dari dana sekolah.',
            'Menetapkan penanggung jawab teknis pelaksanaan.',
            'Menetapkan ketua panitia yang bertanda tangan di bawah ini.',
        ];
        $surat = $this->buatSuratDinas([
            'status' => 'disetujui',
            'no_surat' => '421.5/SMKN4/10/2026/0001',
            'detail_json' => [
                'menimbang' => 'a. Bahwa panitia perlu dibentuk.',
                'mengingat' => '1. Undang-Undang Nomor 20 Tahun 2003.',
                'dictum_list' => $teksDiktum,
                'tempat_ditetapkan' => 'Tanjungpinang',
            ],
        ]);

        $html = view('surat-dinas.pdf-sk', ['suratDinas' => $surat, 'kepsek' => null])->render();

        foreach (['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        foreach ($teksDiktum as $teks) {
            $this->assertStringContainsString($teks, $html);
        }
        $this->assertStringContainsString('MEMUTUSKAN', $html);
    }

    public function test_pdf_spt_menampilkan_dasar_memerintahkan_dan_tabel_peserta(): void
    {
        $surat = $this->buatSuratDinas([
            'jenis_surat' => 'SPT',
            'status' => 'disetujui',
            'no_surat' => '421.5.1/SMKN4/10/2026/0001',
            'detail_json' => [
                'dasar' => "1. Surat Edaran Kepala Dinas Pendidikan Nomor 421.5/SE/2026.\n2. Petunjuk Teknis Pelaksanaan Ujian Sekolah.",
                'pegawai_nama' => 'Rina Wati, S.Pd.',
                'pegawai_pangkat_golongan' => 'Penata Tk. I / III.d',
                'pegawai_nip' => '19850315 201001 2 004',
                'pegawai_jabatan' => 'Guru Mata Pelajaran Bahasa Indonesia',
                'untuk_tugas' => 'Mengawasi pelaksanaan Ujian Sekolah kelas XII.',
                'untuk_tempat' => 'Gedung A',
                'untuk_tanggal_mulai' => '2026-10-05',
                'untuk_tanggal_selesai' => '2026-10-07',
                'peserta_list' => [
                    ['nama' => 'Budi Santoso', 'kelas' => 'XII TKJ 1'],
                    ['nama' => 'Siti Aminah', 'kelas' => 'XII TKJ 2'],
                ],
            ],
        ]);

        $html = view('surat-dinas.pdf-spt', ['suratDinas' => $surat, 'kepsek' => null])->render();

        $this->assertStringContainsString('DASAR', $html);
        $this->assertStringContainsString('MEMERINTAHKAN', $html);
        $this->assertStringContainsString('Kepada', $html);
        $this->assertStringContainsString('Untuk', $html);
        $this->assertStringContainsString('Daftar Siswa / Peserta', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Siti Aminah', $html);
        $this->assertStringContainsString('XII TKJ 1', $html);
    }

    public function test_tampilan_menu_sidebar_hanya_untuk_role_berwenang(): void
    {
        $urlMenu = route('surat-dinas.index');

        $tu = User::factory()->create(['role' => 'tu']);
        $this->actingAs($tu)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($urlMenu);

        foreach (['kepala_sekolah', 'humas'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee($urlMenu);
        }
    }
}
