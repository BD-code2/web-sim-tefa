<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratMasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_masuks';

    protected $fillable = [
        'no_agenda',
        'nomor_urut',
        'diteruskan_ke_kepala_sekolah_at',
        'diteruskan_ke_kepala_sekolah_oleh',
        'no_surat',
        'tanggal_surat',
        'tanggal_diterima',
        'pengirim',
        'perihal',
        'disposisi_tindak_lanjut',
        'keterangan',
        'isi_ringkas',
        'sifat',
        'lampiran_path',
        'status',
        'catatan_revisi',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_diterima' => 'date',
            'diteruskan_ke_kepala_sekolah_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (SuratMasuk $model) {
            if (empty($model->nomor_urut)) {
                $model->nomor_urut = (static::withTrashed()->max('nomor_urut') ?? 0) + 1;
            }

            if (empty($model->no_agenda)) {
                $month = now()->format('m');
                $year = now()->format('Y');
                $prefix = "AGD/{$month}/{$year}/";

                $latest = static::withTrashed()
                    ->where('no_agenda', 'LIKE', $prefix.'%')
                    ->orderByDesc('id')
                    ->first();

                $seq = 1;
                if ($latest && preg_match('/\/(\d+)$/', $latest->no_agenda, $matches)) {
                    $seq = intval($matches[1]) + 1;
                }

                $model->no_agenda = sprintf('AGD/%s/%s/%04d', $month, $year, $seq);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disposisis(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    public function suratKeluars(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'referensi_surat_masuk_id');
    }
}
