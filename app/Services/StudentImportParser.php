<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Pembaca file Excel (.xlsx/.xls) dan CSV untuk import Data Siswa.
 *
 * Pemetaan kolom header (baris pertama, tidak sensitif huruf besar/kecil):
 *   NISN / NIS        -> nisn
 *   Nama Lengkap      -> nama
 *   Kelas             -> kelas
 *   Industri          -> industri
 *   Peran/Penghargaan -> peran
 *   Tanggal           -> tanggal
 *   Tempat / Tgl. Lahir atau kolom Tempat Lahir + Tanggal Lahir -> tempat_tgl_lahir
 */
class StudentImportParser
{
    private const HEADER_KEYWORDS = [
        'tempat_tgl_lahir' => ['tempat tanggal lahir', 'tempat tgl lahir', 'ttl'],
        'tempat_lahir' => ['tempat lahir'],
        'tanggal_lahir' => ['tanggal lahir', 'tgl lahir', 'tanggal lahir siswa'],
        'nisn' => ['nisn', 'nis'],
        'nama' => ['nama_lengkap', 'nama'],
        'kelas' => ['kelas', 'rombel', 'class'],
        'industri' => ['industri', 'industry', 'tempat pkl', 'dudi', 'perusahaan'],
        'peran' => ['peran', 'penghargaan', 'prestasi'],
        'tanggal' => ['tanggal', 'tgl'],
    ];

    private const REQUIRED_FIELDS = ['nisn', 'nama', 'kelas', 'industri'];

    /**
     * @return array<int, array{nisn: string, nama: string, tempat_tgl_lahir: string, kelas: string, industri: string, peran: string, tanggal: ?string}>
     */
    public function parse(string $sourcePath, string $extension): array
    {
        $extension = strtolower($extension);

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \RuntimeException("Format .{$extension} tidak didukung. Gunakan .xlsx, .xls, atau .csv.");
        }

        ini_set('memory_limit', '512M');

        $reader = match ($extension) {
            'csv' => new Csv,
            'xls' => new Xls,
            'xlsx' => new Xlsx,
        };

        if ($reader instanceof Csv) {
            $reader->setInputEncoding(Csv::guessEncoding($sourcePath));
        }

        try {
            $spreadsheet = $reader->load($sourcePath);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File tidak dapat dibaca: '.$e->getMessage(), 0, $e);
        }

        try {
            $sheet = $spreadsheet->getActiveSheet();
            $columns = $this->mapHeader($sheet);
            $this->assertRequiredColumns($columns);

            $rows = [];
            $highestRow = $sheet->getHighestDataRow();

            for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
                $row = $this->readRow($sheet, $rowNumber, $columns);

                if ($row !== null) {
                    $rows[] = $row;
                }
            }

            return $rows;
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    /**
     * Peta kolom header -> nama field. @return array<string, int> field => index kolom (0-based).
     */
    private function mapHeader(Worksheet $sheet): array
    {
        $columns = [];
        $lastColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($columnNumber = 1; $columnNumber <= $lastColumn; $columnNumber++) {
            $label = $this->cellValue($sheet->getCell(Coordinate::stringFromColumnIndex($columnNumber).'1'));

            if (! is_string($label) || trim($label) === '') {
                continue;
            }

            $label = trim(preg_replace('/[\s_\/.,-]+/u', ' ', mb_strtolower(trim($label))));

            // Header gabungan dapat ditulis "Tempat / Tgl. Lahir".
            if (str_contains($label, 'tempat') && str_contains($label, 'lahir') && (str_contains($label, 'tanggal') || str_contains($label, 'tgl'))) {
                $columns['tempat_tgl_lahir'] = $columnNumber - 1;

                continue;
            }

            foreach (self::HEADER_KEYWORDS as $field => $keywords) {
                if (isset($columns[$field])) {
                    continue;
                }

                foreach ($keywords as $keyword) {
                    if (str_contains($label, $keyword)) {
                        $columns[$field] = $columnNumber - 1;

                        continue 3;
                    }
                }
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, int>  $columns
     */
    private function assertRequiredColumns(array $columns): void
    {
        $missing = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (! isset($columns[$field])) {
                $missing[] = $field;
            }
        }

        if ($missing === []) {
            return;
        }

        $labels = ['nisn' => 'NIS/NISN', 'nama' => 'Nama Lengkap', 'kelas' => 'Kelas', 'industri' => 'Industri'];
        $missingLabels = array_map(fn ($field) => $labels[$field] ?? $field, $missing);

        throw ValidationException::withMessages([
            'file' => 'Kolom wajib tidak ditemukan pada baris judul: '.implode(', ', $missingLabels)
                .'. Pastikan header berisi NIS/NISN, Nama Lengkap, Kelas, dan Industri.',
        ]);
    }

    /**
     * @param  array<string, int>  $columns
     * @return array{nisn: string, nama: string, tempat_tgl_lahir: string, kelas: string, industri: string, peran: string, tanggal: ?string}|null
     */
    private function readRow(Worksheet $sheet, int $rowNumber, array $columns): ?array
    {
        $raw = [];

        foreach ($columns as $field => $index) {
            $coordinate = Coordinate::stringFromColumnIndex($index + 1).$rowNumber;
            $raw[$field] = $this->cellValue($sheet->getCell($coordinate));
        }

        $nisn = is_string($raw['nisn']) ? trim($raw['nisn']) : $this->stringify($raw['nisn']);
        $nama = is_string($raw['nama']) ? trim($raw['nama']) : $this->stringify($raw['nama']);

        if ($nisn === '' && $nama === '') {
            return null;
        }

        return [
            'nisn' => $nisn,
            'nama' => $nama,
            'tempat_tgl_lahir' => $this->birthDetails($raw),
            'kelas' => isset($raw['kelas']) ? $this->stringify($raw['kelas']) : '',
            'industri' => isset($raw['industri']) ? $this->stringify($raw['industri']) : '',
            'peran' => isset($raw['peran']) ? $this->stringify($raw['peran']) : '',
            'tanggal' => isset($raw['tanggal']) ? $this->normalizeDate($raw['tanggal']) : null,
        ];
    }

    private function cellValue(Cell $cell): mixed
    {
        $value = $cell->getValue();

        if ($value instanceof RichText) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_int($value) || is_float($value)) {
            if (Date::isDateTime($cell)) {
                try {
                    return Date::excelToDateTimeObject($value)->format('Y-m-d');
                } catch (\Throwable) {
                    return (string) $value;
                }
            }

            return (string) $value;
        }

        return $value;
    }

    private function birthDetails(array $raw): string
    {
        $combined = $this->stringify($raw['tempat_tgl_lahir'] ?? null);
        if ($combined !== '') {
            return $combined;
        }

        $place = $this->stringify($raw['tempat_lahir'] ?? null);
        $value = $this->stringify($raw['tanggal_lahir'] ?? null);
        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $date = $this->normalizeDate($value);
        $formatted = $date ? Carbon::parse($date)->locale('id')->translatedFormat('d F Y') : $value;

        return implode(' / ', array_filter([$place, $formatted], fn ($part) => $part !== ''));
    }

    private function stringify(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        if (is_float($value)) {
            $value = floor($value) == $value ? (string) (int) $value : (string) $value;
        }

        return trim((string) $value);
    }

    /**
     * Terima berbagai format tanggal yang lazim dipakai Excel/CSV Indonesia.
     */
    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Angka polos YYYYMMDD (Excel tanpa format tanggal).
        if (preg_match('/^\d{8}$/', $value)) {
            try {
                return Carbon::createFromFormat('!Ymd', $value)->format('Y-m-d');
            } catch (\Throwable) {
                // lanjut ke percobaian format lain
            }
        }

        $formats = ['!Y-m-d', '!d/m/Y', '!d-m-Y', '!d.m.Y', '!m/d/Y', '!Y/m/d', '!j n Y'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // format tidak cocok, coba berikutnya
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
