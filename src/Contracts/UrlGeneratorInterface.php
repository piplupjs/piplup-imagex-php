<?php
namespace Piplup\ImageX\Contracts;

interface UrlGeneratorInterface
{
    /**
     * Generate a URL from a storage path.
     * Options may include 'base' and 'cdn'.
     */
    public function generate(string $path, array $options = []): string;
}
