<?php
namespace Piplup\ImageX\Url;

use Piplup\ImageX\Contracts\UrlGeneratorInterface;

class RelativeUrlGenerator implements UrlGeneratorInterface
{
    public function generate(string $path, array $options = []): string
    {
        return ltrim($path, '/');
    }
}
