<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratKeluar::with(['creator', 'approver', 'referensiSuratMasuk'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_surat', 'like', "%{$s}%")
                    ->orWhere('tujuan', 'like', "%{$s}%")
                    ->orWhere('perihal', 'like', "%{$s}%")
                    ->orWhere('acara', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suratKeluar = $query->paginate(10)->withQueryString();

        return view('surat-keluar.index', compact('suratKeluar'));
    }

    // Generator khusus surat keluar: undangan dan rekomendasi mutasi.
    public function create(Request $request, $kategori = null)
    {
        if ($kategori !== null && ! in_array(strtolower((string) $kategori), ['undangan', 'mutasi', 'sk', 'spt'], true)) {
            return redirect()->route('surat-keluar.create')->with('error', 'Jenis surat tidak dikenali.');
        }
        if (in_array(strtolower((string) $kategori), ['sk', 'spt'], true)) {
            return redirect()->route(strtolower((string) $kategori) === 'sk' ? 'surat-dinas.create-sk' : 'surat-dinas.create-spt');
        }
        return view('surat-keluar.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $jenis = strtolower($request->input('jenis_surat', ''));
        $legacyRequest = ! $request->has('jenis_surat');
        if ($legacyRequest) {
            $jenis = 'undangan';
        }
        if ($jenis === 'sk' && ! $request->exists('dictum_list')) {
            $teksDiktum = (string) $request->input('diktum_teks', '');
            $request->merge(['dictum_list' => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $teksDiktum))))]);
        }
        if ($jenis === 'spt' && ! $request->exists('peserta_list')) {
            $pesertaList = array_map(function ($baris) {
                [$nama, $kelas] = array_pad(explode('|', trim($baris), 2), 2, '');

                return ['nama' => trim($nama), 'kelas' => trim($kelas)];
            }, preg_split('/\r\n|\r|\n/', (string) $request->input('daftar_siswa_teks', '')));
            $request->merge(['peserta_list' => array_values(array_filter($pesertaList, fn ($row) => $row['nama'] !== ''))]);
        }
        if ($jenis === 'undangan' && $legacyRequest) {
            $request->merge(['nama_kegiatan' => $request->input('acara')]);
        }
        $rules = [
            'jenis_surat' => $legacyRequest ? 'sometimes|in:undangan,mutasi' : 'required|in:undangan,mutasi',
            'no_surat' => ['nullable', 'string', 'max:255', Rule::unique('surat_keluars', 'no_surat')->ignore($request->route('suratKeluar')?->id)],
            'tanggal_surat' => $legacyRequest ? 'nullable|date' : 'required|date',
            'tempat_ditetapkan' => 'nullable|string|max:255',
            'tanggal_mulai_berlaku' => 'nullable|date',
            'tahun_pelajaran' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan,mutasi,sk').'|string|max:20',
            'tujuan' => 'required_if:jenis_surat,undangan|nullable|string|max:255',
            'perihal' => 'nullable|string|max:255',
            'nama_siswa' => 'required_if:jenis_surat,mutasi|nullable|string|max:255',
            'kelas_siswa' => 'required_if:jenis_surat,mutasi|nullable|string|max:100',
            'semester' => 'nullable|string|max:30',
            'nisn' => 'required_if:jenis_surat,mutasi|nullable|string|max:30',
            'alamat_penerima' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan').'|string|max:1000',
            'nama_kegiatan' => 'required_if:jenis_surat,undangan|nullable|string|max:255',
            'keperluan' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan').'|string|max:2000',
            'tgl_pelaksanaan' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan,spt').'|date',
            'waktu' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan').'|string|max:255',
            'tempat' => ($legacyRequest ? 'nullable' : 'required_if:jenis_surat,undangan,spt').'|string|max:255',
            'dasar' => 'required_if:jenis_surat,spt|nullable|string',
            'pegawai_nama' => 'required_if:jenis_surat,spt,sk|nullable|string|max:255',
            'pegawai_pangkat_golongan' => 'required_if:jenis_surat,spt|nullable|string|max:255',
            'pegawai_nip' => 'required_if:jenis_surat,spt|nullable|string|max:255',
            'pegawai_jabatan' => 'required_if:jenis_surat,spt,sk|nullable|string|max:255',
            'untuk_tanggal_selesai' => 'nullable|date|after_or_equal:tgl_pelaksanaan',
            'untuk_tugas' => 'required_if:jenis_surat,spt|nullable|string',
            'peserta_list' => 'required_if:jenis_surat,spt|array|min:1',
            'peserta_list.*.nama' => 'nullable|string|max:255',
            'peserta_list.*.kelas' => 'nullable|string|max:100',
            'tentang_judul' => 'required_if:jenis_surat,sk|nullable|string|max:255',
            'menimbang' => 'nullable|string',
            'mengingat' => 'nullable|string',
            'dictum_list' => 'required_if:jenis_surat,sk|array|min:1',
            'dictum_list.*' => 'nullable|string',
            'tembusan' => 'nullable|string',
            'tanggal_mulai_berlaku' => 'nullable|date',
            'nama_orang_tua' => 'required_if:jenis_surat,mutasi|nullable|string|max:255',
            'alamat_orang_tua' => 'required_if:jenis_surat,mutasi|nullable|string|max:1000',
            'tempat_lahir' => 'required_if:jenis_surat,mutasi|nullable|string|max:100',
            'tanggal_lahir' => 'required_if:jenis_surat,mutasi|nullable|date',
            'nama_ayah' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'semester' => 'required_if:jenis_surat,mutasi|nullable|string|max:30',
            'sekolah_asal' => 'required_if:jenis_surat,mutasi|nullable|string|max:255',
            'alamat_sekolah_asal' => 'required_if:jenis_surat,mutasi|nullable|string|max:1000',
            'sekolah_tujuan' => 'required_if:jenis_surat,mutasi|nullable|string|max:255',
            'alasan_mutasi' => 'required_if:jenis_surat,mutasi|nullable|string',
            'dokumen_lampiran' => 'required_if:jenis_surat,mutasi|nullable|string',
            'referensi_surat_masuk_id' => 'nullable|exists:surat_masuks,id',
            'action' => 'nullable|in:draft,ajukan',
        ];
        $validated = $request->validate($rules);
        if ($jenis === 'undangan' && empty($validated['tujuan'])) {
            return back()->withErrors(['tujuan' => 'Nama penerima surat wajib diisi.'])->withInput();
        }
        if ($jenis === 'undangan' && ! $legacyRequest) {
            $request->validate([
                'nama_siswa' => 'required|string|max:255',
                'kelas_siswa' => 'required|string|max:100',
                'nama_kegiatan' => 'required|string|max:255',
                'tahun_pelajaran' => 'required|string|max:20',
            ]);
            $validated = array_merge($validated, $request->only(['nama_siswa', 'kelas_siswa', 'nama_kegiatan']));
        }
        if ($jenis === 'sk') {
            $diktumInput = $request->input('dictum_list', $request->input('diktum_teks', ''));
            $validated['dictum_list'] = is_array($diktumInput)
                ? array_values(array_filter(array_map('trim', $diktumInput)))
                : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $diktumInput))));
            if ($validated['dictum_list'] === []) {
                if ($request->has('diktum_teks')) {
                    return back()->withErrors(['diktum_teks' => 'Minimal satu diktum harus diisi.'])->withInput();
                }
            }
        }
        if ($jenis === 'spt') {
            $request->validate(['pegawai_jabatan' => 'required|string|max:255', 'dasar' => 'required|string', 'untuk_tugas' => 'required|string', 'tempat_ditetapkan' => 'required|string|max:255']);
            $pesertaInput = $request->input('peserta_list');
            $validated['peserta_list'] = is_array($pesertaInput) ? array_values(array_filter($pesertaInput, fn ($row) => trim((string) ($row['nama'] ?? '')) !== '')) : array_values(array_filter(array_map(function ($baris) {
                [$nama, $kelas] = array_pad(explode('|', $baris, 2), 2, '');

                return ['nama' => trim($nama), 'kelas' => trim($kelas)];
            }, preg_split('/\r\n|\r|\n/', $request->input('daftar_siswa_teks', ''))), fn ($row) => $row['nama'] !== ''));
            if (! $legacyRequest && $validated['peserta_list'] === []) {
                return back()->withErrors(['daftar_siswa_teks' => 'Isi minimal satu nama siswa beserta kelas.'])->withInput();
            }
        }
        if ($jenis === 'sk') {
            $request->validate(['tahun_pelajaran' => 'required|string|max:20', 'tanggal_mulai_berlaku' => 'required|date']);
        }
        if ($jenis === 'sk' && ($request->filled('pegawai_nama') || $request->filled('pegawai_jabatan'))) {
            $request->validate(['pegawai_nama' => 'required|string|max:255', 'pegawai_jabatan' => 'required|string|max:255']);
        }
        $detailKeys = ['tanggal_surat', 'tahun_pelajaran', 'alamat_penerima', 'nama_siswa', 'kelas_siswa', 'nama_orang_tua', 'alamat_orang_tua', 'tempat_lahir', 'tanggal_lahir', 'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu', 'nama_kegiatan', 'keperluan', 'dasar', 'pegawai_nama', 'pegawai_pangkat_golongan', 'pegawai_nip', 'pegawai_jabatan', 'untuk_tugas', 'peserta_list', 'tentang_judul', 'menimbang', 'mengingat', 'dictum_list', 'tembusan', 'tanggal_mulai_berlaku', 'sekolah_asal', 'alamat_sekolah_asal', 'sekolah_tujuan', 'alasan_mutasi', 'dokumen_lampiran', 'semester', 'tempat_ditetapkan'];
        $detail = array_intersect_key($validated, array_flip($detailKeys));
        if ($jenis === 'spt') {
            $detail['untuk_tempat'] = $validated['tempat'];
            $detail['untuk_tanggal_mulai'] = $validated['tgl_pelaksanaan'];
            $detail['tanggal_surat'] = $validated['tanggal_surat'];
        }
        $judul = match ($jenis) {
            'spt' => 'Surat Perintah Tugas: '.($validated['pegawai_nama'] ?? ''),
            'sk' => $validated['tentang_judul'] ?? 'Surat Keputusan',
            'mutasi' => 'Rekomendasi Mutasi: '.($validated['nama_siswa'] ?? ''),
            default => $validated['perihal'] ?? 'Surat Undangan',
        };
        $status = $request->input('action') === 'draft' ? 'draft' : 'menunggu_persetujuan';

        $nomorSurat = ($validated['no_surat'] ?? '') ?: null;

        $surat = SuratKeluar::create([
            'jenis_surat' => $jenis,
            'no_surat' => $nomorSurat,
            'tujuan' => $validated['tujuan'] ?? $validated['sekolah_tujuan'] ?? $validated['pegawai_nama'] ?? $validated['tentang_judul'] ?? $validated['nama_siswa'] ?? '-',
            'perihal' => $jenis === 'mutasi' ? 'Surat Rekomendasi Mutasi/Pindah Siswa' : ($jenis === 'undangan' ? 'Undangan' : $judul),
            'tgl_pelaksanaan' => $validated['tgl_pelaksanaan'] ?? null,
            'waktu' => $validated['waktu'] ?? null,
            'tempat' => $validated['tempat'] ?? null,
            'acara' => $validated['nama_kegiatan'] ?? $validated['untuk_tugas'] ?? null,
            'isi_surat' => null,
            'detail_json' => $detail,
            'referensi_surat_masuk_id' => $validated['referensi_surat_masuk_id'] ?? null,
            'status' => $status,
            'created_by' => $request->user()->id,
        ]);

        $pesan = $status === 'draft' ? 'Draf surat otomatis berhasil disimpan.' : 'Surat otomatis berhasil dibuat dan diajukan ke Kepala Sekolah untuk persetujuan.';

        return redirect()->route('surat-keluar.show', $surat)->with('success', $pesan);
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['creator', 'approver', 'referensiSuratMasuk']);

        return view('surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        if (! in_array($suratKeluar->status, ['draft', 'ditolak', 'minta_revisi'])) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', 'Surat yang sedang diajukan atau sudah disetujui tidak dapat diedit langsung.');
        }

        $suratMasukList = SuratMasuk::latest()->get();

        return view('surat-keluar.edit', compact('suratKeluar', 'suratMasukList'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'tgl_pelaksanaan' => 'nullable|date',
            'waktu' => 'nullable|string|max:255',
            'tempat' => 'nullable|string|max:255',
            'acara' => 'nullable|string|max:255',
            'isi_surat' => 'nullable|string',
            'referensi_surat_masuk_id' => 'nullable|exists:surat_masuks,id',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'action' => 'nullable|in:draft,ajukan',
        ]);

        if ($request->hasFile('lampiran')) {
            if ($suratKeluar->lampiran_path && Storage::disk('public')->exists($suratKeluar->lampiran_path)) {
                Storage::disk('public')->delete($suratKeluar->lampiran_path);
            }
            $validated['lampiran_path'] = $request->file('lampiran')->store('surat-keluar', 'public');
        }

        if (isset($validated['action']) && $validated['action'] === 'ajukan') {
            $validated['status'] = 'menunggu_persetujuan';
        }

        unset($validated['action']);

        $suratKeluar->update($validated);

        return redirect()->route('surat-keluar.show', $suratKeluar)->with('success', 'Konsep surat berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    // Persetujuan oleh Kepala Sekolah
    public function approve(Request $request, SuratKeluar $suratKeluar)
    {
        abort_unless(Gate::allows('manage-disposisi'), 403);

        $request->validate([
            'decision' => 'required|in:setujui,tolak',
            'catatan_revisi' => 'nullable|string',
        ]);

        if ($request->decision === 'setujui') {
            // Generate nomor surat jika belum ada
            if (! $suratKeluar->no_surat) {
                $suratKeluar->no_surat = $this->nomorOtomatis($suratKeluar->jenis_surat);
            }

            $suratKeluar->update([
                'status' => 'disetujui',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'catatan_revisi' => $request->catatan_revisi,
            ]);

            return back()->with('success', "Surat keluar telah disetujui dengan No. Surat: {$suratKeluar->no_surat}");
        } else {
            $suratKeluar->update([
                'status' => 'ditolak',
                'catatan_revisi' => $request->catatan_revisi,
            ]);

            return back()->with('warning', 'Konsep surat ditolak dengan catatan revisi.');
        }
    }

    // Ubah status kirim / arsip
    public function updateStatus(Request $request, SuratKeluar $suratKeluar)
    {
        $request->validate([
            'status' => 'required|in:dikirim,diarsipkan',
        ]);

        $suratKeluar->update(['status' => $request->status]);

        return back()->with('success', 'Status surat berhasil diperbarui menjadi '.ucfirst($request->status));
    }

    // Export PDF Cetak Surat Undangan / Surat Umum
    public function cetakPdf(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['creator', 'approver']);
        $kepsek = User::where('role', 'kepala_sekolah')->where('is_active', true)->first();

        ini_set('memory_limit', '256M');

        $view = match ($suratKeluar->jenis_surat) {
            'spt' => 'surat-keluar.pdf-spt',
            'sk' => 'surat-keluar.pdf-sk',
            'mutasi' => 'surat-keluar.pdf-mutasi',
            default => 'surat-keluar.pdf',
        };
        $pdf = Pdf::loadView($view, compact('suratKeluar', 'kepsek'))
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'portrait');

        $safeNoSurat = str_replace(['/', '\\'], '-', $suratKeluar->nomor_surat_display ?? 'tanpa-nomor');
        $filename = "surat-keluar-{$safeNoSurat}.pdf";

        return $pdf->stream($filename);
    }

    private function nomorOtomatis(string $jenis): string
    {
        $prefix = match ($jenis) {
            'spt' => '421.5.1', 'sk' => '421.5', 'mutasi' => '421.5.2', default => '421.5',
        };

        return SuratKeluar::nomorBerikutnya($jenis);
    }
}
