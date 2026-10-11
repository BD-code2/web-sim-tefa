<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodePkl extends Model
{
    protected $table = 'periode_pkls';

    protected $fillable = ['nama', 'agenda', 'tanggal_mulai', 'tanggal_selesai', 'aktif', 'created_by'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dataSiswas(): HasMany
    {
        return $this->hasMany(DataSiswa::class, 'periode_pkl_id');
    }

    public function sertifikats(): HasMany
    {
        return $this->hasMany(SertifikatSiswa::class, 'periode_pkl_id');
    }
}
