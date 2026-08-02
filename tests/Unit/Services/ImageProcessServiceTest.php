<?php

namespace Tests\Unit\Services;

use App\Services\Process\ImageProcessService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageProcessServiceTest extends TestCase
{
    public function test_it_stores_uploaded_image_as_webp_on_public_disk(): void
    {
        Storage::fake('public');

        $path = (new ImageProcessService)->store(
            'avatars',
            UploadedFile::fake()->image('avatar.jpg', 320, 320)
        );

        $this->assertStringStartsWith('/storage/images/avatars/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($this->storagePath($path));
    }

    public function test_it_keeps_old_image_when_no_new_image_is_provided(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/avatars/current.webp', 'current');

        $path = (new ImageProcessService)->store(
            'avatars',
            null,
            '/storage/images/avatars/current.webp'
        );

        $this->assertSame('/storage/images/avatars/current.webp', $path);
        Storage::disk('public')->assertExists('images/avatars/current.webp');
    }

    public function test_it_replaces_old_image_when_new_image_is_provided(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/avatars/old.webp', 'old');

        $path = (new ImageProcessService)->store(
            'avatars',
            UploadedFile::fake()->image('avatar.png', 320, 320),
            '/storage/images/avatars/old.webp'
        );

        $this->assertNotSame('/storage/images/avatars/old.webp', $path);
        Storage::disk('public')->assertMissing('images/avatars/old.webp');
        Storage::disk('public')->assertExists($this->storagePath($path));
    }

    public function test_it_deletes_existing_image_from_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/avatars/delete-me.webp', 'old');

        $deleted = (new ImageProcessService)->delete('/storage/images/avatars/delete-me.webp');

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing('images/avatars/delete-me.webp');
    }

    public function test_it_returns_false_when_deleting_missing_image(): void
    {
        Storage::fake('public');

        $deleted = (new ImageProcessService)->delete('/storage/images/avatars/missing.webp');

        $this->assertFalse($deleted);
    }

    private function storagePath(string $publicPath): string
    {
        return str_replace('/storage/', '', $publicPath);
    }
}
