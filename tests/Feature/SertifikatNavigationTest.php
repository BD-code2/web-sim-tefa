<?php

namespace Tests\Feature;

use App\Models\DataSiswa;
use App\Models\PeriodePkl;
use App\Models\SertifikatSiswa;
use App\Models\User;
use App\Services\StudentImportParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Tests\TestCase;

class SertifikatNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function certificate(): SertifikatSiswa
    {
        $user = User::factory()->create(['role' => 'tu']);
        $this->actingAs($user);
        $periode = PeriodePkl::create([
            'nama' => 'Agenda PKL 2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'aktif' => true,
            'created_by' => $user->id,
        ]);
        DataSiswa::create([
            'nisn' => '12345', 'nama' => 'Siswa Uji', 'kelas' => 'XII RPL 1',
            'industri' => 'Mitra dari Excel', 'periode_pkl_id' => $periode->id,
        ]);

        return SertifikatSiswa::create([
            'nama_siswa' => 'Siswa Uji', 'nisn' => '12345', 'kelas' => 'XII RPL 1',
            'program_keahlian' => 'RPL', 'nama_industri_pkl' => 'Mitra dari Excel',
            'periode_pkl_id' => $periode->id, 'tanggal_mulai_pkl' => '2026-01-01',
            'tanggal_selesai_pkl' => '2026-06-30', 'status' => 'draft',
        ]);
    }

    public function test_detail_kembali_ke_agenda_dengan_filter_dan_industri_tetap_terlihat(): void
    {
        $certificate = $this->certificate();
        $filters = ['kelas' => 'XII RPL 1', 'search' => 'Siswa', 'page' => 1];
        $detail = route('sertifikat.show', ['sertifikat' => $certificate->id] + $filters);
        $agenda = route('data-siswa.agenda.show', ['periode' => $certificate->periode_pkl_id] + $filters);

        $this->get($agenda)->assertOk()->assertSee($detail)->assertSee('Mitra dari Excel')->assertSee('Nilai &amp; Predikat', false);
        $this->get($detail)->assertOk()->assertSee($agenda)->assertSee('Mitra dari Excel');
        $this->get(route('sertifikat.index'))->assertOk()->assertSee('Mitra dari Excel');
        $this->assertDatabaseHas('sertifikat_siswas', ['id' => $certificate->id, 'nama_industri_pkl' => 'Mitra dari Excel']);
    }

    public function test_nilai_dan_predikat_disimpan_tanpa_mengubah_industri_atau_status(): void
    {
        $certificate = $this->certificate();
        $params = ['sertifikat' => $certificate->id, 'kelas' => 'XII RPL 1', 'page' => 2];
        $this->patch(route('sertifikat.nilai', $params), [
            'nilai' => 92.5, 'predikat' => 'Sangat Baik', 'status' => 'diterbitkan',
            'nama_industri_pkl' => 'Tidak boleh berubah',
        ])->assertSessionHasNoErrors()->assertRedirect(route('sertifikat.show', $params));

        $this->assertDatabaseHas('sertifikat_siswas', [
            'id' => $certificate->id, 'nilai' => 92.5, 'predikat' => 'Sangat Baik',
            'status' => 'draft', 'nama_industri_pkl' => 'Mitra dari Excel',
        ]);
        $this->get(route('data-siswa.agenda.show', $certificate->periode_pkl_id))
            ->assertOk()->assertSee('92.50')->assertSee('Sangat Baik');

        $this->patch(route('sertifikat.nilai', $certificate), ['nilai' => 101, 'predikat' => ''])
            ->assertSessionHasErrors(['nilai', 'predikat']);
        $this->patch(route('sertifikat.nilai', $certificate), ['nilai' => 0, 'predikat' => 'Kurang'])
            ->assertSessionHasNoErrors();
    }

    public function test_sertifikat_tanpa_agenda_memiliki_tautan_kembali_yang_valid(): void
    {
        $certificate = $this->certificate();
        $certificate->update(['periode_pkl_id' => null]);
        $this->get(route('sertifikat.show', $certificate))->assertOk()->assertSee(route('sertifikat.index'));
        $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))
            ->patch(route('sertifikat.nilai', $certificate), ['nilai' => 90, 'predikat' => 'Baik'])
            ->assertForbidden();
    }

    public function test_impor_data_lahir_memperbarui_sertifikat_dan_tidak_menimpa_tanggal_kegiatan(): void
    {
        $certificate = $this->certificate();
        $csv = "NISN,Nama,Kelas,Industri,Tempat Lahir,Tanggal Lahir,Tanggal\n12345,Siswa Uji,XII RPL 1,Mitra dari Excel,Kijang,08/10/2007,01/06/2026";
        $this->post(route('data-siswa.import.store'), [
            'periode_pkl_id' => $certificate->periode_pkl_id,
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ])->assertSessionHasNoErrors();
        $this->assertSame('Kijang / 08 Oktober 2007', $certificate->fresh()->tempat_tgl_lahir);
        $student = DataSiswa::where('nisn', '12345')->firstOrFail();
        $this->assertSame('Kijang / 08 Oktober 2007', $student->tempat_tgl_lahir);
        $this->assertSame('2026-06-01', $student->tanggal->format('Y-m-d'));

        $csv = "NISN,Nama,Kelas,Industri,Tempat / Tgl. Lahir\n12345,Siswa Uji,XII RPL 1,Mitra dari Excel,Kijang / 09 Oktober 2007";
        $this->post(route('data-siswa.import.store'), [
            'periode_pkl_id' => $certificate->periode_pkl_id,
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ])->assertSessionHasNoErrors();
        $this->assertSame('Kijang / 09 Oktober 2007', $certificate->fresh()->tempat_tgl_lahir);

        $this->patch(route('sertifikat.nilai', $certificate), [
            'nilai' => 90, 'predikat' => 'Sangat Baik', 'tempat_tgl_lahir' => 'Batam / 10 Oktober 2007',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Batam / 10 Oktober 2007', $student->fresh()->tempat_tgl_lahir);
        $this->post(route('data-siswa.proses-siswa', $student), ['program_keahlian' => 'RPL'])->assertSessionHasNoErrors();
        $this->assertSame('Batam / 10 Oktober 2007', $certificate->fresh()->tempat_tgl_lahir);
    }

    public function test_parser_mendukung_tanggal_lahir_serial_excel(): void
    {
        $file = UploadedFile::fake()->createWithContent('siswa.csv', "NIS,Nama,Kelas,Industri,Tanggal_Lahir\n12345,Siswa,XII RPL 1,Mitra,39363");
        $rows = app(StudentImportParser::class)->parse($file->getRealPath(), 'csv');
        $expected = Date::excelToDateTimeObject(39363)->format('Y-m-d');
        $this->assertSame(Carbon::parse($expected)->locale('id')->translatedFormat('d F Y'), $rows[0]['tempat_tgl_lahir']);
        $this->assertNull($rows[0]['tanggal']);
    }
}
