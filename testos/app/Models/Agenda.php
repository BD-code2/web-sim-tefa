<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'agendas';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu',
        'lokasi',
        'tipe',
        'is_penting',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_penting' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for agendas happening today.
     */
    public function scopeHariIni(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereDate('tanggal_mulai', '<=', $today)
              ->where(function ($sub) use ($today) {
                  $sub->whereDate('tanggal_selesai', '>=', $today)
                      ->orWhereNull('tanggal_selesai');
              });
        })->orWhere(function ($q) use ($today) {
            $q->whereDate('tanggal_mulai', $today);
        });
    }

    /**
     * Scope for upcoming agendas.
     */
    public function scopeMendatang(Builder $query): Builder
    {
        return $query->whereDate('tanggal_mulai', '>', now()->toDateString())
                     ->orderBy('tanggal_mulai', 'asc');
    }
}
