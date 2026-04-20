<?php
namespace Piplup\ImageX\Converters;

use Piplup\ImageX\Contracts\ConverterInterface;

class NullConverter implements ConverterInterface
{
    public function convert(string $sourcePath, string $targetPath, array $options = []): void
    {
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        copy($sourcePath, $targetPath);
    }
}
