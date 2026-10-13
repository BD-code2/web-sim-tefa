<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const DISPOSISI_RECIPIENT_ROLES = [
        'waka_kurikulum',
        'waka_kesiswaan',
        'waka_sarpras',
        'waka_humas',
        'pembina_osis',
        'tu',
        'bendahara_bos',
        'bendahara_spp',
    ];

    public const AVAILABLE_ROLES = [
        'kepala_sekolah' => 'Kepala Sekolah',
        'kepala_tu' => 'Kepala Tata Usaha',
        'tu' => 'Tata Usaha / Koordinator TU',
        'humas' => 'Humas & Hubungan Industri',
        'waka_kurikulum' => 'Waka Kurikulum',
        'waka_kesiswaan' => 'Waka Kesiswaan',
        'waka_sarpras' => 'Waka Sarpras',
        'waka_humas' => 'Waka Humas',
        'pembina_osis' => 'Pembina OSIS',
        'bendahara_bos' => 'Bendahara Sekolah/BOS',
        'bendahara_spp' => 'Bendahara SPP',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
        'nisn',
        'kelas',
        'program_keahlian',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    public function isTU(): bool
    {
        return $this->role === 'tu';
    }

    public function isKepalaTU(): bool
    {
        return $this->role === 'kepala_tu';
    }

    public function isHumas(): bool
    {
        return in_array($this->role, ['humas', 'waka_humas'], true);
    }

    public function isWakaHumas(): bool
    {
        return $this->role === 'waka_humas';
    }

    public function isWakaKurikulum(): bool
    {
        return $this->role === 'waka_kurikulum';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($this->role, $roles);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'kepala_sekolah' => 'Kepala Sekolah',
            'kepala_tu' => 'Kepala Tata Usaha',
            'tu' => 'Tata Usaha / Koordinator TU',
            'humas' => 'Humas',
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'pembina_osis' => 'Pembina OSIS',
            'bendahara_bos' => 'Bendahara Sekolah/BOS',
            'bendahara_spp' => 'Bendahara SPP',
            default => ucfirst($this->role ?? ''),
        };
    }

    // Relationships
    public function suratMasuks(): HasMany
    {
        return $this->hasMany(SuratMasuk::class, 'created_by');
    }

    public function suratKeluars(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'created_by');
    }

    public function disposisiDiterima(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'kepada_user_id');
    }

    public function disposisiDikirim(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'dari_user_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'created_by');
    }
}
