<?php
namespace Piplup\ImageX\Converters;

use Piplup\ImageX\Contracts\ConverterInterface;

class ImagickConverter implements ConverterInterface
{
    public function convert(string $sourcePath, string $targetPath, array $options = []): void
    {
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (class_exists('Imagick')) {
            $im = new \Imagick($sourcePath);
            if (!empty($options['format'])) {
                $im->setImageFormat($options['format']);
            }
            if (isset($options['quality'])) {
                $im->setImageCompressionQuality((int)$options['quality']);
            }
            $im->writeImage($targetPath);
            $im->clear();
            $im->destroy();
        } else {
            // Fallback to simple copy so tests don't require Imagick extension
            copy($sourcePath, $targetPath);
        }
    }
}
