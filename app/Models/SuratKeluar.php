<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_keluars';

    // Surat Undangan / Surat Umum.
    // Detail SK & SPT pindah ke App\Models\SuratDinas (detail_json).
    protected $fillable = [
        'jenis_surat',
        'no_surat',
        'tujuan',
        'perihal',
        'tgl_pelaksanaan',
        'waktu',
        'tempat',
        'acara',
        'isi_surat',
        'detail_json',
        'lampiran_path',
        'referensi_surat_masuk_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'catatan_revisi',
        'diperiksa_kepala_tu_at',
        'diperiksa_kepala_tu_oleh',
        'diteruskan_ke_kepala_sekolah_at',
        'diteruskan_ke_kepala_sekolah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'detail_json' => 'array',
            'tgl_pelaksanaan' => 'date',
            'approved_at' => 'datetime',
            'diperiksa_kepala_tu_at' => 'datetime',
            'diteruskan_ke_kepala_sekolah_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (SuratKeluar $model) {
            if ($model->status === 'disetujui' && empty($model->no_surat)) {
                $month = now()->format('m');
                $year = now()->format('Y');
                $prefix = match ($model->jenis_surat) {
                    'spt' => "421.5.1/SMKN4/{$month}/{$year}/",
                    'sk' => "421.5/SMKN4/{$month}/{$year}/",
                    'mutasi' => "421.5.2/SMKN4/{$month}/{$year}/",
                    default => "421.5/SMKN4/{$month}/{$year}/",
                };

                $model->no_surat = static::nomorBerikutnya($model->jenis_surat);

                if (empty($model->approved_at)) {
                    $model->approved_at = now();
                }
            }
        });
    }

    public static function nomorBerikutnya(?string $jenis): string
    {
        $month = now()->format('m');
        $year = now()->format('Y');
        $prefix = match ($jenis) {
            'spt' => "421.5.1/SMKN4/{$month}/{$year}/",
            'sk' => "421.5/SMKN4/{$month}/{$year}/",
            'mutasi' => "421.5.2/SMKN4/{$month}/{$year}/",
            default => "421.5/SMKN4/{$month}/{$year}/",
        };
        $nomorTerakhir = static::withTrashed()
            ->where('no_surat', 'like', $prefix.'%')
            ->pluck('no_surat')
            ->map(fn ($nomor) => preg_match('/\/(\d+)$/', $nomor, $cocok) ? (int) $cocok[1] : 0)
            ->max() ?? 0;

        return $prefix.sprintf('%04d', $nomorTerakhir + 1);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function referensiSuratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'referensi_surat_masuk_id');
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'referensi_surat_masuk_id');
    }

    // Helper methods
    public function getNomorSuratDisplayAttribute(): ?string
    {
        return $this->no_surat;
    }
}
