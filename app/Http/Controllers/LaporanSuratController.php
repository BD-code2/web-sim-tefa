<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanSuratController extends Controller
{
    private const JENIS = ['surat_masuk', 'surat_keluar', 'surat_dinas', 'disposisi'];

    public function index(Request $request)
    {
        $filter = $this->filter($request);
        $laporan = $this->dataLaporan($filter);

        return view('laporan-surat.index', compact('filter', 'laporan'));
    }

    public function cetak(Request $request)
    {
        $filter = $this->filter($request);
        $laporan = $this->dataLaporan($filter);

        ini_set('memory_limit', '256M');

        return Pdf::loadView('laporan-surat.pdf', compact('filter', 'laporan'))
            ->setPaper('a4', 'landscape')
            ->stream('laporan-surat-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function filter(Request $request): array
    {
        $validated = $request->validate([
            'jenis' => ['nullable', 'array'],
            'jenis.*' => ['string', 'in:'.implode(',', self::JENIS)],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        return [
            'jenis' => $validated['jenis'] ?? self::JENIS,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
        ];
    }

    private function dataLaporan(array $filter): array
    {
        $dipilih = $filter['jenis'];

        return [
            'surat_masuk' => in_array('surat_masuk', $dipilih, true)
                ? $this->rentangTanggal(SuratMasuk::query(), 'tanggal_diterima', $filter)
                    ->orderByDesc('tanggal_diterima')->orderByDesc('id')->get()
                : collect(),
            'surat_keluar' => in_array('surat_keluar', $dipilih, true)
                ? $this->rentangTanggal(SuratKeluar::query(), 'tgl_pelaksanaan', $filter)
                    ->orderByDesc('tgl_pelaksanaan')->orderByDesc('id')->get()
                : collect(),
            'surat_dinas' => in_array('surat_dinas', $dipilih, true)
                ? $this->rentangTanggal(SuratDinas::query(), 'tgl_ditetapkan', $filter)
                    ->orderByDesc('tgl_ditetapkan')->orderByDesc('id')->get()
                : collect(),
            'disposisi' => in_array('disposisi', $dipilih, true)
                ? $this->rentangTanggal(
                    Disposisi::with(['suratMasuk', 'dariUser', 'kepadaUser']),
                    'created_at',
                    $filter
                )->orderByDesc('created_at')->orderByDesc('id')->get()
                : collect(),
        ];
    }

    private function rentangTanggal(Builder $query, string $kolom, array $filter): Builder
    {
        return $query
            ->when($filter['tanggal_mulai'], fn (Builder $q, string $tanggal) => $q->whereDate($kolom, '>=', $tanggal))
            ->when($filter['tanggal_selesai'], fn (Builder $q, string $tanggal) => $q->whereDate($kolom, '<=', $tanggal));
    }
}
