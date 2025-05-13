<?php

namespace App\Services;

use App\Models\SetPart;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OptimizeFileService
{
    public static function optimize(string $filePath)
    {
        $disk = Storage::disk('public');
        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg','jpeg','png'], true) 
            || !$disk->exists($filePath) 
            || $disk->size($filePath) === 0
        ) {
            return;
        }

        $fullPath = $disk->path($filePath);

        try {
            $manager = new ImageManager(Driver::class);
            $image   = $manager->read($fullPath);
            $encoded = $image->toWebp(80, true);
        } catch (\Exception $e) {
            \Log::error("Falha ao decodificar {$fullPath}: ".$e->getMessage());
            return;
        }

        $newPath = preg_replace('/\.\w+$/', '.webp', $filePath);

        $disk->put($newPath, (string) $encoded, 'public');
        $disk->delete($filePath);

        return $newPath;
    }
}
