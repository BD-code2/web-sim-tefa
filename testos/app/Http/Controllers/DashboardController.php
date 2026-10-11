<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KerjaSamaIndustri;
use App\Models\Disposisi;
use App\Models\SertifikatSiswa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Statistik umum
        $stats = [
            'total_surat_masuk' => SuratMasuk::count(),
            'surat_masuk_baru' => SuratMasuk::where('status', 'diterima')->count(),
            'total_surat_keluar' => SuratKeluar::count(),
            'surat_keluar_menunggu' => SuratKeluar::where('status', 'menunggu_persetujuan')->count(),
            'total_kerja_sama' => KerjaSamaIndustri::count(),
            'kerja_sama_aktif' => KerjaSamaIndustri::where('status', 'aktif')->count(),
            'total_sertifikat' => SertifikatSiswa::count(),
        ];

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
