<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_surat_keluar_mengalir_dari_tu_ke_kepala_tu_kembali_ke_tu_lalu_ke_kepala_sekolah(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepalaTu = User::factory()->create(['role' => 'kepala_tu']);
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);

        $this->actingAs($tu)->post(route('surat-keluar.store'), [
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Undangan rapat koordinasi',
            'action' => 'ajukan',
        ])->assertSessionHasNoErrors();

        $surat = SuratKeluar::sole();
        $this->assertNull($surat->diperiksa_kepala_tu_at);
        $this->assertNull($surat->diteruskan_ke_kepala_sekolah_at);

        $this->actingAs($kepalaTu)->get(route('surat-keluar.pemeriksaan-kepala-tu'))
            ->assertOk()
            ->assertSee('Dinas Pendidikan');

        $this->actingAs($kepsek)->get(route('disposisi.antrian', ['tab' => 'persetujuan']))
            ->assertOk()
            ->assertDontSee('Dinas Pendidikan');

        $this->actingAs($kepalaTu)->post(route('surat-keluar.periksa-kepala-tu', $surat), [
            'keputusan' => 'periksa',
        ])->assertRedirect(route('surat-keluar.pemeriksaan-kepala-tu'));

        $surat->refresh();
        $this->assertNotNull($surat->diperiksa_kepala_tu_at);
        $this->assertSame($kepalaTu->id, $surat->diperiksa_kepala_tu_oleh);

        $this->actingAs($tu)->get(route('surat-keluar.show', $surat))
            ->assertOk()
            ->assertSee('Teruskan ke Kepala Sekolah');

        $this->actingAs($tu)->post(route('surat-keluar.teruskan-kepala-sekolah', $surat))
            ->assertRedirect(route('surat-keluar.show', $surat));

        $this->actingAs($kepsek)->get(route('disposisi.antrian', ['tab' => 'persetujuan']))
            ->assertOk()
            ->assertSee('Dinas Pendidikan');

        $this->actingAs($kepsek)->post(route('disposisi.surat-keluar.setujui', $surat))
            ->assertRedirect(route('disposisi.antrian', ['tab' => 'persetujuan']));

        $this->assertSame('disetujui', $surat->fresh()->status);
    }

    public function test_kepala_tu_dapat_mengembalikan_surat_untuk_revisi_dan_pengajuan_ulang_mengulang_alur(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepalaTu = User::factory()->create(['role' => 'kepala_tu']);
        $surat = SuratKeluar::create([
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Surat perlu diperbaiki',
            'status' => 'menunggu_persetujuan',
            'created_by' => $tu->id,
        ]);

        $this->actingAs($kepalaTu)->post(route('surat-keluar.periksa-kepala-tu', $surat), [
            'keputusan' => 'revisi',
            'catatan_revisi' => 'Lengkapi tanggal kegiatan.',
        ])->assertRedirect(route('surat-keluar.pemeriksaan-kepala-tu'));

        $this->assertSame('ditolak', $surat->fresh()->status);

        $this->actingAs($tu)->put(route('surat-keluar.update', $surat), [
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Surat sudah diperbaiki',
            'tgl_pelaksanaan' => '2026-10-10',
            'action' => 'ajukan',
        ])->assertSessionHasNoErrors();

        $surat->refresh();
        $this->assertSame('menunggu_persetujuan', $surat->status);
        $this->assertNull($surat->diperiksa_kepala_tu_at);
        $this->assertNull($surat->diteruskan_ke_kepala_sekolah_at);
    }
}
