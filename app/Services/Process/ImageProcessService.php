<?php

namespace App\Services\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessService
{
    private const DISK = 'public';

    public function store(string $folder, ?UploadedFile $image, ?string $oldImage = null): string
    {
        if(!$image) return $oldImage ?? '';
        if ($oldImage) $this->delete($oldImage);

        $folder = 'images/' . trim($folder, '/');

        $manager = new ImageManager(new Driver());
        $imageContent = $manager->read($image)->toWebp(85);

        $name = (string) Str::uuid() . '.' . 'webp';
        $path = $folder . '/' . $name;

        Storage::disk(self::DISK)->put($path, (string) $imageContent);
        return '/storage/' . $path;
    }

    public function delete(string $imagePath): bool
    {
        $filePath = str_replace('/storage/', '', $imagePath);
        
        if (Storage::disk(self::DISK)->exists($filePath)) {
            return Storage::disk(self::DISK)->delete($filePath);
        }

        return false;
    }
}
