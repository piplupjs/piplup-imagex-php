<?php
namespace Piplup\ImageX\Contracts;

interface ConverterInterface
{
    /**
     * Convert a source image to the target path/format.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param array $options
     */
    public function convert(string $sourcePath, string $targetPath, array $options = []): void;
}
