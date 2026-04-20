<?php
namespace Piplup\ImageX\Contracts;

interface ResizerInterface
{
    /**
     * Resize an image from source to target path.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param int $width
     * @param int|null $height
     * @param array $options
     */
    public function resize(string $sourcePath, string $targetPath, int $width, ?int $height = null, array $options = []): void;
}
