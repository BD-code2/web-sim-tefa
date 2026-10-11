<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratDinas extends Model
{
    protected $table = 'surat_dinas';

    protected $fillable = [
        'jenis_surat',
        'no_surat',
        'tentang_judul',
        'tgl_ditetapkan',
        'status',
        'detail_json',
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
            'tgl_ditetapkan' => 'date',
            'approved_at' => 'datetime',
            'diperiksa_kepala_tu_at' => 'datetime',
            'diteruskan_ke_kepala_sekolah_at' => 'datetime',
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

    /**
     * Label penomoran diktum MEMUTUSKAN (0-based): KESATU, KEDUA, KETIGA, ...
     * Di atas 20 jatuh ke format "KE-{n}".
     */
    public static function labelDiktum(int $index): string
    {
        static $satuan = [
            'SATU', 'DUA', 'TIGA', 'EMPAT', 'LIMA', 'ENAM',
            'TUJUH', 'DELAPAN', 'SEMBILAN', 'SEPULUH', 'SEBELAS',
        ];

        $n = $index + 1;

        if ($n >= 1 && $n <= 12) {
            return 'KE'.$satuan[$n - 1];
        }

        if ($n <= 19) {
            return 'KE'.$satuan[$n - 11].'BELAS';
        }

        if ($n === 20) {
            return 'KEDUAPULUH';
        }

        return 'KE-'.$n;
    }
}
