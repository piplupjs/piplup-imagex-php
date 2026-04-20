<?php
namespace Piplup\ImageX\Url;

use Piplup\ImageX\Contracts\UrlGeneratorInterface;

class CdnUrlGenerator implements UrlGeneratorInterface
{
    public function generate(string $path, array $options = []): string
    {
        $cdn = $options['cdn'] ?? null;
        if (!empty($cdn)) {
            return rtrim($cdn, '/') . '/' . ltrim($path, '/');
        }

        $base = $options['base'] ?? null;
        if (!empty($base)) {
            return rtrim($base, '/') . '/' . ltrim($path, '/');
        }

        return ltrim($path, '/');
    }
}
