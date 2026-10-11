<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarPdfTest extends TestCase
{
    use RefreshDatabase;

    private function userTu(): User
    {
        return User::factory()->create(['role' => 'tu']);
    }

    private function buatUndangan(): SuratKeluar
    {
        $user = $this->userTu();

        return SuratKeluar::create([
            'no_surat' => '421.5/SMKN4/09/2026/0001',
            'tujuan' => 'Kepala Dinas Pendidikan Provinsi Kepulauan Riau',
            'perihal' => 'Undangan Rapat Koordinasi UKK SMK 2026',
            'tgl_pelaksanaan' => '2026-10-15',
            'waktu' => '08.00 WIB s.d. selesai',
            'tempat' => 'Aula SMK Negeri 4 Tanjungpinang',
            'acara' => 'Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau',
            'isi_surat' => 'Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau.',
            'status' => 'disetujui',
            'created_by' => $user->id,
        ]);
    }

    public function test_template_pdf_menampilkan_bagian_wajib(): void
    {
        $html = view('surat-keluar.pdf', ['suratKeluar' => $this->buatUndangan(), 'kepsek' => null])->render();

        $this->assertStringContainsString('SMK Negeri 4 Tanjungpinang', $html);
        $this->assertStringContainsString('421.5/SMKN4/09/2026/0001', $html);
        $this->assertStringContainsString('Undangan Rapat Koordinasi UKK SMK 2026', $html);
        $this->assertStringContainsString('Kepala Dinas Pendidikan Provinsi Kepulauan Riau', $html);
        $this->assertStringContainsString('Rapat Koordinasi UKK SMK se-Provinsi Kepulauan Riau', $html);
        $this->assertStringContainsString('Aula SMK Negeri 4 Tanjungpinang', $html);
        $this->assertStringContainsString('08.00 WIB s.d. selesai', $html);
        $this->assertStringContainsString('Kepala SMK Negeri 4 Tanjungpinang', $html);
    }

    public function test_cetak_pdf_menghasilkan_file_pdf(): void
    {
        $surat = $this->buatUndangan();

        $response = $this->actingAs($this->userTu())->get(route('surat-keluar.pdf', $surat));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_cetak_pdf_menggunakan_nomor_generik_bila_kosong(): void
    {
        $user = $this->userTu();
        $surat = SuratKeluar::create([
            'tujuan' => 'Kepala Sekolah',
            'perihal' => 'Undangan Tanpa Nomor',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('surat-keluar.pdf', $surat));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_cetak_pdf_ditolak_untuk_role_yang_tidak_berwenang(): void
    {
        $surat = $this->buatUndangan();
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('surat-keluar.pdf', $surat))
            ->assertForbidden();
    }
}
