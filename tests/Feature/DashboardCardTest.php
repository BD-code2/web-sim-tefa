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

    public function test_card_persuratan_tampil_di_semua_role_dengan_data_sesuai_akses(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
        $penerima = User::factory()->create(['role' => 'waka_kurikulum']);
        $wakaSarpras = User::factory()->create(['role' => 'waka_sarpras']);

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

        foreach ([$tu, $kepsek, $penerima, $wakaSarpras] as $user) {
            $this->actingAs($user)->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Surat masuk')
                ->assertSee('Surat keluar')
                ->assertSee('Surat dinas')
                ->assertSee('Disposisi');
        }

        $this->actingAs($penerima)->get(route('dashboard'))
            ->assertSee('1 tugas masih menunggu')
            ->assertSee('Tidak tersedia untuk role ini');

    }
}
