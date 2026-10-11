<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratMasuk::with(['creator', 'disposisis.kepadaUser'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_surat', 'like', "%{$s}%")
                    ->orWhere('no_agenda', 'like', "%{$s}%")
                    ->orWhere('pengirim', 'like', "%{$s}%")
                    ->orWhere('perihal', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sifat')) {
            $query->where('sifat', $request->sifat);
        }

        $suratMasuk = $query->paginate(10)->withQueryString();

        return view('surat-masuk.index', compact('suratMasuk'));
    }

    public function create()
    {
        return view('surat-masuk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'required|date',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'disposisi_tindak_lanjut' => 'nullable|string|max:2000',
            'keterangan' => 'nullable|string|max:2000',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $lampiranPath = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('surat-masuk', 'public')
            : null;

        $surat = SuratMasuk::create([
            'no_surat' => $validated['no_surat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_diterima' => $validated['tanggal_diterima'],
            'pengirim' => $validated['pengirim'],
            'perihal' => $validated['perihal'],
            'disposisi_tindak_lanjut' => $validated['disposisi_tindak_lanjut'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'lampiran_path' => $lampiranPath,
            'status' => 'diterima',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('surat-masuk.show', $surat)->with('success', 'Surat masuk berhasil dicatat dengan Nomor Urut: '.$surat->nomor_urut);
    }

    public function teruskanKeKepalaSekolah(SuratMasuk $suratMasuk)
    {
        abort_unless(auth()->user()->isTU(), 403);

        if ($suratMasuk->diteruskan_ke_kepala_sekolah_at) {
            return back()->with('info', 'Surat ini sudah diteruskan ke Kepala Sekolah.');
        }

        $suratMasuk->update([
            'diteruskan_ke_kepala_sekolah_at' => now(),
            'diteruskan_ke_kepala_sekolah_oleh' => auth()->id(),
            'status' => 'diproses',
        ]);

        return back()->with('success', 'Surat masuk berhasil diteruskan kepada Kepala Sekolah.');
    }

    public function unduhLampiran(SuratMasuk $suratMasuk)
    {
        abort_unless(
            auth()->user()->isTU()
                || auth()->user()->isKepalaSekolah()
                || $suratMasuk->disposisis()->where('kepada_user_id', auth()->id())->exists(),
            403
        );

        abort_unless(
            $suratMasuk->lampiran_path && Storage::disk('public')->exists($suratMasuk->lampiran_path),
            404,
            'Berkas surat masuk belum tersedia.'
        );

        return Storage::disk('public')->download($suratMasuk->lampiran_path, basename($suratMasuk->lampiran_path));
    }

    public function show(SuratMasuk $suratMasuk)
    {
        abort_unless(
            auth()->user()->isTU()
                || auth()->user()->isKepalaSekolah()
                || $suratMasuk->disposisis()->where('kepada_user_id', auth()->id())->exists(),
            403
        );

        $suratMasuk->load(['creator', 'disposisis.dariUser', 'disposisis.kepadaUser', 'suratKeluars']);

        return view('surat-masuk.show', compact('suratMasuk'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        return view('surat-masuk.edit', compact('suratMasuk'));
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $validated = $request->validate([
            'no_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_diterima' => 'required|date',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'isi_ringkas' => 'nullable|string',
            'sifat' => 'required|in:biasa,segera,rahasia',
            'status' => 'required|in:diterima,didisposisi,diproses,selesai,diarsipkan',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        if ($request->hasFile('lampiran')) {
            if ($suratMasuk->lampiran_path && Storage::disk('public')->exists($suratMasuk->lampiran_path)) {
                Storage::disk('public')->delete($suratMasuk->lampiran_path);
            }
            $validated['lampiran_path'] = $request->file('lampiran')->store('surat-masuk', 'public');
        }

        $suratMasuk->update($validated);

        return redirect()->route('surat-masuk.show', $suratMasuk)->with('success', 'Data surat masuk berhasil diperbarui.');
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        $suratMasuk->delete();

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dihapus.');
    }
}
