<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_persuratan_tampil_sesuai_akses_role(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $penerima = User::factory()->create(['role' => 'waka_kurikulum']);
        $kepalaTu = User::factory()->create(['role' => 'kepala_tu']);

        $suratMasuk = SuratMasuk::create([
            'no_surat' => 'SM/DASHBOARD/001',
            'tanggal_surat' => '2026-09-30',
            'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Data Card Dashboard',
            'created_by' => $tu->id,
        ]);
        SuratKeluar::create([
            'tujuan' => 'Dinas Pendidikan',
            'perihal' => 'Surat Keluar Dashboard',
            'created_by' => $tu->id,
        ]);
        SuratDinas::create([
            'jenis_surat' => 'SK',
            'tentang_judul' => 'Surat Dinas Dashboard',
            'created_by' => $tu->id,
        ]);
        Disposisi::create([
            'surat_masuk_id' => $suratMasuk->id,
            'dari_user_id' => $kepsek->id,
            'kepada_user_id' => $penerima->id,
            'instruksi' => 'Tindak lanjuti.',
        ]);

        foreach ([$tu, $kepsek, $kepalaTu] as $user) {
            $this->actingAs($user)->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Surat masuk')
                ->assertSee('Surat keluar')
                ->assertSee('Surat dinas')
                ->assertSee('Disposisi');
        }

        foreach (['humas', 'waka_humas', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'pembina_osis', 'bendahara_bos', 'bendahara_spp'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Surat masuk')
                ->assertSee('Disposisi')
                ->assertDontSee('Surat keluar')
                ->assertDontSee('Surat dinas')
                ->assertDontSee('Tidak tersedia untuk role ini');
        }

        $this->actingAs($penerima)->get(route('dashboard'))
            ->assertSee('1 tugas masih menunggu');

    }
}
