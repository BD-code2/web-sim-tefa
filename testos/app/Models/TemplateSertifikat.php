<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSertifikat extends Model
{
    use HasFactory;

    protected $table = 'template_sertifikats';

    protected $fillable = [
        'nama',
        'deskripsi',
        'file_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sertifikats(): HasMany
    {
        return $this->hasMany(SertifikatSiswa::class, 'template_id');
    }

    public function sertifikatSiswas(): HasMany
    {
        return $this->hasMany(SertifikatSiswa::class, 'template_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
