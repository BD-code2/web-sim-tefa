<?php

namespace App\Http\Controllers;

use App\Models\DataSiswa;
use App\Models\KerjaSamaIndustri;
use App\Models\SertifikatSiswa;
use App\Services\PklCertificatePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SertifikatController extends Controller
{
    public function index(Request $request)
    {
        $query = SertifikatSiswa::with(['industri', 'penerbit'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_siswa', 'like', "%{$s}%")
                    ->orWhere('nisn', 'like', "%{$s}%")
                    ->orWhere('no_sertifikat', 'like', "%{$s}%")
                    ->orWhere('kelas', 'like', "%{$s}%")
                    ->orWhere('nama_industri_pkl', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sertifikats = $query->paginate(10)->withQueryString();

        return view('sertifikat.index', compact('sertifikats'));
    }

    public function show(Request $request, SertifikatSiswa $sertifikat)
    {
        $sertifikat->load(['industri', 'penerbit']);

        $daftarParams = $request->only(['kelas', 'search', 'page']);
        $daftarUrl = $sertifikat->periode_pkl_id
            ? route('data-siswa.agenda.show', ['periode' => $sertifikat->periode_pkl_id] + $daftarParams)
            : route('sertifikat.index', $daftarParams);

        return view('sertifikat.show', compact('sertifikat', 'daftarUrl', 'daftarParams'));
    }

    public function updateNilai(Request $request, SertifikatSiswa $sertifikat)
    {
        $validated = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'predikat' => ['required', 'string', 'max:50'],
            'tempat_tgl_lahir' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($sertifikat, $validated) {
            $sertifikat->update($validated);
            if (array_key_exists('tempat_tgl_lahir', $validated) && $sertifikat->periode_pkl_id) {
                DataSiswa::where('nisn', $sertifikat->nisn)
                    ->where('periode_pkl_id', $sertifikat->periode_pkl_id)
                    ->update(['tempat_tgl_lahir' => $validated['tempat_tgl_lahir']]);
            }
        });

        return redirect()->route('sertifikat.show', ['sertifikat' => $sertifikat->id] + $request->only(['kelas', 'search', 'page']))
            ->with('success', 'Data sertifikat berhasil disimpan. Data lahir dan predikat akan tampil pada sertifikat PDF.');
    }

    public function edit(SertifikatSiswa $sertifikat)
    {
        $mitras = KerjaSamaIndustri::all();

        return view('sertifikat.edit', compact('sertifikat', 'mitras'));
    }

    public function update(Request $request, SertifikatSiswa $sertifikat)
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|string|max:30',
            'kelas' => 'required|string|max:50',
            'program_keahlian' => 'required|string|max:100',
            'kerja_sama_industri_id' => 'required|exists:kerja_sama_industris,id',
            'tanggal_mulai_pkl' => 'required|date',
            'tanggal_selesai_pkl' => 'required|date|after:tanggal_mulai_pkl',
            'nilai' => 'nullable|numeric|min:0|max:100',
            'predikat' => 'nullable|string|max:50',
            'status' => 'required|in:draft,terverifikasi,diterbitkan',
        ]);

        $sertifikat->update($validated);

        return redirect()->route('sertifikat.show', $sertifikat)->with('success', 'Data sertifikat PKL berhasil diperbarui.');
    }

    // Verifikasi dan Terbitkan Sertifikat
    public function updateStatus(Request $request, SertifikatSiswa $sertifikat)
    {
        $request->validate([
            'status' => 'required|in:terverifikasi,diterbitkan',
        ]);

        abort_unless(
            ($request->status === 'terverifikasi' && $sertifikat->status === 'draft')
                || ($request->status === 'diterbitkan' && $sertifikat->status === 'terverifikasi'),
            409,
            'Tahap PKL harus diselesaikan secara berurutan.'
        );

        $data = ['status' => $request->status];

        if ($request->status === 'diterbitkan') {
            if (! $sertifikat->no_sertifikat) {
                $prefix = 'SERT/'.now()->format('Y').'/';
                $nomorBerikutnya = SertifikatSiswa::where('no_sertifikat', 'like', $prefix.'%')
                    ->pluck('no_sertifikat')
                    ->map(fn ($number) => preg_match('/\/(\d+)$/', $number, $matches) ? (int) $matches[1] : 0)
                    ->max() + 1;
                $data['no_sertifikat'] = $prefix.sprintf('%04d', $nomorBerikutnya);
            }
            $data['diterbitkan_oleh'] = $request->user()->id;
            $data['diterbitkan_pada'] = now();
        }

        $sertifikat->update($data);

        return back()->with('success', 'Status sertifikat PKL berhasil diubah menjadi '.ucfirst($request->status));
    }

    public function destroy(SertifikatSiswa $sertifikat)
    {
        $sertifikat->delete();

        return redirect()->route('sertifikat.index')->with('success', 'Data sertifikat berhasil dihapus.');
    }

    // Cetak PDF Sertifikat PKL
    public function cetakPdf(SertifikatSiswa $sertifikat)
    {
        $sertifikat->load(['industri', 'penerbit']);

        ini_set('memory_limit', '256M');

        $safeNisn = str_replace(['/', '\\'], '-', $sertifikat->nisn ?? 'sertifikat');
        $filename = "sertifikat-pkl-{$safeNisn}.pdf";

        $pdf = app(PklCertificatePdf::class)->create([$sertifikat]);

        return $pdf->stream($filename);
    }
}
