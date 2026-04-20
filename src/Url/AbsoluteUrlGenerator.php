<?php
namespace Piplup\ImageX\Url;

use Piplup\ImageX\Contracts\UrlGeneratorInterface;

class AbsoluteUrlGenerator implements UrlGeneratorInterface
{
    public function generate(string $path, array $options = []): string
    {
        $base = $options['base'] ?? '';
        if (empty($base)) {
            throw new \InvalidArgumentException('Base URL is required for absolute URL generation');
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
