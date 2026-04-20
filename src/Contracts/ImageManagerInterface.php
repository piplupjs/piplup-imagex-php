<?php
namespace Piplup\ImageX\Contracts;

interface ImageManagerInterface
{
    /**
     * Return attribute array for an image (src, srcset, sizes, width, height, type).
     *
     * @param string $path
     * @param array $options
     * @return array
     */
    public function getAttributes(string $path, array $options = []): array;
}
