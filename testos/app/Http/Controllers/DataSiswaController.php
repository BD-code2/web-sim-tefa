<?php

namespace App\Http\Controllers;

use App\Models\DataSiswa;
use App\Models\PeriodePkl;
use App\Models\SertifikatSiswa;
use App\Services\PklCertificatePdf;
use App\Services\StudentImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DataSiswaController extends Controller
{
    /**
     * Dashboard Data Siswa: daftar siswa, status sertifikat, dan aksi generate.
     */
    public function index(Request $request)
    {
        $request->merge(['periode_pkl_id' => null]);

        return $this->renderAgendaView($request, false);
    }

    public function showAgenda(Request $request, PeriodePkl $periode)
    {
        $request->merge(['periode_pkl_id' => $periode->id]);

        return $this->renderAgendaView($request, true);
    }

    private function renderAgendaView(Request $request, bool $agendaPage)
    {
        $periodes = PeriodePkl::orderByDesc('tanggal_mulai')->get();
        $periodeTerpilihId = $request->input('periode_pkl_id');

        $query = DataSiswa::query()->latest();
        if ($periodeTerpilihId) {
            $query->where('periode_pkl_id', $periodeTerpilihId);
        } else {
            $query->whereRaw('1 = 0');
        }

        $kelasTerpilih = $request->input('kelas');
        if (! $kelasTerpilih && $request->filled('search')) {
            $kataKunci = mb_strtolower(trim($request->input('search')));
            $kelasCocok = DataSiswa::query()
                ->where('periode_pkl_id', $periodeTerpilihId)
                ->whereNotNull('kelas')->where('kelas', '!=', '')
                ->distinct()->pluck('kelas')
                ->filter(fn ($kelas) => str_contains(mb_strtolower($kelas), $kataKunci));

            if ($kelasCocok->count() === 1) {
                $kelasTerpilih = $kelasCocok->first();
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nisn', 'like', "%{$s}%")
                    ->orWhere('nama', 'like', "%{$s}%")
                    ->orWhere('kelas', 'like', "%{$s}%")
                    ->orWhere('peran', 'like', "%{$s}%");
            });
        }

        if ($kelasTerpilih) {
            $query->where('kelas', $kelasTerpilih);
        }

        $dataSiswas = $query->paginate(10)->withQueryString();
        $kelasQuery = DataSiswa::query()->whereNotNull('kelas')->where('kelas', '!=', '');
        if ($periodeTerpilihId) {
            $kelasQuery->where('periode_pkl_id', $periodeTerpilihId);
        } else {
            $kelasQuery->whereRaw('1 = 0');
        }
        $kelasList = $kelasQuery->distinct()->orderBy('kelas')->pluck('kelas');
        $jumlahSiswaKelas = $kelasTerpilih
            ? DataSiswa::where('kelas', $kelasTerpilih)->where('periode_pkl_id', $periodeTerpilihId)->count()
            : 0;
        $sertifikatKelas = $kelasTerpilih ? SertifikatSiswa::with('industri')->where('kelas', $kelasTerpilih)->where('periode_pkl_id', $periodeTerpilihId)->get() : collect();
        $pklByNisn = SertifikatSiswa::where('periode_pkl_id', $periodeTerpilihId)->whereIn('nisn', $dataSiswas->pluck('nisn'))->get()->keyBy('nisn');

        return view('data-siswa.index', compact('dataSiswas', 'kelasList', 'kelasTerpilih', 'jumlahSiswaKelas', 'sertifikatKelas', 'pklByNisn', 'periodes', 'periodeTerpilihId', 'agendaPage'));
    }

    public function createPeriode()
    {
        return view('data-siswa.periode-create');
    }

    public function storePeriode(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'agenda' => 'nullable|string|max:2000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        $periode = PeriodePkl::create($validated + ['aktif' => true, 'created_by' => $request->user()->id]);

        return redirect()->route('data-siswa.index')
            ->with('success', "Agenda PKL {$periode->nama} tersimpan di arsip. Buka agenda tersebut untuk mengimport siswa.");
    }

    public function editPeriode(PeriodePkl $periode)
    {
        return view('data-siswa.periode-edit', compact('periode'));
    }

    public function updatePeriode(Request $request, PeriodePkl $periode)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
        ]);

        $periode->update($validated);

        return redirect()->route('data-siswa.index')
            ->with('success', 'Nama agenda PKL berhasil diperbarui.');
    }

    public function destroyPeriode(PeriodePkl $periode)
    {
        $namaAgenda = $periode->nama;

        DB::transaction(function () use ($periode) {
            $periode->sertifikats()->delete();
            $periode->dataSiswas()->delete();
            $periode->delete();
        });

        return redirect()->route('data-siswa.index')
            ->with('success', "Agenda PKL {$namaAgenda}, data siswa, dan data sertifikat terkait berhasil dihapus.");
    }

    /**
     * Form upload Excel/CSV.
     */
    public function importForm(Request $request)
    {
        $periodes = PeriodePkl::orderByDesc('tanggal_mulai')->get();
        $periodeTerpilihId = $request->input('periode_pkl_id');
        abort_unless($periodeTerpilihId && $periodes->contains('id', (int) $periodeTerpilihId), 404, 'Buka agenda PKL terlebih dahulu untuk mengimport siswa.');

        $agendaTerpilih = $periodes->firstWhere('id', (int) $periodeTerpilihId);

        return view('data-siswa.import', compact('periodes', 'periodeTerpilihId', 'agendaTerpilih'));
    }

    /**
     * Terima file, baca dengan parser, simpan ke tabel data_siswas.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'periode_pkl_id' => ['required', 'exists:periode_pkls,id'],
            'file' => ['required', 'file', 'extensions:xls,xlsx,csv', 'max:2048'],
        ]);

        $upload = $request->file('file');
        $extension = strtolower($upload->getClientOriginalExtension());
        $temporary = null;

        try {
            $temporary = $this->temporaryCopy($upload->getRealPath(), $extension);
            $rows = app(StudentImportParser::class)->parse($temporary, $extension);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal membaca file: '.$e->getMessage());
        } finally {
            if ($temporary !== null) {
                @unlink($temporary);
            }
        }

        if ($rows === []) {
            return back()->withInput()->with('warning', 'File terbaca tetapi tidak ada baris data.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if ($row['nisn'] === '' || $row['nama'] === '') {
                $skipped++;

                continue;
            }

            $siswa = DataSiswa::query()
                ->where('nisn', $row['nisn'])
                ->where('periode_pkl_id', $validated['periode_pkl_id'])
                ->first();

            if ($siswa !== null) {
                // Import ulang menyegarkan identitas tanpa mereset status sertifikat.
                $siswa->nama = $row['nama'];
                $siswa->kelas = $row['kelas'] !== '' ? $row['kelas'] : $siswa->kelas;
                if ($row['industri'] !== '') {
                    $siswa->industri = $row['industri'];
                }

                if ($row['peran'] !== '') {
                    $siswa->peran = $row['peran'];
                }

                if ($row['kelas'] !== '') {
                    $siswa->kelas = $row['kelas'];
                }

                if ($row['tanggal'] !== null) {
                    $siswa->tanggal = $row['tanggal'];
                }

                $siswa->save();
                $updated++;

                continue;
            }

            DataSiswa::create([
                'nisn' => $row['nisn'],
                'nama' => $row['nama'],
                'kelas' => $row['kelas'] !== '' ? $row['kelas'] : null,
                'industri' => $row['industri'] !== '' ? $row['industri'] : null,
                'periode_pkl_id' => $validated['periode_pkl_id'],
                'peran' => $row['peran'] !== '' ? $row['peran'] : null,
                'tanggal' => $row['tanggal'],
                'status_sertifikat' => DataSiswa::STATUS_PENDING,
            ]);
            $created++;
        }

        return redirect()
            ->route('data-siswa.agenda.show', $validated['periode_pkl_id'])
            ->with('success', sprintf('Import selesai: %d data baru, %d diperbarui, %d baris dilewati.', $created, $updated, $skipped));
    }

    public function prosesKelas(Request $request)
    {
        $validated = $request->validate([
            'kelas' => 'required|string|max:50',
            'periode_pkl_id' => 'required|exists:periode_pkls,id',
            'program_keahlian' => 'required|string|max:100',
        ]);

        $periode = PeriodePkl::findOrFail($validated['periode_pkl_id']);
        $siswas = DataSiswa::where('kelas', $validated['kelas'])->where('periode_pkl_id', $periode->id)->orderBy('nama')->get();
        abort_if($siswas->isEmpty(), 422, 'Tidak ada siswa pada kelas yang dipilih.');
        if ($siswas->contains(fn (DataSiswa $siswa) => blank($siswa->industri))) {
            throw ValidationException::withMessages([
                'kelas' => 'Kolom industri belum terisi untuk seluruh siswa di kelas ini. Import ulang Excel yang memiliki kolom industri sebelum menyiapkan PKL.',
            ]);
        }

        $created = DB::transaction(function () use ($siswas, $validated, $periode) {
            $count = 0;
            foreach ($siswas as $siswa) {
                $certificate = SertifikatSiswa::firstOrNew(['nisn' => $siswa->nisn, 'kelas' => $siswa->kelas, 'periode_pkl_id' => $periode->id]);
                if ($certificate->exists && $certificate->status === 'diterbitkan') {
                    continue;
                }
                $certificate->fill([
                    'nama_siswa' => $siswa->nama,
                    'program_keahlian' => $validated['program_keahlian'],
                    'kerja_sama_industri_id' => null,
                    'nama_industri_pkl' => $siswa->industri,
                    'status' => 'draft',
                    'tanggal_mulai_pkl' => $periode->tanggal_mulai,
                    'tanggal_selesai_pkl' => $periode->tanggal_selesai,
                ])->save();
                $count++;
            }

            return $count;
        });

        return redirect()->route('data-siswa.agenda.show', ['periode' => $periode->id, 'kelas' => $validated['kelas']])
            ->with('success', "Data PKL kelas {$validated['kelas']} disiapkan untuk {$created} siswa.");
    }

    public function terbitkanKelas(Request $request)
    {
        $validated = $request->validate(['kelas' => 'required|string|max:50', 'periode_pkl_id' => 'required|exists:periode_pkls,id']);
        $count = DB::transaction(function () use ($validated, $request) {
            $prefix = 'SERT/'.now()->format('Y').'/';
            $nomorTerpakai = SertifikatSiswa::where('no_sertifikat', 'like', $prefix.'%')->pluck('no_sertifikat');
            $nomorBerikutnya = $nomorTerpakai->map(function ($nomor) {
                return preg_match('/\/(\d+)$/', $nomor, $matches) ? (int) $matches[1] : 0;
            })->max() + 1;

            $certificates = SertifikatSiswa::where('kelas', $validated['kelas'])
                ->where('periode_pkl_id', $validated['periode_pkl_id'])
                ->where('status', 'terverifikasi')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($certificates as $certificate) {
                if (! $certificate->no_sertifikat) {
                    $certificate->no_sertifikat = $prefix.sprintf('%04d', $nomorBerikutnya++);
                }

                $certificate->status = 'diterbitkan';
                $certificate->diterbitkan_oleh = $request->user()->id;
                $certificate->diterbitkan_pada = now();
                $certificate->save();
            }

            return $certificates->count();
        });

        abort_if($count === 0, 422, 'Tidak ada sertifikat PKL terverifikasi untuk diterbitkan.');

        return redirect()->route('data-siswa.agenda.show', ['periode' => $validated['periode_pkl_id'], 'kelas' => $validated['kelas']])
            ->with('success', "Sertifikat PKL {$count} siswa kelas {$validated['kelas']} berhasil diterbitkan.");
    }

    public function selesaikanKelas(Request $request)
    {
        $validated = $request->validate(['kelas' => 'required|string|max:50', 'periode_pkl_id' => 'required|exists:periode_pkls,id']);
        $count = SertifikatSiswa::where('kelas', $validated['kelas'])->where('periode_pkl_id', $validated['periode_pkl_id'])->where('status', 'draft')->update(['status' => 'terverifikasi']);
        abort_if($count === 0, 422, 'Tidak ada data PKL draft untuk diselesaikan pada kelas ini.');

        return redirect()->route('data-siswa.agenda.show', ['periode' => $validated['periode_pkl_id'], 'kelas' => $validated['kelas']])
            ->with('success', "PKL {$count} siswa kelas {$validated['kelas']} ditandai selesai dan siap diterbitkan.");
    }

    public function pdfKelas(Request $request)
    {
        $validated = $request->validate(['kelas' => 'required|string|max:50', 'periode_pkl_id' => 'required|exists:periode_pkls,id']);
        $sertifikats = SertifikatSiswa::with(['industri', 'penerbit'])
            ->where('kelas', $validated['kelas'])
            ->where('periode_pkl_id', $validated['periode_pkl_id'])
            ->where('status', 'diterbitkan')
            ->orderBy('nama_siswa')
            ->get();
        abort_if($sertifikats->isEmpty(), 404, 'Belum ada sertifikat terbit untuk kelas ini.');

        ini_set('memory_limit', '256M');
        $pdf = app(PklCertificatePdf::class)->create($sertifikats);

        return $pdf->download('sertifikat-pkl-'.Str::slug($validated['kelas']).'-'.Str::slug(PeriodePkl::findOrFail($validated['periode_pkl_id'])->nama).'.pdf');
    }

    public function destroy(DataSiswa $dataSiswa)
    {
        $dataSiswa->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Salin upload ke path sementara berekstensi asli
     * (upload PHP bernama phpXXXX.tmp sehingga reader tidak bisa menebak format).
     */
    private function temporaryCopy(string $source, string $extension): string
    {
        $temporary = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'siswa-import-'.uniqid().'.'.$extension;

        if (! @copy($source, $temporary)) {
            throw new \RuntimeException('File sementara gagal dibuat.');
        }

        return $temporary;
    }
}
