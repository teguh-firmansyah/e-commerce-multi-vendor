<?php

namespace App\Services\Media;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageProcessor
{
    private const MAX_PIXELS = 36_000_000;

    public function store(UploadedFile $file, string $directory, int $maxDimension = 1200, string $disk = 'public'): string
    {
        $path = $file->getRealPath();
        $contents = $path === false ? false : file_get_contents($path);
        $info = $contents === false ? false : @getimagesizefromstring($contents);

        if ($contents === false || $info === false || $info[0] * $info[1] > self::MAX_PIXELS) {
            throw new InvalidArgumentException('File bukan gambar yang valid.');
        }

        $source = @imagecreatefromstring($contents);

        if (! $source instanceof GdImage) {
            throw new InvalidArgumentException('File bukan gambar yang valid.');
        }

        if ($info[2] === IMAGETYPE_JPEG && $path !== false) {
            $source = $this->applyOrientation($source, $path);
        }

        $image = $this->resize($source, $maxDimension);

        ob_start();
        imagewebp($image, null, 82);
        $encoded = (string) ob_get_clean();

        $target = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk($disk)->put($target, $encoded);

        return $target;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk($disk)->delete($path);
        }
    }

    private function applyOrientation(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private function resize(GdImage $source, int $maxDimension): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min(1, $maxDimension / max($width, $height));
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if (! $canvas instanceof GdImage) {
            throw new InvalidArgumentException('Gagal memproses gambar.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, (int) imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $canvas;
    }
}
