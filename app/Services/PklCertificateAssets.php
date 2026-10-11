<?php

namespace App\Services;

use GdImage;
use RuntimeException;

class PklCertificateAssets
{
    /** Aset asli dari contoh sertifikat, termasuk pemetaan watermark dan tanda tangan. */
    private const SOURCES = [
        'background' => ['Gambar ChatGPT 28 Sep 2026, 14.00.25-1.png', 1500],
        'smk_bisa' => ['Gambar ChatGPT 28 Sep 2026, 14.00.26-2.png', 420],
        'kepri' => ['Gambar ChatGPT 28 Sep 2026, 14.00.27-3.png', 360],
        'school' => ['Gambar ChatGPT 28 Sep 2026, 14.00.28-4.png', 360],
        'watermark' => ['Gambar ChatGPT 28 Sep 2026, 14.00.29-5.png', 460],
        'signature' => ['Gambar ChatGPT 28 Sep 2026, 14.00.30-6.png', 520],
    ];

    /** @return array<string, string> */
    public function prepare(): array
    {
        $sourceDirectory = public_path('preference/serifikat');
        $sources = [];
        $version = 'pkl-reference-v3';

        foreach (self::SOURCES as $key => [$name]) {
            $source = $sourceDirectory.DIRECTORY_SEPARATOR.$name;
            if (! is_readable($source)) {
                throw new RuntimeException("Aset contoh sertifikat tidak ditemukan: {$name}");
            }

            $sources[$key] = $source;
            $version .= '|'.$name.'|'.filemtime($source).'|'.filesize($source);
        }

        $directory = storage_path('app/pkl-template/'.substr(hash('sha256', $version), 0, 16));
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder cache aset sertifikat tidak dapat dibuat.');
        }

        $paths = [];
        foreach (self::SOURCES as $key => [, $maximumWidth]) {
            $path = $directory.DIRECTORY_SEPARATOR.$key.($key === 'background' ? '.jpg' : '.png');
            if (! is_file($path) || filesize($path) === 0) {
                $this->optimize($sources[$key], $path, $maximumWidth, $key === 'background');
            }
            $paths[$key] = $path;
        }

        return $paths;
    }

    private function optimize(string $source, string $target, int $maximumWidth, bool $background): void
    {
        $image = imagecreatefrompng($source);
        if ($image === false) {
            throw new RuntimeException('Gambar contoh sertifikat tidak dapat dibaca.');
        }

        $temporary = null;
        $resized = null;
        try {
            if (! $background) {
                // Buang tepian transparan agar ukuran logo mengikuti bentuk aslinya,
                // bukan ukuran kanvas PNG yang mempunyai banyak ruang kosong.
                $cropped = imagecrop($image, $this->visibleBounds($image));
                if ($cropped !== false) {
                    imagedestroy($image);
                    $image = $cropped;
                }
            }

            $width = min($maximumWidth, imagesx($image));
            $height = max(1, (int) round(imagesy($image) * $width / imagesx($image)));
            $resized = imagecreatetruecolor($width, $height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

            $temporary = tempnam(dirname($target), 'pkl-');
            if ($temporary === false) {
                throw new RuntimeException('Cache gambar sertifikat tidak dapat dibuat.');
            }
            $saved = $background ? imagejpeg($resized, $temporary, 92) : imagepng($resized, $temporary, 6);
            if (! $saved || ! rename($temporary, $target)) {
                throw new RuntimeException('Cache gambar sertifikat tidak dapat disimpan.');
            }
        } finally {
            imagedestroy($image);
            if ($resized instanceof GdImage) {
                imagedestroy($resized);
            }
            if (is_string($temporary) && is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    /** @return array{x: int, y: int, width: int, height: int} */
    private function visibleBounds(GdImage $image): array
    {
        $left = $width = imagesx($image);
        $top = $height = imagesy($image);
        $right = $bottom = -1;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                // Abaikan bintik hampir transparan yang jauh dari isi utama PNG.
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) < 120) {
                    $left = min($left, $x);
                    $right = max($right, $x);
                    $top = min($top, $y);
                    $bottom = max($bottom, $y);
                }
            }
        }

        return $right < $left
            ? ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height]
            : ['x' => $left, 'y' => $top, 'width' => $right - $left + 1, 'height' => $bottom - $top + 1];
    }
}
