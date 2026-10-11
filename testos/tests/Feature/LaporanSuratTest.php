<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanSuratTest extends TestCase
{
    use RefreshDatabase;

    public function test_tu_dapat_memfilter_dan_mencetak_laporan_gabungan(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $suratMasuk = SuratMasuk::create([
            'no_surat' => 'SM/001',
            'tanggal_surat' => '2026-09-10',
            'tanggal_diterima' => '2026-09-11',
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Undangan Rapat',
            'created_by' => $tu->id,
        ]);
        SuratKeluar::create([
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Balasan Rapat',
            'tgl_pelaksanaan' => '2026-09-12',
            'created_by' => $tu->id,
        ]);
        SuratDinas::create([
            'jenis_surat' => 'SK',
            'tentang_judul' => 'Tim Pelaksana Rapat',
            'tgl_ditetapkan' => '2026-09-13',
            'created_by' => $tu->id,
        ]);
        Disposisi::create([
            'surat_masuk_id' => $suratMasuk->id,
            'dari_user_id' => $kepsek->id,
            'kepada_user_id' => $tu->id,
            'instruksi' => 'Tindak lanjuti.',
            'created_at' => '2026-09-14 08:00:00',
        ]);

        $filter = [
            'jenis' => ['surat_masuk', 'surat_keluar', 'surat_dinas', 'disposisi'],
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
        ];

        $this->actingAs($tu)->get(route('laporan-surat.index', $filter))
            ->assertOk()
            ->assertSee('Undangan Rapat')
            ->assertSee('Balasan Rapat')
            ->assertSee('Tim Pelaksana Rapat')
            ->assertSee('Cetak Semua');

        $this->get(route('laporan-surat.cetak', $filter))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($kepsek)->get(route('laporan-surat.index'))->assertForbidden();
    }
}
