<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KerjaSamaIndustri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kerja_sama_industris';

    protected $fillable = [
        'nama_industri',
        'alamat',
        'kontak',
        'email',
        'program_keahlian',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'dokumen_mou_path',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sertifikatSiswas(): HasMany
    {
        return $this->hasMany(SertifikatSiswa::class, 'kerja_sama_industri_id');
    }

    public function sertifikats(): HasMany
    {
        return $this->hasMany(SertifikatSiswa::class, 'kerja_sama_industri_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'kerja_sama_industri_id');
    }

    /**
     * Determine auto status based on MoU start and end dates.
     */
    public function getStatusAutoAttribute(): string
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_berakhir) {
            return $this->status ?? 'aktif';
        }

        $today = now()->startOfDay();
        $mulai = $this->tanggal_mulai->copy()->startOfDay();
        $berakhir = $this->tanggal_berakhir->copy()->endOfDay();

        if ($today->gt($berakhir)) {
            return 'berakhir';
        }

        if ($today->lt($mulai)) {
            return 'akan_datang';
        }

        return 'aktif';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_auto) {
            'aktif' => 'Aktif',
            'berakhir' => 'Berakhir',
            'akan_datang' => 'Akan Datang',
            default => ucfirst($this->status ?? 'Aktif'),
        };
    }
}
