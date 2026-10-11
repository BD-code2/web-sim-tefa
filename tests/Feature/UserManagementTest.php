<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_tu_menyimpan_email_domain_lama_dengan_domain_sekolah_yang_baru(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);

        $this->actingAs($tu)->post(route('pengaturan.users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'pengguna@smkn4tanjungpinang.sch.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'waka_humas',
            'is_active' => '1',
        ])->assertRedirect(route('pengaturan.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'pengguna@smkn4.com']);
        $this->assertDatabaseMissing('users', ['email' => 'pengguna@smkn4tanjungpinang.sch.id']);
    }

    public function test_tu_bisa_mengganti_password_pengguna_dari_password_default(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $pengguna = User::factory()->create([
            'email' => 'humas@smkn4.com',
            'password' => 'password123',
            'role' => 'humas',
        ]);

        $this->actingAs($tu)->put(route('pengaturan.users.update', $pengguna), [
            'name' => $pengguna->name,
            'email' => $pengguna->email,
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
            'role' => $pengguna->role,
            'is_active' => '1',
        ])->assertRedirect(route('pengaturan.users.index'));

        $this->assertTrue(Hash::check('sandi-baru-123', $pengguna->fresh()->password));
    }
}
