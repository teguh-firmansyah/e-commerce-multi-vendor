<?php

namespace Tests\Feature\Media;

use App\Services\Media\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    public function test_large_images_are_resized_and_converted_to_webp(): void
    {
        Storage::fake('public');

        $path = app(ImageProcessor::class)->store(
            UploadedFile::fake()->image('foto.jpg', 3000, 2000),
            'tests',
            1200,
        );

        $this->assertStringStartsWith('tests/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $size = getimagesizefromstring((string) Storage::disk('public')->get($path));

        $this->assertSame(1200, $size[0]);
        $this->assertSame(800, $size[1]);
    }

    public function test_small_images_are_not_upscaled(): void
    {
        Storage::fake('public');

        $path = app(ImageProcessor::class)->store(
            UploadedFile::fake()->image('kecil.png', 400, 300),
            'tests',
            1200,
        );

        $size = getimagesizefromstring((string) Storage::disk('public')->get($path));

        $this->assertSame(400, $size[0]);
        $this->assertSame(300, $size[1]);
    }

    public function test_files_that_are_not_images_are_refused(): void
    {
        Storage::fake('public');

        $this->expectException(InvalidArgumentException::class);

        app(ImageProcessor::class)->store(
            UploadedFile::fake()->createWithContent('palsu.jpg', 'bukan gambar'),
            'tests',
        );
    }

    public function test_images_can_be_deleted(): void
    {
        Storage::fake('public');

        $processor = app(ImageProcessor::class);
        $path = $processor->store(UploadedFile::fake()->image('a.jpg', 100, 100), 'tests');

        $processor->delete($path);

        Storage::disk('public')->assertMissing($path);
    }
}
