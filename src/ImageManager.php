<?php
namespace Piplup\ImageX;

use Piplup\ImageX\Contracts\ImageManagerInterface;

class ImageManager implements ImageManagerInterface
{
    public function __construct()
    {
    }

    public function getAttributes(string $path, array $options = []): array
    {
        $factory = new ImageAttributesFactory();
        return $factory->getAttributes($path, $options);
    }
}
