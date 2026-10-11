<?php

namespace App\Services;

/**
 * Menyiapkan aset visual sertifikat pada folder image/certificate/.
 *
 * File yang belum ada dibuat otomatis agar proses generate tidak gagal:
 *   background.png  - latar sertifikat
 *   frame.png       - bingkai dekoratif (transparan)
 *   overlay.png     - elemen overlay / watermark (transparan)
 *   font.ttf        - font reguler (DejaVu Sans, ikut terpasang dompdf)
 *   font-bold.ttf   - font tebal
 */
class CertificateAssetFactory
{
    public const WIDTH = 1600;

    public const HEIGHT = 1131;

    public static function directory(): string
    {
        return base_path('image/certificate');
    }

    /**
     * @return array{background: string, frame: string, overlay: string, font: string, font_bold: string}
     */
    public static function ensure(?string $directory = null): array
    {
        $directory = $directory ?? self::directory();

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Folder aset tidak dapat dibuat: {$directory}");
        }

        $paths = [
            'background' => $directory.'/background.png',
            'frame' => $directory.'/frame.png',
            'overlay' => $directory.'/overlay.png',
            'font' => $directory.'/font.ttf',
            'font_bold' => $directory.'/font-bold.ttf',
        ];

        if (! file_exists($paths['background'])) {
            self::makeBackground($paths['background']);
        }

        if (! file_exists($paths['frame'])) {
            self::makeFrame($paths['frame']);
        }

        if (! file_exists($paths['overlay'])) {
            self::makeOverlay($paths['overlay']);
        }

        if (! file_exists($paths['font'])) {
            self::copyFont('DejaVuSans.ttf', $paths['font']);
        }

        if (! file_exists($paths['font_bold'])) {
            self::copyFont('DejaVuSans-Bold.ttf', $paths['font_bold']);
        }

        return $paths;
    }

    private static function copyFont(string $sourceName, string $target): void
    {
        $source = base_path('vendor/dompdf/dompdf/lib/fonts/'.$sourceName);

        if (! file_exists($source)) {
            throw new \RuntimeException("Font sumber tidak ditemukan: {$source}");
        }

        if (! @copy($source, $target)) {
            throw new \RuntimeException("Font tidak dapat disalin ke {$target}");
        }
    }

    private static function makeBackground(string $target): void
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // Gradasi vertikal krem lembut.
        for ($y = 0; $y < $height; $y++) {
            $t = $y / max(1, $height - 1);
            $red = (int) round(255 + (244 - 255) * $t);
            $green = (int) round(255 + (239 - 255) * $t);
            $blue = (int) round(255 + (226 - 255) * $t);
            imagefilledrectangle($image, 0, $y, $width, $y + 1, imagecolorallocate($image, $red, $green, $blue));
        }

        // Sorotan lembut di tengah.
        imagefilledellipse(
            $image,
            (int) ($width / 2),
            (int) ($height / 2),
            1300,
            760,
            imagecolorallocatealpha($image, 255, 255, 255, 110)
        );

        imagesavealpha($image, true);
        imagepng($image, $target, 9);
        imagedestroy($image);
    }

    private static function makeFrame(string $target): void
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;
        $image = imagecreatetruecolor($width, $height);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagealphablending($image, false);
        imagefill($image, 0, 0, $transparent);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $navy = imagecolorallocate($image, 16, 49, 95);
        $gold = imagecolorallocate($image, 168, 122, 24);

        imagesetthickness($image, 7);
        imagerectangle($image, 34, 34, $width - 35, $height - 35, $navy);

        imagesetthickness($image, 2);
        imagerectangle($image, 50, 50, $width - 51, $height - 51, $gold);
        imagerectangle($image, 64, 64, $width - 65, $height - 65, $navy);

        $corners = [
            [34, 34],
            [$width - 35, 34],
            [34, $height - 35],
            [$width - 35, $height - 35],
        ];

        foreach ($corners as [$x, $y]) {
            imagefilledpolygon($image, [
                $x, $y - 34,
                $x + 34, $y,
                $x, $y + 34,
                $x - 34, $y,
            ], $gold);
            imagefilledellipse($image, $x, $y, 16, 16, $navy);
        }

        $middles = [
            [(int) ($width / 2), 34],
            [(int) ($width / 2), $height - 35],
        ];

        foreach ($middles as [$x, $y]) {
            imagefilledellipse($image, $x, $y, 26, 26, $gold);
            imagefilledellipse($image, $x, $y, 12, 12, $navy);
        }

        imagepng($image, $target, 9);
        imagedestroy($image);
    }

    private static function makeOverlay(string $target): void
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;
        $font = self::directory().'/font-bold.ttf';

        $image = imagecreatetruecolor($width, $height);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagealphablending($image, false);
        imagefill($image, 0, 0, $transparent);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $gold = imagecolorallocatealpha($image, 168, 122, 24, 80);
        $navy = imagecolorallocatealpha($image, 16, 49, 95, 105);

        if (file_exists($font)) {
            $band = imagecreatetruecolor(1500, 240);
            $bandTransparent = imagecolorallocatealpha($band, 0, 0, 0, 127);
            imagealphablending($band, false);
            imagefill($band, 0, 0, $bandTransparent);
            imagealphablending($band, true);
            imagesavealpha($band, true);
            imagettftext($band, 96, 0, 20, 170, $navy, $font, 'SMKN 4 TANJUNGPINANG');

            $rotated = imagerotate($band, 20, $bandTransparent);

            if ($rotated !== false) {
                imagecopyalpha(
                    $image,
                    $rotated,
                    (int) (($width - imagesx($rotated)) / 2),
                    (int) (($height - imagesy($rotated)) / 2),
                    0,
                    0,
                    imagesx($rotated),
                    imagesy($rotated)
                );
                imagedestroy($rotated);
            }

            imagedestroy($band);
        }

        // Aksen pita emas pada sudut kiri-atas & kanan-bawah.
        $ribbons = [
            [0, 0, 1],
            [$width, $height, -1],
        ];

        foreach ($ribbons as [$x, $y, $dir]) {
            imagefilledpolygon($image, [
                $x, $y,
                $x + 320 * $dir, $y,
                $x, $y + 180 * $dir,
            ], $gold);
            imagefilledpolygon($image, [
                $x, $y,
                $x + 210 * $dir, $y,
                $x, $y + 120 * $dir,
            ], $gold);
        }

        imagepng($image, $target, 9);
        imagedestroy($image);
    }
}
