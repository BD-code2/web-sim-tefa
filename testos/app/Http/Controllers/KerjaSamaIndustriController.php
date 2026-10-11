<?php

namespace App\Http\Controllers;

use App\Models\KerjaSamaIndustri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KerjaSamaIndustriController extends Controller
{
    public function index(Request $request)
    {
        $query = KerjaSamaIndustri::with(['creator', 'approver'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_industri', 'like', "%{$s}%")
                    ->orWhere('alamat', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        $industris = $query->paginate(10)->withQueryString();

        // Kartu statistik persis sesuai mockup
        $stats = [
            'total' => KerjaSamaIndustri::count(),
            'aktif' => KerjaSamaIndustri::where('status', 'aktif')->where('approval_status', 'approved')->count(),
            'akan_berakhir' => KerjaSamaIndustri::where('status', 'akan_berakhir')->count(),
            'berakhir' => KerjaSamaIndustri::where('status', 'berakhir')->count(),
        ];

        return view('kerja-sama.index', compact('industris', 'stats'));
    }

    public function create()
    {
        return view('kerja-sama.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_industri' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after:tanggal_mulai',
            'status' => 'required|in:aktif,akan_berakhir,berakhir',
            'dokumen_mou' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'catatan' => 'nullable|string',
        ]);

        $dokumenMouPath = null;
        if ($request->hasFile('dokumen_mou')) {
            $dokumenMouPath = $request->file('dokumen_mou')->store('mou', 'public');
        }

        $mitra = KerjaSamaIndustri::create([
            'nama_industri' => $validated['nama_industri'],
            'alamat' => $validated['alamat'],
            'kontak' => $validated['kontak'] ?? null,
            'email' => $validated['email'] ?? null,
            // Kolom lama masih wajib di database, tetapi tidak lagi digunakan di aplikasi.
            'program_keahlian' => '-',
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_berakhir' => $validated['tanggal_berakhir'],
            'status' => $validated['status'],
            'approval_status' => $request->user()->isTU() ? 'pending' : 'approved',
            'approved_by' => $request->user()->isTU() ? null : $request->user()->id,
            'approved_at' => $request->user()->isTU() ? null : now(),
            'dokumen_mou_path' => $dokumenMouPath,
            'catatan' => $validated['catatan'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $message = ! $request->user()->isTU()
            ? "Mitra industri {$mitra->nama_industri} berhasil ditambahkan dan langsung aktif."
            : "Mitra industri {$mitra->nama_industri} tersimpan dan menunggu persetujuan Waka Humas.";

        return redirect()->route('kerja-sama.index')->with('success', $message);
    }

    public function show(KerjaSamaIndustri $kerjaSama)
    {
        $kerjaSama->load(['creator', 'sertifikatSiswas']);

        return view('kerja-sama.show', compact('kerjaSama'));
    }

    public function edit(KerjaSamaIndustri $kerjaSama)
    {
        return view('kerja-sama.edit', compact('kerjaSama'));
    }

    public function update(Request $request, KerjaSamaIndustri $kerjaSama)
    {
        $validated = $request->validate([
            'nama_industri' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after:tanggal_mulai',
            'status' => 'required|in:aktif,akan_berakhir,berakhir',
            'dokumen_mou' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('dokumen_mou')) {
            if ($kerjaSama->dokumen_mou_path && Storage::disk('public')->exists($kerjaSama->dokumen_mou_path)) {
                Storage::disk('public')->delete($kerjaSama->dokumen_mou_path);
            }
            $validated['dokumen_mou_path'] = $request->file('dokumen_mou')->store('mou', 'public');
        }

        if (! $request->user()->isTU()) {
            $validated['approval_status'] = 'approved';
            $validated['approved_by'] = $request->user()->id;
            $validated['approved_at'] = $kerjaSama->approved_at ?? now();
        } elseif ($request->user()->isTU()) {
            $validated['approval_status'] = 'pending';
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
        }

        $kerjaSama->update($validated);

        $message = $request->user()->isTU()
            ? 'Data mitra industri diperbarui dan menunggu persetujuan Waka Humas.'
            : 'Data mitra industri berhasil diperbarui.';

        return redirect()->route('kerja-sama.index')->with('success', $message);
    }

    public function approve(Request $request, KerjaSamaIndustri $kerjaSama)
    {
        abort_unless($request->user()->isWakaHumas(), 403);

        abort_unless($kerjaSama->approval_status === 'pending', 409, 'Mitra ini tidak sedang menunggu persetujuan.');

        $kerjaSama->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('kerja-sama.index')->with('success', "Mitra industri {$kerjaSama->nama_industri} telah disetujui.");
    }

    public function destroy(KerjaSamaIndustri $kerjaSama)
    {
        $kerjaSama->delete();

        return redirect()->route('kerja-sama.index')->with('success', 'Data kerja sama industri berhasil dihapus.');
    }
}
