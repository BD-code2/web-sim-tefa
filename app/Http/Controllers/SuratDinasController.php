<?php

namespace App\Http\Controllers;

use App\Models\SuratDinas;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SuratDinasController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratDinas::with('creator')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('no_surat', 'like', "%{$s}%")
                    ->orWhere('tentang_judul', 'like', "%{$s}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_surat', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suratDinas = $query->paginate(10)->withQueryString();

        return view('surat-dinas.index', compact('suratDinas'));
    }

    public function createSk()
    {
        return view('surat-dinas.create-sk');
    }

    public function createSpt()
    {
        return view('surat-dinas.create-spt');
    }

    public function store(Request $request)
    {
        if (strtoupper($request->input('jenis_surat')) === 'SK' || strtoupper($request->input('jenis_surat')) === 'SPT') {
            $validated = $request->validate([
                'jenis_surat' => 'required|in:SK,SPT', 'no_surat' => 'nullable|string|max:255',
                'tentang_judul' => 'required|string|max:255', 'tgl_ditetapkan' => 'nullable|date',
                'tempat_ditetapkan' => 'nullable|string|max:255', 'tahun_pelajaran' => 'nullable|string|max:20', 'tanggal_mulai_berlaku' => 'nullable|date', 'menimbang' => 'required_if:jenis_surat,SK|nullable|string',
                'mengingat' => 'required_if:jenis_surat,SK|nullable|string', 'dictum_list' => 'required_if:jenis_surat,SK|array',
                'dictum_list.*' => 'nullable|string', 'tembusan' => 'nullable|string', 'dasar' => 'nullable|string',
                'pegawai_nama' => 'required_if:jenis_surat,SPT|nullable|string|max:255', 'pegawai_pangkat_golongan' => 'nullable|string|max:255',
                'pegawai_nip' => 'nullable|string|max:255', 'pegawai_jabatan' => 'nullable|string|max:255',
                'untuk_tugas' => 'required_if:jenis_surat,SPT|nullable|string', 'untuk_tempat' => 'nullable|string|max:255',
                'untuk_tanggal_mulai' => 'nullable|date', 'untuk_tanggal_selesai' => 'nullable|date|after_or_equal:untuk_tanggal_mulai',
                'peserta_list' => 'nullable|array', 'peserta_list.*.nama' => 'nullable|string|max:255', 'peserta_list.*.kelas' => 'nullable|string|max:100',
            ]);
            $dictums = array_values(array_filter(array_map('trim', $validated['dictum_list'] ?? [])));
            if ($validated['jenis_surat'] === 'SK' && $dictums === []) {
                return back()->withErrors(['dictum_list' => 'Minimal satu diktum harus diisi.'])->withInput();
            }
            $detail = $validated['jenis_surat'] === 'SK' ? [
                'tempat_ditetapkan' => $validated['tempat_ditetapkan'] ?? 'Tanjungpinang', 'tahun_pelajaran' => $validated['tahun_pelajaran'] ?? null, 'tanggal_mulai_berlaku' => $validated['tanggal_mulai_berlaku'] ?? null, 'menimbang' => $validated['menimbang'] ?? null,
                'mengingat' => $validated['mengingat'] ?? null, 'dictum_list' => $dictums, 'tembusan' => $validated['tembusan'] ?? null,
            ] : $this->detailFrom($validated);
            $surat = SuratDinas::create([
                'jenis_surat' => $validated['jenis_surat'], 'no_surat' => $validated['no_surat'] ?? null,
                'tentang_judul' => $validated['tentang_judul'], 'tgl_ditetapkan' => $validated['tgl_ditetapkan'] ?? null,
                'status' => 'menunggu_persetujuan', 'detail_json' => $detail, 'created_by' => $request->user()->id,
            ]);

            return redirect()->route('surat-dinas.show', $surat)->with('success', 'Surat berhasil dibuat dan diajukan ke Kepala TU untuk diperiksa.');
        }
        $validated = $request->validate([
            // Identitas surat
            'jenis_surat' => 'required|in:SK,SPT',
            'no_surat' => 'nullable|string|max:255',
            'tentang_judul' => 'required|string|max:255',
            'tgl_ditetapkan' => 'nullable|date',

            // Detail SK (Surat Keputusan)
            'tempat_ditetapkan' => 'nullable|string|max:255',
            'menimbang' => 'required_if:jenis_surat,SK|nullable|string',
            'mengingat' => 'required_if:jenis_surat,SK|nullable|string',
            'dictum_list' => 'required_if:jenis_surat,SK|array',
            'dictum_list.*' => 'nullable|string',
            'tembusan' => 'nullable|string',

            // Detail SPT (Surat Perintah Tugas)
            'dasar' => 'nullable|string',
            'pegawai_nama' => 'required_if:jenis_surat,SPT|nullable|string|max:255',
            'pegawai_pangkat_golongan' => 'nullable|string|max:255',
            'pegawai_nip' => 'nullable|string|max:255',
            'pegawai_jabatan' => 'nullable|string|max:255',
            'untuk_tugas' => 'required_if:jenis_surat,SPT|nullable|string',
            'untuk_tempat' => 'nullable|string|max:255',
            'untuk_tanggal_mulai' => 'nullable|date',
            'untuk_tanggal_selesai' => 'nullable|date|after_or_equal:untuk_tanggal_mulai',
            'peserta_list' => 'nullable|array',
            'peserta_list.*.nama' => 'nullable|string|max:255',
            'peserta_list.*.kelas' => 'nullable|string|max:100',
        ]);

        // Buang baris diktum kosong; pastikan SK punya minimal satu diktum.
        $dictumList = array_values(array_filter(
            array_map('trim', $validated['dictum_list'] ?? []),
            fn ($teks) => $teks !== ''
        ));

        if ($validated['jenis_surat'] === 'SK' && $dictumList === []) {
            return back()->withErrors([
                'dictum_list' => 'Minimal satu butir diktum (Menetapkan) wajib diisi.',
            ])->withInput();
        }

        $validated['dictum_list'] = $dictumList;

        // Status awal: diajukan ke Kepala Sekolah untuk persetujuan.
        $surat = SuratDinas::create([
            'jenis_surat' => $validated['jenis_surat'],
            'no_surat' => ($validated['no_surat'] ?? '') ?: null,
            'tentang_judul' => $validated['tentang_judul'],
            'tgl_ditetapkan' => $validated['tgl_ditetapkan'] ?? null,
            'status' => 'menunggu_persetujuan',
            'detail_json' => $this->detailFrom($validated),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('surat-dinas.show', $surat)
            ->with('success', 'Surat dinas berhasil dibuat dan diajukan ke Kepala TU untuk diperiksa.');
    }

    public function show(SuratDinas $suratDinas)
    {
        $suratDinas->load('creator');

        return view('surat-dinas.show', compact('suratDinas'));
    }

    public function edit(SuratDinas $suratDinas)
    {
        abort_unless(
            $suratDinas->status === 'ditolak'
                && $suratDinas->diperiksa_kepala_tu_at
                && is_null($suratDinas->diteruskan_ke_kepala_sekolah_at),
            403
        );

        $detail = $suratDinas->detail_json ?? [];

        return view($suratDinas->jenis_surat === 'SK' ? 'surat-dinas.create-sk' : 'surat-dinas.create-spt', compact('suratDinas', 'detail'));
    }

    public function update(Request $request, SuratDinas $suratDinas)
    {
        abort_unless(
            $suratDinas->status === 'ditolak'
                && $suratDinas->diperiksa_kepala_tu_at
                && is_null($suratDinas->diteruskan_ke_kepala_sekolah_at),
            403
        );

        $request->merge(['jenis_surat' => $suratDinas->jenis_surat]);

        $validated = $request->validate([
            'no_surat' => ['nullable', 'string', 'max:255', Rule::unique('surat_dinas', 'no_surat')->ignore($suratDinas->id)],
            'tentang_judul' => 'required|string|max:255',
            'tgl_ditetapkan' => 'nullable|date',
            'tempat_ditetapkan' => 'nullable|string|max:255',
            'tahun_pelajaran' => 'nullable|string|max:20',
            'tanggal_mulai_berlaku' => 'nullable|date',
            'menimbang' => 'required_if:jenis_surat,SK|nullable|string',
            'mengingat' => 'required_if:jenis_surat,SK|nullable|string',
            'dictum_list' => 'required_if:jenis_surat,SK|array',
            'dictum_list.*' => 'nullable|string',
            'tembusan' => 'nullable|string',
            'dasar' => 'nullable|string',
            'pegawai_nama' => 'required_if:jenis_surat,SPT|nullable|string|max:255',
            'pegawai_pangkat_golongan' => 'nullable|string|max:255',
            'pegawai_nip' => 'nullable|string|max:255',
            'pegawai_jabatan' => 'nullable|string|max:255',
            'untuk_tugas' => 'required_if:jenis_surat,SPT|nullable|string',
            'untuk_tempat' => 'nullable|string|max:255',
            'untuk_tanggal_mulai' => 'nullable|date',
            'untuk_tanggal_selesai' => 'nullable|date|after_or_equal:untuk_tanggal_mulai',
            'peserta_list' => 'nullable|array',
            'peserta_list.*.nama' => 'nullable|string|max:255',
            'peserta_list.*.kelas' => 'nullable|string|max:100',
        ]);

        $validated['jenis_surat'] = $suratDinas->jenis_surat;
        $dictums = array_values(array_filter(array_map('trim', $validated['dictum_list'] ?? [])));
        if ($suratDinas->jenis_surat === 'SK' && $dictums === []) {
            return back()->withErrors(['dictum_list' => 'Minimal satu diktum harus diisi.'])->withInput();
        }
        $validated['dictum_list'] = $dictums;

        $suratDinas->update([
            'no_surat' => ($validated['no_surat'] ?? '') ?: null,
            'tentang_judul' => $validated['tentang_judul'],
            'tgl_ditetapkan' => $validated['tgl_ditetapkan'] ?? null,
            'detail_json' => $this->detailFrom($validated),
            'status' => 'menunggu_persetujuan',
            'catatan_revisi' => null,
            'diperiksa_kepala_tu_at' => null,
            'diperiksa_kepala_tu_oleh' => null,
            'diteruskan_ke_kepala_sekolah_at' => null,
            'diteruskan_ke_kepala_sekolah_oleh' => null,
        ]);

        return redirect()->route('surat-dinas.show', $suratDinas)
            ->with('success', 'Surat Dinas hasil revisi berhasil diajukan kembali ke Kepala TU.');
    }

    public function cetakPdf($id)
    {
        $suratDinas = SuratDinas::with('creator')->findOrFail($id);

        // Cetak PDF hanya aktif setelah Surat Dinas disetujui Kepala Sekolah.
        abort_if($suratDinas->status !== 'disetujui', 403, 'Cetak PDF hanya tersedia untuk Surat Dinas yang sudah disetujui.');

        $kepsek = User::where('role', 'kepala_sekolah')->where('is_active', true)->first();

        ini_set('memory_limit', '256M');

        $template = $suratDinas->jenis_surat === 'SPT' ? 'surat-dinas.pdf-spt' : 'surat-dinas.pdf-sk';

        $pdf = Pdf::loadView($template, compact('suratDinas', 'kepsek'))
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'portrait');

        $safeNoSurat = str_replace(['/', '\\'], '-', $suratDinas->no_surat ?? 'tanpa-nomor');
        $filename = "surat-dinas-{$suratDinas->jenis_surat}-{$safeNoSurat}.pdf";

        return $pdf->stream($filename);
    }

    public function periksaKepalaTU(Request $request, SuratDinas $suratDinas)
    {
        abort_unless($suratDinas->status === 'menunggu_persetujuan' && is_null($suratDinas->diperiksa_kepala_tu_at), 409);

        $validated = $request->validate([
            'keputusan' => 'required|in:periksa,revisi',
            'catatan_revisi' => 'required_if:keputusan,revisi|nullable|string|max:1000',
        ]);

        if ($validated['keputusan'] === 'revisi') {
            $suratDinas->update([
                'status' => 'ditolak',
                'catatan_revisi' => $validated['catatan_revisi'],
                'diperiksa_kepala_tu_at' => now(),
                'diperiksa_kepala_tu_oleh' => $request->user()->id,
                'diteruskan_ke_kepala_sekolah_at' => null,
                'diteruskan_ke_kepala_sekolah_oleh' => null,
            ]);

            return redirect()->route('surat-keluar.pemeriksaan-kepala-tu')
                ->with('warning', 'Surat Dinas dikembalikan kepada TU untuk diperbaiki.');
        }

        $suratDinas->update([
            'diperiksa_kepala_tu_at' => now(),
            'diperiksa_kepala_tu_oleh' => $request->user()->id,
            'catatan_revisi' => null,
        ]);

        return redirect()->route('surat-keluar.pemeriksaan-kepala-tu')
            ->with('success', 'Pemeriksaan Surat Dinas selesai. Surat dikembalikan ke TU untuk diteruskan ke Kepala Sekolah.');
    }

    public function teruskanKeKepalaSekolah(Request $request, SuratDinas $suratDinas)
    {
        abort_unless(
            $suratDinas->status === 'menunggu_persetujuan'
                && $suratDinas->diperiksa_kepala_tu_at
                && is_null($suratDinas->diteruskan_ke_kepala_sekolah_at),
            409,
            'Surat Dinas harus diperiksa Kepala TU sebelum diteruskan.'
        );

        $suratDinas->update([
            'diteruskan_ke_kepala_sekolah_at' => now(),
            'diteruskan_ke_kepala_sekolah_oleh' => $request->user()->id,
        ]);

        return redirect()->route('surat-dinas.show', $suratDinas)
            ->with('success', 'Surat Dinas telah diteruskan ke Kepala Sekolah untuk persetujuan.');
    }

    /**
     * Setujui Surat Dinas (Kepala Sekolah / Admin) — terbitkan nomor bila kosong.
     */
    public function setujui(Request $request, SuratDinas $suratDinas)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);
        abort_unless($suratDinas->diteruskan_ke_kepala_sekolah_at, 409, 'Surat Dinas belum diteruskan ke Kepala Sekolah oleh TU.');

        $request->validate([
            'catatan_revisi' => 'nullable|string|max:1000',
        ]);

        if (! $suratDinas->no_surat) {
            $suratDinas->no_surat = $this->generateNomor($suratDinas);
        }

        $suratDinas->update([
            'status' => 'disetujui',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'catatan_revisi' => $request->catatan_revisi,
        ]);

        return redirect()->route('disposisi.antrian', ['tab' => 'persetujuan'])
            ->with('success', "Surat dinas telah disetujui dengan No. Surat: {$suratDinas->no_surat}");
    }

    /**
     * Tolak / Minta Revisi Surat Dinas (Kepala Sekolah / Admin) — wajib catatan.
     */
    public function revisi(Request $request, SuratDinas $suratDinas)
    {
        abort_if(! Gate::allows('manage-disposisi'), 403);
        abort_unless($suratDinas->diteruskan_ke_kepala_sekolah_at, 409, 'Surat Dinas belum diteruskan ke Kepala Sekolah oleh TU.');

        $request->validate([
            'catatan_revisi' => 'required|string|max:1000',
        ]);

        $suratDinas->update([
            'status' => 'ditolak',
            'catatan_revisi' => $request->catatan_revisi,
        ]);

        return redirect()->route('disposisi.antrian', ['tab' => 'persetujuan'])
            ->with('warning', 'Surat dinas dikembalikan untuk revisi. Catatan: '.$request->catatan_revisi);
    }

    /**
     * Nomor surat saat disetujui (bila TU tidak mengisi manual):
     * SK  -> 421.5/SMKN4/MM/YYYY/NNNN
     * SPT -> 421.5.1/SMKN4/MM/YYYY/NNNN
     * Urut per jenis, dihitung dari nomor ber-prefix tersebut.
     */
    private function generateNomor(SuratDinas $suratDinas): string
    {
        $prefix = $suratDinas->jenis_surat === 'SPT' ? '421.5.1' : '421.5';
        $month = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        $year = now()->year;
        $format = "{$prefix}/SMKN4/{$month}/{$year}/";

        $count = SuratDinas::where('jenis_surat', $suratDinas->jenis_surat)
            ->where('no_surat', 'like', $format.'%')
            ->count() + 1;

        return $format.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Kumpulkan field detail sesuai jenis surat, lalu simpan sebagai JSON
     * di kolom detail_json (lihat model SuratDinas).
     */
    private function detailFrom(array $validated): array
    {
        if ($validated['jenis_surat'] === 'SK') {
            return [
                'tempat_ditetapkan' => ($validated['tempat_ditetapkan'] ?? '') ?: 'Tanjungpinang',
                'tahun_pelajaran' => $validated['tahun_pelajaran'] ?? null,
                'tanggal_mulai_berlaku' => $validated['tanggal_mulai_berlaku'] ?? null,
                'menimbang' => $validated['menimbang'] ?? null,
                'mengingat' => $validated['mengingat'] ?? null,
                'dictum_list' => $validated['dictum_list'] ?? [],
                'tembusan' => $validated['tembusan'] ?? null,
            ];
        }

        $pesertaList = array_values(array_filter(
            $validated['peserta_list'] ?? [],
            fn ($baris) => trim((string) ($baris['nama'] ?? '')) !== ''
        ));

        return [
            'tempat_ditetapkan' => ($validated['tempat_ditetapkan'] ?? '') ?: 'Tanjungpinang',
            'dasar' => $validated['dasar'] ?? null,
            'pegawai_nama' => $validated['pegawai_nama'] ?? null,
            'pegawai_pangkat_golongan' => $validated['pegawai_pangkat_golongan'] ?? null,
            'pegawai_nip' => $validated['pegawai_nip'] ?? null,
            'pegawai_jabatan' => $validated['pegawai_jabatan'] ?? null,
            'untuk_tugas' => $validated['untuk_tugas'] ?? null,
            'untuk_tempat' => $validated['untuk_tempat'] ?? null,
            'untuk_tanggal_mulai' => $validated['untuk_tanggal_mulai'] ?? null,
            'untuk_tanggal_selesai' => $validated['untuk_tanggal_selesai'] ?? null,
            'peserta_list' => $pesertaList,
        ];
    }
}
