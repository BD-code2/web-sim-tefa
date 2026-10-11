<?php

namespace Tests\Feature;

use App\Models\DataSiswa;
use App\Models\PeriodePkl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DataSiswaTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_nama_agenda_pkl_dapat_diedit_oleh_role_berwenang(): void
    {
        $periode = PeriodePkl::create([
            'nama' => 'Agenda Lama',
            'agenda' => 'Kegiatan PKL',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'aktif' => true,
        ]);

        foreach (['tu', 'humas', 'waka_humas'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('data-siswa.periode.edit', $periode))
                ->assertOk()
                ->assertSee('Agenda Lama');
        }

        $this->actingAs($this->userWithRole('tu'))
            ->patch(route('data-siswa.periode.update', $periode), ['nama' => 'Agenda PKL Semester Ganjil'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('data-siswa.index'));

        $this->assertDatabaseHas('periode_pkls', [
            'id' => $periode->id,
            'nama' => 'Agenda PKL Semester Ganjil',
            'agenda' => 'Kegiatan PKL',
        ]);

        $this->actingAs($this->userWithRole('siswa'))
            ->get(route('data-siswa.periode.edit', $periode))
            ->assertForbidden();
    }

    public function test_index_harus_login_terlebih_dulu(): void
    {
        $this->get(route('data-siswa.index'))->assertRedirect(route('login'));
    }

    public function test_index_hanya_untuk_role_yang_berwenang(): void
    {
        $this->actingAs($this->userWithRole('humas'))
            ->get(route('data-siswa.index'))
            ->assertOk()
            ->assertSee('Dashboard Data Siswa')
            ->assertSee('Import Excel')
            ->assertSee('Generate Semua Sertifikat');

        $this->actingAs($this->userWithRole('tu'))
            ->get(route('data-siswa.index'))
            ->assertOk();

        $this->actingAs($this->userWithRole('humas'))
            ->get(route('data-siswa.import'))
            ->assertOk()
            ->assertSee('Pemetaan Kolom');

        $this->actingAs($this->userWithRole('guru'))
            ->get(route('data-siswa.import'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole('guru'))
            ->get(route('data-siswa.index'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole('guru'))
            ->post(route('data-siswa.generate-all'))
            ->assertForbidden();
    }

    public function test_import_csv_memetakan_kolom_ke_database(): void
    {
        $csv = implode("\n", [
            'NISN,Nama Lengkap,Peran/Penghargaan,Tanggal',
            '"0012345678","Budi Santoso","Juara 1 Lomba Keterampilan","15/01/2026"',
            '"0098765432","Siti Aminah","Harapan 2","2026-02-10"',
            '"","","Baris Kosong",""',
        ]);

        $response = $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ]);

        $response->assertRedirect(route('data-siswa.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, DataSiswa::count());

        $budi = DataSiswa::query()->where('nisn', '0012345678')->first();
        $this->assertNotNull($budi);
        $this->assertSame('Budi Santoso', $budi->nama);
        $this->assertSame('Juara 1 Lomba Keterampilan', $budi->peran);
        $this->assertSame('2026-01-15', $budi->tanggal?->toDateString());
        $this->assertSame(DataSiswa::STATUS_PENDING, $budi->status_sertifikat);

        $siti = DataSiswa::query()->where('nisn', '0098765432')->first();
        $this->assertNotNull($siti);
        $this->assertSame('Siti Aminah', $siti->nama);
        $this->assertSame('Harapan 2', $siti->peran);
        $this->assertSame('2026-02-10', $siti->tanggal?->toDateString());
    }

    public function test_import_xlsx_memetakan_kolom_ke_database(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['NISN', 'Nama Lengkap', 'Peran/Penghargaan', 'Tanggal'],
            ['0055512345', 'Andi Wijaya', 'Peserta Terbaik', '2026-03-01'],
        ], null, 'A1');

        $temporary = tempnam(sys_get_temp_dir(), 'siswa').'.xlsx';
        (new Xlsx($spreadsheet))->save($temporary);
        $content = file_get_contents($temporary);
        $spreadsheet->disconnectWorksheets();
        @unlink($temporary);

        $response = $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.xlsx', $content),
            ]);

        $response->assertRedirect(route('data-siswa.index'));

        $siswa = DataSiswa::query()->where('nisn', '0055512345')->first();

        $this->assertNotNull($siswa);
        $this->assertSame('Andi Wijaya', $siswa->nama);
        $this->assertSame('Peserta Terbaik', $siswa->peran);
        $this->assertSame('2026-03-01', $siswa->tanggal?->toDateString());
    }

    public function test_import_import_ulang_tanpa_mereset_status_sertifikat(): void
    {
        DataSiswa::create([
            'nisn' => '0012345678',
            'nama' => 'Nama Lama',
            'peran' => 'Peran Lama',
            'status_sertifikat' => DataSiswa::STATUS_SELESAI,
            'sertifikat_path' => 'certificates/lama.png',
        ]);

        $csv = implode("\n", [
            'NISN,Nama Lengkap,Peran/Penghargaan,Tanggal',
            '"0012345678","Budi Santoso","Juara 1","15/01/2026"',
        ]);

        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertSessionHas('success');

        $this->assertSame(1, DataSiswa::count());
        $this->assertDatabaseHas('data_siswas', [
            'nisn' => '0012345678',
            'nama' => 'Budi Santoso',
            'peran' => 'Juara 1',
            'status_sertifikat' => DataSiswa::STATUS_SELESAI,
            'sertifikat_path' => 'certificates/lama.png',
        ]);
    }

    public function test_import_menolak_ekstensi_tidak_dikenal(): void
    {
        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.txt', "NISN,Nama\n001,Budi"),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, DataSiswa::count());
    }

    public function test_import_menolak_file_tanpa_kolom_wajib(): void
    {
        $csv = implode("\n", [
            'Nama Lengkap,Email',
            '"Budi Santoso","budi@contoh.id"',
        ]);

        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, DataSiswa::count());
    }

    public function test_generate_sertifikat_per_baris_menjadi_selesai(): void
    {
        Storage::fake('public');

        $siswa = DataSiswa::create([
            'nisn' => '0012345678',
            'nama' => 'Budi Santoso',
            'peran' => 'Juara 1',
            'tanggal' => '2026-01-15',
            'status_sertifikat' => DataSiswa::STATUS_PENDING,
        ]);

        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.generate', $siswa))
            ->assertSessionHas('success');

        $siswa->refresh();

        $this->assertSame(DataSiswa::STATUS_SELESAI, $siswa->status_sertifikat);
        $this->assertNotNull($siswa->sertifikat_path);
        Storage::disk('public')->assertExists($siswa->sertifikat_path);
        $this->assertGreaterThan(10000, Storage::disk('public')->size($siswa->sertifikat_path));
    }

    public function test_generate_semua_sertifikat_menyelesaikan_seluruh_baris(): void
    {
        Storage::fake('public');

        foreach ([
            ['0011111111', 'Agus Priyono', 'Juara 1'],
            ['0022222222', 'Dewi Lestari', 'Juara 2'],
            ['0033333333', 'Rudi Hartono', 'Peserta'],
        ] as [$nisn, $nama, $peran]) {
            DataSiswa::create([
                'nisn' => $nisn,
                'nama' => $nama,
                'peran' => $peran,
                'tanggal' => '2026-04-01',
                'status_sertifikat' => DataSiswa::STATUS_PENDING,
            ]);
        }

        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.generate-all'))
            ->assertSessionHas('success');

        $this->assertSame(0, DataSiswa::where('status_sertifikat', '!=', DataSiswa::STATUS_SELESAI)->count());
        $this->assertSame(3, DataSiswa::whereNotNull('sertifikat_path')->count());

        foreach (DataSiswa::all() as $siswa) {
            Storage::disk('public')->assertExists($siswa->sertifikat_path);
        }
    }

    public function test_generate_semua_pada_tabel_kosong_memberi_peringatan(): void
    {
        $this->actingAs($this->userWithRole('humas'))
            ->post(route('data-siswa.generate-all'))
            ->assertSessionHas('warning');
    }

    public function test_hapus_data_siswa(): void
    {
        $siswa = DataSiswa::create([
            'nisn' => '0012345678',
            'nama' => 'Budi Santoso',
            'status_sertifikat' => DataSiswa::STATUS_PENDING,
        ]);

        $this->actingAs($this->userWithRole('humas'))
            ->delete(route('data-siswa.destroy', $siswa))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('data_siswas', ['id' => $siswa->id]);
    }
}
