<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiRecipientRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_membuat_semua_akun_penerima_sesuai_format_disposisi(): void
    {
        $this->seed(DatabaseSeeder::class);

        $roles = [
            'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas',
            'pembina_osis', 'tu', 'bendahara_bos', 'bendahara_spp',
        ];

        foreach ($roles as $role) {
            $this->assertDatabaseHas('users', ['role' => $role, 'is_active' => 1]);
        }

        $this->assertDatabaseMissing('users', ['role' => 'admin', 'is_active' => 1]);
        $this->assertDatabaseMissing('users', ['role' => 'guru', 'is_active' => 1]);
        $this->assertDatabaseHas('users', ['email' => 'tu@smkn4tanjungpinang.sch.id', 'role' => 'tu', 'is_active' => 1]);
    }

    public function test_semua_penerima_bisa_membuka_antrean_dan_tu_bisa_kelola_pengguna(): void
    {
        foreach (User::DISPOSISI_RECIPIENT_ROLES as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('disposisi.antrian'))->assertOk();
        }

        $tu = User::factory()->create(['role' => 'tu']);
        $this->actingAs($tu)->get(route('pengaturan.users.index'))->assertOk();
        $this->actingAs($tu)->get(route('pengaturan.users.create'))
            ->assertOk()
            ->assertSee('Bendahara Sekolah/BOS')
            ->assertDontSee('Administrator SIM')
            ->assertDontSee('Guru / Staf Pendidik');
    }

    public function test_disposisi_mencatat_role_penerima_dari_akun_yang_dipilih(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $penerima = User::factory()->create(['role' => 'waka_kesiswaan']);
        $suratMasuk = SuratMasuk::create([
            'no_surat' => 'TEST/001',
            'tanggal_surat' => '2026-09-30',
            'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Undangan rapat',
            'created_by' => $tu->id,
        ]);

        $this->actingAs($tu)->post(route('disposisi.store'), [
            'surat_masuk_id' => $suratMasuk->id,
            'kepada_user_id' => $penerima->id,
            'instruksi' => 'Tindak lanjuti.',
        ])->assertRedirect();

        $this->assertDatabaseHas('disposisis', [
            'surat_masuk_id' => $suratMasuk->id,
            'kepada_user_id' => $penerima->id,
            'tujuan_role' => 'waka_kesiswaan',
        ]);

        $this->actingAs($penerima)
            ->get(route('surat-masuk.show', $suratMasuk))
            ->assertOk()
            ->assertSee('Undangan rapat');

        $suratLain = SuratMasuk::create([
            'no_surat' => 'TEST/002',
            'tanggal_surat' => '2026-09-30',
            'tanggal_diterima' => '2026-09-30',
            'pengirim' => 'Dinas Pendidikan',
            'perihal' => 'Surat bukan untuk penerima ini',
            'created_by' => $tu->id,
        ]);
        $this->actingAs($penerima)->get(route('surat-masuk.show', $suratLain))->assertForbidden();

        $orangLuar = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($tu)->post(route('disposisi.store'), [
            'surat_masuk_id' => $suratMasuk->id,
            'kepada_user_id' => $orangLuar->id,
            'instruksi' => 'Tidak boleh diteruskan.',
        ])->assertStatus(403);

        $this->assertSame(1, Disposisi::count());
    }
}
