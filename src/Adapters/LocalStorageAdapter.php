<?php
namespace Piplup\ImageX\Adapters;

use Piplup\ImageX\Contracts\StorageAdapterInterface;

class LocalStorageAdapter implements StorageAdapterInterface
{
    private string $basePath;
    private ?string $baseUrl;

    public function __construct(string $basePath, ?string $baseUrl = null)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->baseUrl = $baseUrl;
    }

    private function fullPath(string $path): string
    {
        $path = ltrim($path, '/\\');
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    public function put(string $path, string $contents): string
    {
        $full = $this->fullPath($path);
        $dir = dirname($full);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($full, $contents);
        return $full;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->fullPath($path));
    }

    public function url(string $path): string
    {
        if ($this->baseUrl) {
            return rtrim($this->baseUrl, '/') . '/' . ltrim(str_replace('\\', '/', $path), '/');
        }
        return $this->fullPath($path);
    }

    public function get(string $path): string
    {
        return file_get_contents($this->fullPath($path));
    }

    public function delete(string $path): bool
    {
        $full = $this->fullPath($path);
        if (file_exists($full)) {
            return unlink($full);
        }
        return false;
    }
}
