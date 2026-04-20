<?php
namespace Piplup\ImageX\Contracts;

interface StorageAdapterInterface
{
    /**
     * Store contents at the given path and return the stored path.
     */
    public function put(string $path, string $contents): string;

    public function exists(string $path): bool;

    public function url(string $path): string;

    public function get(string $path): string;

    public function delete(string $path): bool;
}
