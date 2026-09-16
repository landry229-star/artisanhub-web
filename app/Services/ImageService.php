<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function store(UploadedFile $file, string $folder, int $maxWidth=800, int $maxHeight=800, int $quality=85, string $disk='public'): string
    {
        $filename = $folder . '/' . Str::random(32) . '.jpg';
        $source   = $this->createImage($file);
        if (!$source) return $file->store($folder, $disk);

        $origW = imagesx($source); $origH = imagesy($source);
        $ratio = min($maxWidth/$origW, $maxHeight/$origH, 1);
        $newW  = (int)round($origW*$ratio); $newH = (int)round($origH*$ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start(); imagejpeg($resized, null, $quality); $data = ob_get_clean();
        imagedestroy($source); imagedestroy($resized);
        Storage::disk($disk)->put($filename, $data);
        return $filename;
    }

    public function storeSquare(UploadedFile $file, string $folder, int $size=300, int $quality=85, string $disk='public'): string
    {
        $filename = $folder . '/' . Str::random(32) . '.jpg';
        $source   = $this->createImage($file);
        if (!$source) return $file->store($folder, $disk);

        $origW = imagesx($source); $origH = imagesy($source);
        $sq = min($origW,$origH);
        $ox = (int)(($origW-$sq)/2); $oy = (int)(($origH-$sq)/2);

        $square  = imagecreatetruecolor($sq,$sq);
        imagecopy($square,$source,0,0,$ox,$oy,$sq,$sq);
        $resized = imagecreatetruecolor($size,$size);
        imagefill($resized,0,0,imagecolorallocate($resized,255,255,255));
        imagecopyresampled($resized,$square,0,0,0,0,$size,$size,$sq,$sq);

        ob_start(); imagejpeg($resized,null,$quality); $data=ob_get_clean();
        imagedestroy($source); imagedestroy($square); imagedestroy($resized);
        Storage::disk($disk)->put($filename,$data);
        return $filename;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function createImage(UploadedFile $file)
    {
        return match($file->getMimeType()) {
            'image/png'  => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default      => @imagecreatefromjpeg($file->getRealPath()),
        };
    }
}
