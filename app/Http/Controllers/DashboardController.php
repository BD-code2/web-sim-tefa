<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\KerjaSamaIndustri;
use App\Models\SertifikatSiswa;
use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $aksesSemuaSurat = $user->isTU() || $user->isKepalaSekolah();
        $aksesDisposisi = ! $user->isSiswa();

        $suratMasukQuery = SuratMasuk::query();
        if (! $aksesSemuaSurat) {
            $suratMasukQuery->whereHas('disposisis', fn ($query) => $query->where('kepada_user_id', $user->id));
        }

        $disposisiQuery = Disposisi::query();
        if (! $user->isTU() && ! $user->isKepalaSekolah()) {
            $disposisiQuery->where('kepada_user_id', $user->id);
        }

        // Statistik persuratan tampil untuk setiap role dan mengikuti hak aksesnya.
        $stats = [
            'total_surat_masuk' => $aksesDisposisi ? (clone $suratMasukQuery)->count() : 0,
            'surat_masuk_baru' => $aksesDisposisi ? (clone $suratMasukQuery)->where('status', 'diterima')->count() : 0,
            'total_surat_keluar' => $aksesSemuaSurat ? SuratKeluar::count() : 0,
            'surat_keluar_menunggu' => $aksesSemuaSurat ? SuratKeluar::where('status', 'menunggu_persetujuan')->count() : 0,
            'total_surat_dinas' => $aksesSemuaSurat ? SuratDinas::count() : 0,
            'surat_dinas_menunggu' => $aksesSemuaSurat ? SuratDinas::where('status', 'menunggu_persetujuan')->count() : 0,
            'total_disposisi' => $aksesDisposisi ? (clone $disposisiQuery)->count() : 0,
            'disposisi_menunggu' => $aksesDisposisi ? (clone $disposisiQuery)->where('status', 'menunggu')->count() : 0,
            'total_kerja_sama' => KerjaSamaIndustri::count(),
            'kerja_sama_aktif' => KerjaSamaIndustri::where('status', 'aktif')->count(),
            'total_sertifikat' => SertifikatSiswa::count(),
        ];

        if ($user->isKepalaTU()) {
            $stats['total_surat_keluar'] = SuratKeluar::where('status', 'menunggu_persetujuan')
                ->whereNull('diperiksa_kepala_tu_at')
                ->count();
            $stats['total_surat_dinas'] = SuratDinas::where('status', 'menunggu_persetujuan')
                ->whereNull('diperiksa_kepala_tu_at')
                ->count();
        }

        // Disposisi menunggu untuk user yang login
        $disposisiMenungguCount = Disposisi::where('kepada_user_id', $user->id)
            ->where('status', 'menunggu')
            ->count();

        // Surat Masuk Terbaru
        $suratMasukTerbaru = SuratMasuk::with('creator')->latest()->limit(5)->get();

        // Surat Keluar Terbaru
        $suratKeluarTerbaru = SuratKeluar::with('creator', 'approver')->latest()->limit(5)->get();

        // Disposisi Aktif untuk user
        $disposisiSaya = Disposisi::with('suratMasuk', 'dariUser')
            ->where('kepada_user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'disposisiMenungguCount',
            'suratMasukTerbaru',
            'suratKeluarTerbaru',
            'disposisiSaya'
        ));
    }
}
