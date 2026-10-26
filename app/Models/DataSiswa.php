<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSiswa extends Model
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_GAGAL = 'Gagal';

    protected $table = 'data_siswas';

    protected $fillable = [
        'nisn',
        'nama',
        'tempat_tgl_lahir',
        'kelas',
        'industri',
        'periode_pkl_id',
        'peran',
        'tanggal',
        'status_sertifikat',
        'sertifikat_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function isSelesai(): bool
    {
        return $this->status_sertifikat === self::STATUS_SELESAI;
    }
}
