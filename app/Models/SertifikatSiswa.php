<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SertifikatSiswa extends Model
{
    use HasFactory;

    protected $table = 'sertifikat_siswas';

    protected $fillable = [
        'siswa_user_id',
        'nama_siswa',
        'nisn',
        'kelas',
        'periode_pkl_id',
        'program_keahlian',
        'kerja_sama_industri_id',
        'nama_industri_pkl',
        'tanggal_mulai_pkl',
        'tanggal_selesai_pkl',
        'nilai',
        'predikat',
        'no_sertifikat',
        'template_id',
        'status',
        'diterbitkan_oleh',
        'diterbitkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai_pkl' => 'date',
            'tanggal_selesai_pkl' => 'date',
            'diterbitkan_pada' => 'datetime',
            'nilai' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (SertifikatSiswa $model) {
            if (empty($model->no_sertifikat)) {
                $year = now()->format('Y');
                $prefix = "SERT/{$year}/";

                $seq = static::where('no_sertifikat', 'LIKE', $prefix.'%')
                    ->pluck('no_sertifikat')
                    ->map(fn ($number) => preg_match('/\/(\d+)$/', $number, $matches) ? (int) $matches[1] : 0)
                    ->max() + 1;

                $model->no_sertifikat = sprintf('SERT/%s/%04d', $year, $seq);
            }
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_user_id');
    }

    public function kerjaSamaIndustri(): BelongsTo
    {
        return $this->belongsTo(KerjaSamaIndustri::class, 'kerja_sama_industri_id');
    }

    public function industri(): BelongsTo
    {
        return $this->belongsTo(KerjaSamaIndustri::class, 'kerja_sama_industri_id');
    }

    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    public function templateSertifikat(): BelongsTo
    {
        return $this->belongsTo(TemplateSertifikat::class, 'template_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSertifikat::class, 'template_id');
    }
}
