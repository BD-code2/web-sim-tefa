<?php

namespace Tests\Feature;

use App\Models\PeriodePkl;
use App\Models\SertifikatSiswa;
use App\Models\User;
use App\Services\PklCertificateAssets;
use App\Services\PklCertificatePdf;
use Dompdf\Canvas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PklCertificatePdfTest extends TestCase
{
    use RefreshDatabase;

    private function certificate(array $attributes = []): SertifikatSiswa
    {
        $certificate = new SertifikatSiswa($attributes + [
            'nama_siswa' => 'SYARIFAH NAZURA NAZALYA',
            'nisn' => '3684',
            'kelas' => 'XII RPL 1',
            'program_keahlian' => 'Rekayasa Perangkat Lunak',
            'nama_industri_pkl' => 'KANWIL KEMENAG PROVINSI KEPULAUAN RIAU',
            'tanggal_mulai_pkl' => '2026-01-01',
            'tanggal_selesai_pkl' => '2026-06-30',
            'predikat' => 'SANGAT BAIK',
            'no_sertifikat' => 'SERT/2026/0001',
            'status' => 'diterbitkan',
        ]);
        $certificate->setRelation('industri', null);

        return $certificate;
    }

    private function pageCount(string $binary): int
    {
        preg_match('/\/Type\s*\/Pages\b(?:(?!endobj).)*?\/Count\s+(\d+)/s', $binary, $matches);

        return (int) ($matches[1] ?? 0);
    }

    public function test_sertifikat_satuan_satu_halaman_dengan_enam_aset_tertanam(): void
    {
        $document = app(PklCertificatePdf::class)->create([$this->certificate()]);
        $canvas = $document->getDomPDF()->getCanvas();
        $binary = $document->output(['compress' => false]);

        $this->assertSame(1, $canvas->get_page_count());
        $this->assertGreaterThan($canvas->get_height(), $canvas->get_width());
        $this->assertStringStartsWith('%PDF-', $binary);
        $this->assertStringContainsString('SYARIFAH NAZURA NAZALYA', $binary);
        $this->assertStringContainsString('SERT/2026/0001', $binary);
        $this->assertGreaterThanOrEqual(6, preg_match_all('/\/Subtype\s*\/Image\b/', $binary));
    }

    public function test_satu_kelas_satu_halaman_per_siswa_dan_gambar_digunakan_ulang(): void
    {
        $builder = app(PklCertificatePdf::class);
        $single = $builder->create([$this->certificate()])->output();
        $certificates = collect(range(1, 35))->map(fn ($index) => $this->certificate([
            'nama_siswa' => sprintf('SISWA PKL %03d', $index),
            'no_sertifikat' => sprintf('SERT/2026/%04d', $index),
        ]));
        $document = $builder->create($certificates);
        $binary = $document->output(['compress' => false]);

        $this->assertSame(35, $document->getDomPDF()->getCanvas()->get_page_count());
        foreach ($certificates as $certificate) {
            $this->assertSame(1, substr_count($binary, $certificate->nama_siswa));
        }
        $this->assertLessThan(strlen($single) + 512_000, strlen($document->output()));

        // Dokumen berikutnya harus terpisah dari canvas unduhan sebelumnya.
        $next = $builder->create([$this->certificate(['nama_siswa' => 'SISWA BERIKUTNYA'])]);
        $this->assertSame(1, $next->getDomPDF()->getCanvas()->get_page_count());
        $this->assertStringNotContainsString('SISWA PKL 001', $next->output(['compress' => false]));
    }

    public function test_nama_dan_industri_panjang_tetap_di_satu_halaman(): void
    {
        $certificate = $this->certificate([
            'nama_siswa' => 'MUHAMMAD ABDURRAHMAN ZAINAL ABIDIN PRATAMA SYARIFUDDIN NUR CAHAYA TANJUNGPINANG',
            'nama_industri_pkl' => str_repeat('INDUSTRI TEKNOLOGI INFORMASI DAN KOMUNIKASI ', 4),
        ]);
        $document = app(PklCertificatePdf::class)->create([$certificate]);
        $binary = $document->output(['compress' => false]);

        $this->assertSame(1, $document->getDomPDF()->getCanvas()->get_page_count());
        $this->assertStringContainsString('MUHAMMAD ABDURRAHMAN', $binary);
        $this->assertStringContainsString('TANJUNGPINANG', $binary);
    }

    public function test_tu_dapat_mengunduh_pdf_satuan_dan_humas_pdf_kelas(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $humas = User::factory()->create(['role' => 'humas']);
        $periode = PeriodePkl::create([
            'nama' => 'PKL Tahun 2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
        ]);
        $certificate = $this->certificate(['periode_pkl_id' => $periode->id]);
        $certificate->save();
        $this->certificate(['periode_pkl_id' => $periode->id, 'nisn' => '3685', 'no_sertifikat' => 'SERT/2026/0002'])->save();
        $this->certificate(['periode_pkl_id' => $periode->id, 'status' => 'draft', 'no_sertifikat' => 'SERT/2026/0003'])->save();
        $this->certificate(['periode_pkl_id' => $periode->id, 'kelas' => 'XII RPL 2', 'no_sertifikat' => 'SERT/2026/0004'])->save();

        $single = $this->actingAs($tu)->get(route('sertifikat.pdf', $certificate));
        $single->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(1, $this->pageCount($single->getContent()));

        $class = $this->actingAs($humas)->get(route('data-siswa.pdf-kelas', [
            'kelas' => 'XII RPL 1', 'periode_pkl_id' => $periode->id,
        ]));
        $class->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $class->headers->get('Content-Disposition'));
        $this->assertSame(2, $this->pageCount($class->getContent()));
    }

    public function test_kelas_tanpa_sertifikat_terbit_tidak_menghasilkan_pdf_kosong(): void
    {
        $user = User::factory()->create(['role' => 'tu']);
        $periode = PeriodePkl::create([
            'nama' => 'Belum Terbit', 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-06-30',
        ]);
        $this->actingAs($user)->get(route('data-siswa.pdf-kelas', [
            'kelas' => 'XII RPL 1', 'periode_pkl_id' => $periode->id,
        ]))->assertNotFound();
    }

    public function test_data_lahir_dicetak_dan_blok_tanda_tangan_sejajar(): void
    {
        $certificate = $this->certificate(['tempat_tgl_lahir' => 'Kijang / 08 Oktober 2007']);
        $builder = app(PklCertificatePdf::class);
        $document = $builder->create([$certificate]);
        $canvas = \Mockery::mock(Canvas::class)->shouldIgnoreMissing();
        $canvas->shouldReceive('get_width')->andReturn(702);
        $canvas->shouldReceive('get_height')->andReturn(491);
        $canvas->shouldReceive('get_text_width')->andReturnUsing(fn ($text, $font, $size) => strlen($text) * $size / 2);
        $canvas->shouldReceive('get_font_height')->andReturn(14);
        $positions = [];
        $canvas->shouldReceive('text')->andReturnUsing(function ($x, $y, $text, $font, $size) use (&$positions) {
            $positions[$text] = ['center' => $x + strlen($text) * $size / 4, 'y' => $y];
        });
        $draw = new \ReflectionMethod(PklCertificatePdf::class, 'draw');
        $draw->invoke($builder, $canvas, $document->getDomPDF()->getFontMetrics(), $certificate, app(PklCertificateAssets::class)->prepare());

        $this->assertArrayHasKey('Kijang / 08 Oktober 2007', $positions);
        $this->assertEqualsWithDelta(351, $positions['Sertifikat']['center'], .01);
        $this->assertEqualsWithDelta(177, $positions[config('pkl.certificate.principal_name')]['center'], .01);
        $this->assertEqualsWithDelta(177, $positions['NIP '.config('pkl.certificate.principal_nip')]['center'], .01);
        $this->assertEquals($positions[config('pkl.certificate.principal_name')]['y'], $positions['Drs. H. EDI BATARA, M.A.P.']['y']);
    }
}
