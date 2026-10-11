<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratDinas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DisposisiController extends Controller
{
    // Pemantauan surat masuk: TU meneruskan ke Kepala Sekolah, lalu Kepala Sekolah mendisposisikan.
    public function antrian(Request $request)
    {
        $user = $request->user();
        $activeTab = $request->query('tab', 'disposisi'); // default tab: disposisi

        if ($user->isTU()) {
            $suratMasukBelumDiteruskan = SuratMasuk::whereNull('diteruskan_ke_kepala_sekolah_at')
                ->with('creator')
                ->latest('tanggal_diterima')
                ->paginate(10);
            $suratMasukSudahDiteruskan = SuratMasuk::whereNotNull('diteruskan_ke_kepala_sekolah_at')
                ->with('creator')
                ->withCount('disposisis')
                ->latest('diteruskan_ke_kepala_sekolah_at')
                ->limit(10)
                ->get();
            $disposisiSaya = Disposisi::with([
                'suratMasuk' => fn ($query) => $query->withTrashed(),
                'dariUser',
            ])
                ->where('kepada_user_id', $user->id)
                ->whereIn('status', ['menunggu', 'diproses'])
                ->latest()
                ->limit(10)
                ->get();

            return view('disposisi.pemantauan-tu', compact('suratMasukBelumDiteruskan', 'suratMasukSudahDiteruskan', 'disposisiSaya'));
        }

        if ($user->isKepalaSekolah()) {
            // Semua jenis surat keluar memakai satu alur persetujuan.
            $suratKeluarMenunggu = SuratKeluar::where('status', 'menunggu_persetujuan')
                ->with(['creator', 'referensiSuratMasuk'])
                ->latest()
                ->get();
            $suratDinasMenunggu = SuratDinas::where('status', 'menunggu_persetujuan')->with('creator')->latest()->get();

            // Surat Masuk menunggu disposisi (Tab 2)
            $suratBelumDisposisi = SuratMasuk::whereNotNull('diteruskan_ke_kepala_sekolah_at')
                ->whereDoesntHave('disposisis')
                ->with('creator')
                ->latest('diteruskan_ke_kepala_sekolah_at')
                ->get();

            // Riwayat disposisi
            $riwayatDisposisi = Disposisi::with('suratMasuk', 'kepadaUser')->latest()->paginate(10);

            return view('disposisi.antrian-kepsek', compact(
                'suratKeluarMenunggu',
                'suratDinasMenunggu',
                'suratBelumDisposisi',
                'riwayatDisposisi',
                'activeTab'
            ));
        } else {
            // Guru/TU/Humas melihat disposisi yang ditujukan ke mereka
            $disposisiSaya = Disposisi::with([
                'suratMasuk' => fn ($query) => $query->withTrashed(),
                'dariUser',
            ])
                ->where('kepada_user_id', $user->id)
                ->whereIn('status', ['menunggu', 'diproses'])
                ->latest()
                ->paginate(10);

            return view('disposisi.antrian-staff', compact('disposisiSaya'));
        }
    }

    public function arsip(Request $request)
    {
        $query = Disposisi::with([
            'suratMasuk' => fn ($query) => $query->withTrashed(),
            'kepadaUser',
            'dariUser',
        ])
            ->where('status', 'selesai');

        if (! $request->user()->isTU() && ! $request->user()->isKepalaSekolah()) {
            $query->where('kepada_user_id', $request->user()->id);
        }

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('instruksi', 'like', "%{$search}%")
                    ->orWhereHas('suratMasuk', function ($suratQuery) use ($search) {
                        $suratQuery->withTrashed()->where(function ($suratQuery) use ($search) {
                            $suratQuery->where('no_agenda', 'like', "%{$search}%")
                                ->orWhere('no_surat', 'like', "%{$search}%")
                                ->orWhere('perihal', 'like', "%{$search}%")
                                ->orWhere('pengirim', 'like', "%{$search}%");
                        });
                    })
                    ->orWhereHas('kepadaUser', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('penerima') && ($request->user()->isTU() || $request->user()->isKepalaSekolah())) {
            $query->whereHas('kepadaUser', fn ($userQuery) => $userQuery->where('role', $request->query('penerima')));
        }

        if ($request->filled('persetujuan')) {
            $query->where('status_persetujuan', $request->query('persetujuan'));
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('selesai_at', '>=', $request->query('tanggal_mulai'));
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('selesai_at', '<=', $request->query('tanggal_selesai'));
        }

        $arsipDisposisi = $query->orderByDesc('selesai_at')->latest()->paginate(15)->withQueryString();
        $recipientRoles = collect(User::DISPOSISI_RECIPIENT_ROLES)
            ->mapWithKeys(fn ($role) => [$role => User::AVAILABLE_ROLES[$role]]);

        return view('disposisi.arsip', compact('arsipDisposisi', 'recipientRoles'));
    }

    // Setujui Surat Keluar (dari Tab Persetujuan)
    public function setujuiSuratKeluar(Request $request, SuratKeluar $suratKeluar)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);

        $request->validate([
            'catatan_revisi' => 'nullable|string',
        ]);

        // Generate nomor surat jika belum ada
        if (! $suratKeluar->no_surat) {
            $suratKeluar->no_surat = SuratKeluar::nomorBerikutnya($suratKeluar->jenis_surat);
        }

        $suratKeluar->update([
            'status' => 'disetujui',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'catatan_revisi' => $request->catatan_revisi,
        ]);

        return redirect()->route('disposisi.antrian', ['tab' => 'persetujuan'])
            ->with('success', "Surat keluar telah disetujui dengan No. Surat: {$suratKeluar->no_surat}");
    }

    // Tolak / Minta Revisi Surat Keluar (dari Tab Persetujuan)
    public function revisiSuratKeluar(Request $request, SuratKeluar $suratKeluar)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);

        $request->validate([
            'catatan_revisi' => 'required|string|max:1000',
        ]);

        $suratKeluar->update([
            'status' => 'minta_revisi',
            'catatan_revisi' => $request->catatan_revisi,
        ]);

        return redirect()->route('disposisi.antrian', ['tab' => 'persetujuan'])
            ->with('warning', 'Surat keluar dikembalikan ke TU untuk revisi. Catatan: '.$request->catatan_revisi);
    }

    public function createLembar(SuratMasuk $suratMasuk)
    {
        abort_unless(auth()->user()->isKepalaSekolah(), 403);
        abort_unless($suratMasuk->diteruskan_ke_kepala_sekolah_at, 403, 'TU harus meneruskan surat ini terlebih dahulu.');

        $roleOptions = [
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'pembina_osis' => 'Pembina OSIS',
            'tu' => 'Koordinator TU',
            'bendahara_bos' => 'Bendahara Sekolah/BOS',
            'bendahara_spp' => 'Bendahara SPP',
        ];
        $activeRecipientCounts = User::where('is_active', true)
            ->whereIn('role', array_keys($roleOptions))
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('disposisi.create-lembar', compact('suratMasuk', 'roleOptions', 'activeRecipientCounts'));
    }

    public function store(Request $request, SuratMasuk $suratMasuk)
    {
        abort_unless($request->user()->isKepalaSekolah(), 403);

        $validated = $request->validate([
            'tujuan_disposisi' => 'required|array|min:1',
            'tujuan_disposisi.*' => 'required|in:waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,pembina_osis,tu,bendahara_bos,bendahara_spp',
            'sifat_surat' => 'nullable|array',
            'sifat_surat.*' => 'in:sangat_segera,segera,rahasia,biasa',
            'harapan_instruksi' => 'nullable|array',
            'harapan_instruksi.*' => 'in:Tanggapan dan saran,Proses lebih lanjut,Koordinasi/konfirmasi',
            'instruksi_tambahan' => 'nullable|string|max:2000',
            'penerima_lain' => 'nullable|required_if:ada_penerima_lain,1|string|max:255',
            'ada_penerima_lain' => 'nullable|boolean',
            'catatan' => 'nullable|string|max:2000',
        ]);

        abort_unless($suratMasuk->diteruskan_ke_kepala_sekolah_at, 403, 'TU harus meneruskan surat ini terlebih dahulu.');

        $roleLabels = [
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'pembina_osis' => 'Pembina OSIS',
            'tu' => 'Koordinator TU',
            'bendahara_bos' => 'Bendahara Sekolah/BOS',
            'bendahara_spp' => 'Bendahara SPP',
        ];
        $roleValues = array_values(array_unique($validated['tujuan_disposisi']));
        $recipientUsers = User::where('is_active', true)->whereIn('role', $roleValues)->get()->groupBy('role');
        $missingRoles = array_diff($roleValues, $recipientUsers->keys()->all());

        if ($missingRoles) {
            return back()->withInput()->withErrors([
                'tujuan_disposisi' => 'Akun penerima aktif belum tersedia untuk: '.collect($missingRoles)->map(fn ($role) => $roleLabels[$role])->implode(', '),
            ]);
        }

        $tujuanLabels = collect($roleValues)->map(fn ($role) => $roleLabels[$role])->values()->all();
        $harapan = $validated['harapan_instruksi'] ?? [];
        $instruksi = implode('; ', $harapan);
        if ($validated['instruksi_tambahan'] ?? false) {
            $instruksi = trim($instruksi.' '.$validated['instruksi_tambahan']);
        }
        if ($instruksi === '') {
            $instruksi = 'Mohon ditindaklanjuti sesuai arahan pada lembar disposisi.';
        }

        DB::transaction(function () use ($recipientUsers, $roleValues, $suratMasuk, $request, $validated, $tujuanLabels, $harapan, $instruksi) {
            foreach ($roleValues as $role) {
                foreach ($recipientUsers->get($role) as $penerima) {
                    Disposisi::create([
                        'surat_masuk_id' => $suratMasuk->id,
                        'dari_user_id' => $request->user()->id,
                        'kepada_user_id' => $penerima->id,
                        'tujuan_role' => $role,
                        'instruksi' => $instruksi,
                        'instruksi_tambahan' => $validated['instruksi_tambahan'] ?? null,
                        'catatan' => $validated['catatan'] ?? null,
                        'tujuan_disposisi' => $tujuanLabels,
                        'harapan_instruksi' => $harapan,
                        'sifat_surat' => $validated['sifat_surat'] ?? [],
                        'penerima_lain' => $validated['penerima_lain'] ?? null,
                        'status' => 'menunggu',
                        'status_persetujuan' => 'pending',
                    ]);
                }
            }

            $suratMasuk->update(['status' => 'didisposisi']);
        });

        return redirect()->route('disposisi.antrian')->with('success', 'Lembar disposisi berhasil dikirim kepada role yang dipilih.');
    }

    public function persetujuan(Request $request, Disposisi $disposisi)
    {
        abort_unless(
            $request->user()->id === $disposisi->kepada_user_id,
            403
        );

        $request->validate([
            'persetujuan' => 'required|in:setuju,tidak_setuju',
            'catatan' => 'nullable|string',
        ]);

        $disposisi->update([
            'status_persetujuan' => $request->persetujuan,
            'catatan' => $request->filled('catatan') ? $request->catatan : $disposisi->catatan,
            'status' => 'diproses',
            'selesai_at' => null,
        ]);
        if ($disposisi->suratMasuk) {
            $this->syncSuratMasukStatus($disposisi->suratMasuk);
        }

        $label = $request->persetujuan === 'setuju' ? 'disetujui' : 'tidak disetujui (ditolak)';

        return back()->with('success', "Disposisi telah {$label}.");
    }

    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        abort_unless(
            $request->user()->id === $disposisi->kepada_user_id,
            403
        );

        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai',
        ]);

        DB::transaction(function () use ($disposisi, $request) {
            $disposisi->update([
                'status' => $request->status,
                'selesai_at' => $request->status === 'selesai' ? now() : null,
            ]);

            $suratMasuk = $disposisi->suratMasuk;
            if (! $suratMasuk) {
                return;
            }

            $this->syncSuratMasukStatus($suratMasuk);
        });

        return back()->with('success', 'Status tindak lanjut disposisi berhasil diperbarui.');
    }

    public function destroy(Disposisi $disposisi)
    {
        abort_unless(auth()->user()->isKepalaSekolah(), 403);

        $disposisi->delete();

        return back()->with('success', 'Disposisi berhasil dihapus.');
    }

    public function show(Disposisi $disposisi)
    {
        abort_unless(
            auth()->id() === $disposisi->kepada_user_id || auth()->user()->isTU() || auth()->user()->isKepalaSekolah(),
            403
        );

        $disposisi->load(['suratMasuk', 'dariUser', 'kepadaUser']);

        return view('disposisi.show', compact('disposisi'));
    }

    public function unduhSuratMasuk(Disposisi $disposisi)
    {
        $this->authorizeFileDownload($disposisi);
        $disposisi->load(['suratMasuk' => fn ($query) => $query->withTrashed()]);

        $path = $disposisi->suratMasuk?->lampiran_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Berkas surat masuk belum tersedia.');

        return Storage::disk('public')->download($path, basename($path));
    }

    public function unduhLembarDisposisi(Disposisi $disposisi)
    {
        $this->authorizeFileDownload($disposisi);

        $disposisi->load(['suratMasuk' => fn ($query) => $query->withTrashed()->with('creator')]);
        $kepsek = User::where('role', 'kepala_sekolah')->where('is_active', true)->first();
        $kepsekData = [
            'nama' => $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
            'nip' => $kepsek?->nip ?? '19770421 200502 2 011',
            'pangkat' => $kepsek?->pangkat ?? 'Pembina Tk.I',
        ];

        $pdf = Pdf::loadView('disposisi.pdf', compact('disposisi', 'kepsekData'))
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'landscape');
        $safeNoAgenda = str_replace(['/', '\\'], '-', $disposisi->suratMasuk->no_agenda ?? 'disposisi');

        return $pdf->download("lembar-disposisi-{$safeNoAgenda}.pdf");
    }

    private function authorizeFileDownload(Disposisi $disposisi): void
    {
        abort_unless(
            auth()->id() === $disposisi->kepada_user_id || auth()->user()->isTU() || auth()->user()->isKepalaSekolah(),
            403
        );
    }

    private function syncSuratMasukStatus(SuratMasuk $suratMasuk): void
    {
        $statuses = $suratMasuk->disposisis()->pluck('status');
        $status = $statuses->isNotEmpty() && $statuses->every(fn ($item) => $item === 'selesai')
            ? 'selesai'
            : ($statuses->contains('diproses') ? 'diproses' : 'didisposisi');

        $suratMasuk->update(['status' => $status]);
    }

    /**
     * Halaman pengisian Lembar Disposisi (hanya Kepala Sekolah & Admin)
     */
    public function isiLembarDisposisi(Disposisi $disposisi)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);

        $disposisi->load('suratMasuk');
        $kepsek = User::where('role', 'kepala_sekolah')->where('is_active', true)->first();

        return view('disposisi.isi-lembar', compact('disposisi', 'kepsek'));
    }

    /**
     * Simpan pengisian Lembar Disposisi (hanya Kepala Sekolah & Admin)
     */
    public function simpanLembarDisposisi(Request $request, Disposisi $disposisi)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);

        $validated = $request->validate([
            'tujuan_disposisi' => 'nullable|array',
            'tujuan_disposisi.*' => 'string',
            'penerima_lain' => 'nullable|string|max:255',
            'harapan_instruksi' => 'nullable|array',
            'harapan_instruksi.*' => 'string',
            'sifat_surat' => 'nullable|array',
            'sifat_surat.*' => 'string',
            'catatan' => 'nullable|string',
            'instruksi_tambahan' => 'nullable|string|max:2000',
        ]);

        $disposisi->update($validated);

        return redirect()->route('disposisi.cetak', $disposisi->id)
            ->with('success', 'Lembar Disposisi berhasil disimpan. Silakan cetak.');
    }

    /**
     * Cetak/Preview Lembar Disposisi PDF (hanya Kepala Sekolah & Admin)
     */
    public function cetakLembarDisposisi(Disposisi $disposisi)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);

        $disposisi->load(['suratMasuk' => fn ($query) => $query->withTrashed()->with('creator')]);
        $kepsek = User::where('role', 'kepala_sekolah')->where('is_active', true)->first();

        $kepsekData = [
            'nama' => $kepsek?->name ?? 'Yayuk Sri Mulyani Rahayu, S.Pd., M.M.',
            'nip' => $kepsek?->nip ?? '19770421 200502 2 011',
            'pangkat' => $kepsek?->pangkat ?? 'Pembina Tk.I',
        ];

        $pdf = Pdf::loadView('disposisi.pdf', compact('disposisi', 'kepsekData'))
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'landscape');

        $safeNoAgenda = str_replace(['/', '\\'], '-', $disposisi->suratMasuk->no_agenda ?? 'draft');
        $filename = "lembar-disposisi-{$safeNoAgenda}.pdf";

        return $pdf->stream($filename);
    }
}
