<?php

namespace App\Http\Controllers;

use App\Models\KerjaSamaIndustri;
use App\Models\SertifikatSiswa;
use App\Services\PklCertificatePdf;
use Illuminate\Http\Request;

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
                    ->orWhere('kelas', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sertifikats = $query->paginate(10)->withQueryString();

        return view('sertifikat.index', compact('sertifikats'));
    }

    public function show(SertifikatSiswa $sertifikat)
    {
        $sertifikat->load(['industri', 'penerbit']);

        return view('sertifikat.show', compact('sertifikat'));
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

        $data = ['status' => $request->status];

        if ($request->status === 'diterbitkan') {
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
