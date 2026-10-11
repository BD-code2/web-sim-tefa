<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'disposisis';

    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'kepada_user_id',
        'instruksi',
        'catatan',
        'status',
        'status_persetujuan',
        'tujuan_role',
        'tujuan_disposisi',
        'harapan_instruksi',
        'sifat_surat',
        'penerima_lain',
        'instruksi_tambahan',
        'selesai_at',
    ];

    protected $casts = [
        'tujuan_disposisi' => 'array',
        'harapan_instruksi' => 'array',
        'sifat_surat' => 'array',
        'selesai_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->status_persetujuan === 'setuju';
    }

    public function isRejected(): bool
    {
        return $this->status_persetujuan === 'tidak_setuju';
    }

    public function isPending(): bool
    {
        return $this->status_persetujuan === 'pending' || empty($this->status_persetujuan);
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function dariUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function kepadaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepada_user_id');
    }
}
