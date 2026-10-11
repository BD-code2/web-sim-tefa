<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\KerjaSamaIndustri;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifikasis = [];

        // Notifikasi berdasarkan peran
        if ($user->isKepalaSekolah() || $user->isTU()) {
            // Surat Keluar menunggu persetujuan
            $suratKeluarMenunggu = SuratKeluar::where('status', 'menunggu_persetujuan')->latest()->get();
            foreach ($suratKeluarMenunggu as $sk) {
                $notifikasis[] = [
                    'tipe' => 'persetujuan',
                    'judul' => 'Pengajuan Persetujuan Surat Keluar',
                    'pesan' => "Surat ke {$sk->tujuan} perihal \"{$sk->perihal}\" menunggu persetujuan Anda.",
                    'link' => route('disposisi.antrian', ['tab' => 'persetujuan']),
                    'waktu' => $sk->updated_at,
                    'badge' => 'Persetujuan',
                    'color' => 'amber',
                ];
            }

            // Surat Masuk baru diterima belum didisposisi
            $suratMasukBaru = SuratMasuk::where('status', 'diterima')->latest()->get();
            foreach ($suratMasukBaru as $sm) {
                $notifikasis[] = [
                    'tipe' => 'surat_masuk',
                    'judul' => 'Surat Masuk Baru Menunggu Disposisi',
                    'pesan' => "Dari {$sm->pengirim} perihal \"{$sm->perihal}\".",
                    'link' => route('disposisi.antrian', ['tab' => 'disposisi']),
                    'waktu' => $sm->created_at,
                    'badge' => 'Surat Masuk',
                    'color' => 'blue',
                ];
            }
        }

        // Disposisi menunggu tindak lanjut untuk user ini
        $disposisiMenunggu = Disposisi::with('suratMasuk')
            ->where('kepada_user_id', $user->id)
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        foreach ($disposisiMenunggu as $disp) {
            $notifikasis[] = [
                'tipe' => 'disposisi',
                'judul' => 'Disposisi Surat Masuk Baru',
                'pesan' => "Instruksi: {$disp->instruksi} (Surat: {$disp->suratMasuk->perihal})",
                'link' => route('surat-masuk.show', $disp->surat_masuk_id),
                'waktu' => $disp->created_at,
                'badge' => 'Disposisi',
                'color' => 'emerald',
            ];
        }

        // Notifikasi Kerja Sama Industri yang Akan Berakhir
        if ($user->isHumas() || $user->isTU() || $user->isKepalaSekolah()) {
            $mitraAkanBerakhir = KerjaSamaIndustri::where('status', 'akan_berakhir')->get();
            foreach ($mitraAkanBerakhir as $mitra) {
                $notifikasis[] = [
                    'tipe' => 'kontrak',
                    'judul' => 'Perhatian Masa Kontrak Kerja Sama',
                    'pesan' => "MOU dengan {$mitra->nama_industri} berakhir pada {$mitra->tanggal_berakhir->format('d/m/Y')}.",
                    'link' => route('kerja-sama.show', $mitra),
                    'waktu' => $mitra->updated_at,
                    'badge' => 'MoU Industri',
                    'color' => 'rose',
                ];
            }
        }

        // Sort by waktu descending
        usort($notifikasis, function ($a, $b) {
            return $b['waktu'] <=> $a['waktu'];
        });

        return view('notifikasi.index', compact('notifikasis'));
    }
}
