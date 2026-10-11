<?php

namespace App\Services;

use App\Models\DataSiswa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Merakit file sertifikat PNG dari aset di image/certificate/
 * lalu menyimpannya ke storage/app/public/certificates/.
 */
class CertificateGenerator
{
    private const WIDTH = CertificateAssetFactory::WIDTH;

    private const HEIGHT = CertificateAssetFactory::HEIGHT;

    private const MARGIN = 140;

    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /** @var array{background: string, frame: string, overlay: string, font: string, font_bold: string} */
    private array $assets;

    public function __construct()
    {
        $this->assets = CertificateAssetFactory::ensure();
    }

    /**
     * Generate sertifikat, kembalikan path relatif pada disk public.
     *
     * @throws \RuntimeException bila aset/gambar tidak dapat diproses
     */
    public function generate(DataSiswa $siswa): string
    {
        $canvas = @imagecreatefrompng($this->assets['background']);

        if ($canvas === false) {
            throw new \RuntimeException('Latar sertifikat tidak dapat dibuka. Pastikan image/certificate/background.png valid.');
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        $canvas = $this->fitToCanvas($canvas);

        $navy = imagecolorallocate($canvas, 16, 49, 95);
        $blue = imagecolorallocate($canvas, 10, 77, 186);
        $gold = imagecolorallocate($canvas, 168, 122, 24);
        $slate = imagecolorallocate($canvas, 71, 85, 105);

        $bold = $this->assets['font_bold'];
        $regular = $this->assets['font'];

        $this->centerText($canvas, $bold, 92, $navy, 'SERTIFIKAT PENGHARGAAN', 215);

        imagesetthickness($canvas, 3);
        imageline($canvas, (int) (self::WIDTH / 2) - 250, 255, (int) (self::WIDTH / 2) + 250, 255, $gold);
        imagesetthickness($canvas, 1);

        $this->centerText($canvas, $regular, 38, $slate, 'Diberikan kepada:', 345);
        $this->centerText($canvas, $bold, 96, $blue, $siswa->nama, 475);

        $this->centerText($canvas, $regular, 32, $slate, 'Peran / Penghargaan', 595);
        $this->centerText($canvas, $bold, 60, $navy, $siswa->peran ?: '-', 670);

        $this->centerText($canvas, $regular, 36, $slate, 'Tanggal : '.$this->tanggalIndo($siswa), 775);
        $this->centerText($canvas, $regular, 34, $slate, 'NISN : '.($siswa->nisn ?: '-'), 835);

        $this->centerText($canvas, $bold, 46, $navy, 'SMK NEGERI 4 TANJUNGPINANG', 955);
        $this->centerText($canvas, $regular, 24, $slate, 'Dokumen ini dibuat otomatis oleh SIM Sekolah', 1010);

        $this->paste($canvas, $this->assets['overlay']);
        $this->paste($canvas, $this->assets['frame']);

        $relative = 'certificates/'.$this->filename($siswa);
        $disk = Storage::disk('public');

        if (! $disk->directoryExists('certificates')) {
            $disk->makeDirectory('certificates');
        }

        $written = @imagepng($canvas, $disk->path($relative), 9);
        imagedestroy($canvas);

        if ($written !== true) {
            throw new \RuntimeException('File sertifikat gagal ditulis ke storage/app/public/certificates.');
        }

        return $relative;
    }

    private function filename(DataSiswa $siswa): string
    {
        $nisn = Str::slug($siswa->nisn ?: 'siswa-'.$siswa->id);
        $peran = Str::slug($siswa->peran ?: 'sertifikat');

        return sprintf('%s-%s-%d.png', $nisn, $peran, $siswa->id);
    }

    private function tanggalIndo(DataSiswa $siswa): string
    {
        if ($siswa->tanggal === null) {
            return '-';
        }

        return sprintf(
            '%d %s %d',
            $siswa->tanggal->day,
            self::BULAN[(int) $siswa->tanggal->month],
            $siswa->tanggal->year
        );
    }

    /**
     * Sesuaikan ukuran gambar aset ke kanvas bila berbeda (aset bisa diganti pengguna).
     *
     * @param  \GdImage  $canvas
     * @return \GdImage
     */
    private function fitToCanvas($canvas)
    {
        if (imagesx($canvas) === self::WIDTH && imagesy($canvas) === self::HEIGHT) {
            return $canvas;
        }

        $fitted = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($fitted, true);
        imagesavealpha($fitted, true);
        imagecopyresampled(
            $fitted,
            $canvas,
            0,
            0,
            0,
            0,
            self::WIDTH,
            self::HEIGHT,
            imagesx($canvas),
            imagesy($canvas)
        );
        imagedestroy($canvas);

        return $fitted;
    }

    /**
     * Tempel layer PNG transparan di atas kanvas.
     *
     * @param  \GdImage  $canvas
     */
    private function paste($canvas, string $path): void
    {
        $layer = @imagecreatefrompng($path);

        if ($layer === false) {
            return;
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        imagecopyresampled(
            $canvas,
            $layer,
            0,
            0,
            0,
            0,
            self::WIDTH,
            self::HEIGHT,
            imagesx($layer),
            imagesy($layer)
        );

        imagedestroy($layer);
    }

    /**
     * Tulis teks rata tengah; ukuran font mengecil otomatis bila teks melebihi lebar aman.
     *
     * @param  \GdImage  $canvas
     */
    private function centerText($canvas, string $font, int $size, int $color, string $text, int $y): void
    {
        $text = trim($text);

        if ($text === '' || ! file_exists($font)) {
            return;
        }

        $maxWidth = self::WIDTH - (2 * self::MARGIN);

        while ($size > 18 && $this->textWidth($font, $size, $text) > $maxWidth) {
            $size -= 2;
        }

        $x = (int) round((self::WIDTH - $this->textWidth($font, $size, $text)) / 2);

        imagettftext($canvas, $size, 0, $x, $y, $color, $font, $text);
    }

    private function textWidth(string $font, int $size, string $text): int
    {
        $box = @imagettfbbox($size, 0, $font, $text);

        if ($box === false) {
            return (int) ceil(strlen($text) * $size * 0.55);
        }

        return max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]);
    }
}
