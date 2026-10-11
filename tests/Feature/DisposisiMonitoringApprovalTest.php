<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiMonitoringApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_sekolah_dapat_menolak_surat_yang_diteruskan_tu_dan_tu_melihat_statusnya(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepalaSekolah = User::factory()->create(['role' => 'kepala_sekolah']);
        $surat = $this->buatSuratDiteruskan($tu, 'TEST/TOLAK/001');

        $this->actingAs($kepalaSekolah)
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertSee('TEST/TOLAK/001')
            ->assertSee('Isi lembar disposisi')
            ->assertSee('Tolak');

        $this->actingAs($tu)
            ->post(route('disposisi.surat-masuk.tolak', $surat))
            ->assertForbidden();

        $this->assertSame('pending', $surat->fresh()->status_persetujuan);

        $this->actingAs($kepalaSekolah)
            ->post(route('disposisi.surat-masuk.tolak', $surat))
            ->assertRedirect(route('disposisi.antrian'));

        $this->assertDatabaseHas('surat_masuks', [
            'id' => $surat->id,
            'status_persetujuan' => 'tidak_setuju',
        ]);
        $this->assertSame(0, Disposisi::where('surat_masuk_id', $surat->id)->count());

        $this->actingAs($tu)
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertSee('TEST/TOLAK/001')
            ->assertSee('Ditolak');

        $this->actingAs($kepalaSekolah)
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertDontSee('TEST/TOLAK/001');

        $this->actingAs($kepalaSekolah)
            ->get(route('disposisi.lembar.create', $surat))
            ->assertStatus(409);
    }

    public function test_keputusan_setuju_menerbitkan_disposisi_dan_penerima_dapat_menolak_lalu_mengarsipkan_tugas(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepalaSekolah = User::factory()->create(['role' => 'kepala_sekolah']);
        $penerima = User::factory()->create(['role' => 'waka_kurikulum']);
        $surat = $this->buatSuratDiteruskan($tu, 'TEST/SETUJU/001');

        $this->actingAs($kepalaSekolah)
            ->post(route('disposisi.store', $surat), [
                'tujuan_disposisi' => ['waka_kurikulum'],
                'instruksi_tambahan' => 'Mohon ditindaklanjuti.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('disposisi.antrian'));

        $this->assertSame('setuju', $surat->fresh()->status_persetujuan);
        $disposisi = Disposisi::where('surat_masuk_id', $surat->id)->sole();
        $this->assertSame($penerima->id, $disposisi->kepada_user_id);
        $this->assertSame('pending', $disposisi->status_persetujuan);

        $this->actingAs($kepalaSekolah)
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertDontSee('TEST/SETUJU/001');

        $this->actingAs($penerima)
            ->post(route('disposisi.persetujuan', $disposisi), ['persetujuan' => 'tidak_setuju'])
            ->assertRedirect();

        $this->assertSame('tidak_setuju', $disposisi->fresh()->status_persetujuan);
        $this->assertSame('setuju', $surat->fresh()->status_persetujuan);

        $this->actingAs($penerima)
            ->get(route('disposisi.antrian'))
            ->assertOk()
            ->assertSee('Ditolak');

        $this->actingAs($penerima)
            ->patch(route('disposisi.status', $disposisi), ['status' => 'selesai'])
            ->assertRedirect();

        $this->assertSame('selesai', $disposisi->fresh()->status);
        $this->actingAs($tu)
            ->get(route('disposisi.arsip'))
            ->assertOk()
            ->assertSee('TEST/SETUJU/001')
            ->assertSee('Ditolak');
    }

    private function buatSuratDiteruskan(User $tu, string $noSurat): SuratMasuk
    {
        return SuratMasuk::create([
            'no_surat' => $noSurat,
            'tanggal_surat' => now()->toDateString(),
            'tanggal_diterima' => now()->toDateString(),
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Permintaan disposisi',
            'created_by' => $tu->id,
            'diteruskan_ke_kepala_sekolah_at' => now(),
            'diteruskan_ke_kepala_sekolah_oleh' => $tu->id,
        ]);
    }
}
