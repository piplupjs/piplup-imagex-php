<?php
namespace Piplup\ImageX\Resizer;

use Piplup\ImageX\Contracts\ResizerInterface;

class SimpleResizer implements ResizerInterface
{
    public function resize(string $sourcePath, string $targetPath, int $width, ?int $height = null, array $options = []): void
    {
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        [$origW, $origH, $type] = getimagesize($sourcePath);
        if ($height === null) {
            $height = (int) round($width * $origH / $origW);
        }

        if (class_exists('Imagick')) {
            $im = new \Imagick($sourcePath);
            $im->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
            if (!empty($options['format'])) {
                $im->setImageFormat($options['format']);
            }
            if (isset($options['quality'])) {
                $im->setImageCompressionQuality((int)$options['quality']);
            }
            $im->writeImage($targetPath);
            $im->clear();
            $im->destroy();
            return;
        }

        // GD fallback
        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($sourcePath);
                $saveFunc = 'imagejpeg';
                $quality = $options['quality'] ?? 85;
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($sourcePath);
                $saveFunc = 'imagepng';
                $quality = $options['quality'] ?? 6;
                break;
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($sourcePath);
                $saveFunc = 'imagegif';
                $quality = null;
                break;
            default:
                $srcImg = imagecreatefromstring(file_get_contents($sourcePath));
                $saveFunc = 'imagejpeg';
                $quality = $options['quality'] ?? 85;
                break;
        }

        $dstImg = imagecreatetruecolor($width, $height);
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($dstImg, imagecolorallocatealpha($dstImg, 0, 0, 0, 127));
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $width, $height, imagesx($srcImg), imagesy($srcImg));

        if ($saveFunc === 'imagejpeg') {
            imagejpeg($dstImg, $targetPath, $quality);
        } elseif ($saveFunc === 'imagepng') {
            imagepng($dstImg, $targetPath, $quality);
        } elseif ($saveFunc === 'imagegif') {
            imagegif($dstImg, $targetPath);
        } else {
            imagejpeg($dstImg, $targetPath, $quality ?? 85);
        }

        imagedestroy($dstImg);
        imagedestroy($srcImg);
    }
}
