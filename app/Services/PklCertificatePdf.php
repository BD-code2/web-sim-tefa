<?php

namespace App\Services;

use App\Models\SertifikatSiswa;
use Barryvdh\DomPDF\PDF as Document;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use InvalidArgumentException;

class PklCertificatePdf
{
    /** Ukuran gambar acuan sertifikat.png; semua posisi mengikuti gambar ini. */
    private const REFERENCE_WIDTH = 702;

    private const REFERENCE_HEIGHT = 491;

    public function __construct(private readonly PklCertificateAssets $assets) {}

    /** @param iterable<SertifikatSiswa> $certificates */
    public function create(iterable $certificates): Document
    {
        $certificates = collect($certificates)->values();
        if ($certificates->isEmpty()) {
            throw new InvalidArgumentException('Tidak ada data sertifikat untuk dicetak.');
        }

        $images = $this->assets->prepare();
        $document = app('dompdf.wrapper')->loadHTML('<html><head><meta charset="utf-8"><style>@page{margin:0}body{margin:0}</style></head><body></body></html>')
            ->setOptions(['isRemoteEnabled' => false, 'isFontSubsettingEnabled' => true])
            ->setPaper('a4', 'landscape');
        $document->render();
        $canvas = $document->getDomPDF()->getCanvas();

        // Tambah halaman secara eksplisit; tidak ada tinggi/padding HTML yang
        // dapat mendorong bagian bawah sertifikat ke halaman tambahan.
        for ($page = 1; $page < $certificates->count(); $page++) {
            $canvas->new_page();
        }
        $canvas->page_script(function (int $page, int $count, Canvas $canvas, FontMetrics $fonts) use ($certificates, $images) {
            $this->draw($canvas, $fonts, $certificates[$page - 1], $images);
        });

        return $document;
    }

    /** @param array<string, string> $images */
    private function draw(Canvas $canvas, FontMetrics $fonts, SertifikatSiswa $certificate, array $images): void
    {
        $sx = $canvas->get_width() / self::REFERENCE_WIDTH;
        $sy = $canvas->get_height() / self::REFERENCE_HEIGHT;
        $center = self::REFERENCE_WIDTH / 2 * $sx;
        $principalLeft = 54.5 * $sx;
        $industryLeft = 480.5 * $sx;
        // Jaga margin kanan sejajar dengan margin kiri blok Kepala Sekolah.
        $industryWidth = 167 * $sx;
        $sans = $fonts->getFont('DejaVu Sans');
        $bold = $fonts->getFont('DejaVu Sans', 'bold');
        $serifBold = $fonts->getFont('Times', 'bold');
        $serif = $fonts->getFont('Times');
        $italic = $fonts->getFont('Times', 'italic');

        // Gambar dibaca langsung oleh canvas PDF dan digunakan ulang antar
        // halaman. HTML tidak lagi membawa base64 besar untuk setiap siswa.
        $canvas->image($images['background'], 0, 0, $canvas->get_width(), $canvas->get_height());
        $canvas->image($images['watermark'], 265 * $sx, 188 * $sy, 166 * $sx, 145 * $sy);
        $canvas->image($images['kepri'], 49 * $sx, 30 * $sy, 71 * $sx, 94 * $sy);
        $canvas->image($images['school'], 573 * $sx, 29 * $sy, 73 * $sx, 98 * $sy);
        $canvas->image($images['smk_bisa'], 303 * $sx, 355 * $sy, 96 * $sx, 116 * $sy);

        $this->center($canvas, 'Sertifikat', $serifBold, 74 * $sx, $center, 23 * $sy, 360 * $sx);
        $this->center($canvas, 'Nomor : '.($certificate->no_sertifikat ?: '-'), $serif, 12.3 * $sx, $center, 103 * $sy, 490 * $sx);
        $this->center($canvas, 'Diberikan Kepada', $sans, 12 * $sx, $center, 132 * $sy, 500 * $sx);

        $name = mb_strtoupper($this->clean($certificate->nama_siswa ?: '-'));
        if (preg_match('/[^\x20-\x7e]/u', $name)) {
            $italic = $fonts->getFont('DejaVu Serif', 'italic');
        }
        $this->centerBlock($canvas, $name, $italic, 27.6 * $sx, 17 * $sx, $center, 166 * $sy, 535 * $sx, 2, 22 * $sy);

        $birth = $this->clean($certificate->getAttribute('tempat_tgl_lahir') ?: '-');
        $details = [
            ['Nomor Induk Siswa', $certificate->nisn ?: '-'],
            ['Tempat / Tgl. Lahir', $birth],
            ['Konsentrasi Keahlian', $certificate->program_keahlian ?: '-'],
        ];
        foreach ($details as $index => [$label, $value]) {
            $y = (214 + 18 * $index) * $sy;
            $canvas->text(200 * $sx, $y, $label, $sans, 12 * $sx);
            $canvas->text(332 * $sx, $y, ':', $sans, 12 * $sx);
            $this->left($canvas, $this->clean($value), $sans, 12 * $sx, 343 * $sx, $y, 330 * $sx);
        }

        $industry = $this->clean($certificate->nama_industri_pkl ?: ($certificate->industri?->nama_industri ?? '-'));
        $upperIndustry = mb_strtoupper($industry);
        $predicate = mb_strtoupper($this->clean($certificate->predikat ?: '-'));
        $this->center($canvas, 'Telah selesai mengikuti Praktik Kerja Lapangan (PKL) pada', $sans, 11.3 * $sx, $center, 280 * $sy, 600 * $sx);
        $this->centerBlock($canvas, $upperIndustry.' dengan Predikat '.$predicate, $sans, 10 * $sx, 8.5 * $sx, $center, 296 * $sy, 590 * $sx, 2, 13 * $sy);

        $this->left($canvas, 'KEPALA', $sans, 10.5 * $sx, $principalLeft, 327 * $sy, 235 * $sx);
        $this->left($canvas, config('pkl.certificate.school_name'), $sans, 10.2 * $sx, $principalLeft, 344 * $sy, 240 * $sx);
        $this->left($canvas, 'KEPALA', $sans, 10.5 * $sx, $industryLeft, 327 * $sy, $industryWidth);
        $this->centerBlock($canvas, $upperIndustry, $sans, 10.2 * $sx, 8.5 * $sx, $industryLeft + $industryWidth / 2, 344 * $sy, $industryWidth, 2, 13 * $sy, true);

        $this->signatureName($canvas, config('pkl.certificate.principal_name'), $bold, 10.5 * $sx, $principalLeft, 412 * $sy, 245 * $sx);
        $this->left($canvas, 'NIP '.config('pkl.certificate.principal_nip'), $sans, 9.5 * $sx, $principalLeft, 433 * $sy, 245 * $sx);

        $signer = config('pkl.certificate.industry_signers', [])[$upperIndustry] ?? [];
        $signerName = $certificate->getAttribute('industri_pimpinan') ?: ($signer['name'] ?? '........................................');
        $signerNip = $certificate->getAttribute('industri_nip') ?: ($signer['nip'] ?? null);
        $this->signatureName($canvas, $signerName, $bold, 9.3 * $sx, $industryLeft, 412 * $sy, $industryWidth);
        if ($signerNip) {
            $this->left($canvas, 'NIP '.$signerNip, $sans, 9.5 * $sx, $industryLeft, 433 * $sy, $industryWidth);
        }
    }

    private function center(Canvas $canvas, string $text, string $font, float $size, float $center, float $y, float $maxWidth): void
    {
        $size = $this->fit($canvas, $text, $font, $size, $maxWidth);
        $width = $canvas->get_text_width($text, $font, $size);
        $canvas->text($center - $width / 2, $y, $text, $font, $size);
    }

    private function left(Canvas $canvas, string $text, string $font, float $size, float $x, float $y, float $maxWidth): void
    {
        $canvas->text($x, $y, $text, $font, $this->fit($canvas, $text, $font, $size, $maxWidth));
    }

    private function signatureName(Canvas $canvas, string $text, string $font, float $size, float $x, float $y, float $maxWidth): void
    {
        $size = $this->fit($canvas, $text, $font, $size, $maxWidth);
        $width = $canvas->get_text_width($text, $font, $size);
        $canvas->text($x, $y, $text, $font, $size);
        $lineY = $y + $canvas->get_font_height($font, $size);
        $canvas->line($x, $lineY, $x + $width, $lineY, [0, 0, 0], .5);
    }

    /** Teks panjang mengecil atau memakai dua baris di dalam bidang tetap. */
    private function centerBlock(Canvas $canvas, string $text, string $font, float $size, float $minimumSize, float $center, float $y, float $maxWidth, int $maxLines, float $lineHeight, bool $alignLeft = false): void
    {
        $fit = $this->fit($canvas, $text, $font, $size, $maxWidth);
        if ($fit >= $minimumSize) {
            $alignLeft
                ? $this->left($canvas, $text, $font, $fit, $center - $maxWidth / 2, $y, $maxWidth)
                : $this->center($canvas, $text, $font, $fit, $center, $y, $maxWidth);

            return;
        }

        // Pecah berdasarkan kata; bila kedua baris masih terlalu panjang,
        // ukurannya menyesuaikan tanpa membuang nama atau informasi siswa.
        $words = explode(' ', $text);
        $split = max(1, (int) ceil(count($words) / $maxLines));
        $lines = array_map(fn ($chunk) => implode(' ', $chunk), array_chunk($words, $split));
        foreach ($lines as $index => $line) {
            $alignLeft
                ? $this->left($canvas, $line, $font, $minimumSize, $center - $maxWidth / 2, $y + $index * $lineHeight, $maxWidth)
                : $this->center($canvas, $line, $font, $minimumSize, $center, $y + $index * $lineHeight, $maxWidth);
        }
    }

    private function fit(Canvas $canvas, string $text, string $font, float $size, float $maxWidth): float
    {
        $width = $canvas->get_text_width($text, $font, $size);

        return $width > $maxWidth ? $size * $maxWidth / $width : $size;
    }

    private function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
