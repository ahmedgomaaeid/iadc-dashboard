<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    /**
     * Upload an image, convert it to WebP, and delete the old image if provided.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @param string|null $oldPath
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function uploadImage($file, $path, $oldPath = null, $width = null, $height = null)
    {
        // Delete old image if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Generate a unique filename with .webp extension
        $filename = Str::uuid() . '.webp';
        $fullPath = $path . '/' . $filename;

        // Make sure the directory exists (Storage::put handles this usually, but good to be safe if using other methods)
        
        // Read the image
        $image = Image::read($file);

        // Resize if dimensions are provided
        if ($width || $height) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Encode to WebP
        $encoded = $image->toWebp(75); // Quality 75

        // Store the image
        Storage::disk('public')->put($fullPath, (string) $encoded);

        return $fullPath;
    }
}
