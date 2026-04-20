<?php
namespace Piplup\ImageX;

use Piplup\ImageX\Contracts\StorageAdapterInterface;

class ImageAttributesFactory
{
    public function getAttributes(string $path, array $options = []): array
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("File not found: $path");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new \RuntimeException("Unable to read image size for: $path");
        }

        $originalWidth = (int)$info[0];
        $originalHeight = (int)$info[1];
        $type = $info[2] ?? null;
        $mime = $type ? image_type_to_mime_type($type) : 'image/*';

        $provided = $options['widths'] ?? null;
        if (is_array($provided) && count($provided) > 0) {
            $widths = array_values(array_unique(array_map('intval', $provided)));
        } else {
            $widths = $this->computeWidths($originalWidth);
        }

        $widths = array_filter($widths, function ($w) use ($originalWidth) {
            return $w > 0 && $w <= $originalWidth;
        });
        sort($widths);
        if (empty($widths)) {
            $widths = [$originalWidth];
        }

        // If generated/stored variants are provided, prefer them and map
        // through the storage adapter to produce proper URLs for srcset.
        $srcsetParts = [];
        if (!empty($options['generated_variants']) && is_array($options['generated_variants'])) {
            $generated = $options['generated_variants'];
            $storage = $options['storage_adapter'] ?? null;
            foreach ($generated as $k => $p) {
                if (is_array($p)) {
                    $width = isset($p['width']) ? (int)$p['width'] : null;
                    $storedPath = $p['path'] ?? ($p['src'] ?? null);
                } else {
                    $width = is_numeric($k) ? (int)$k : null;
                    $storedPath = $p;
                }

                if ($width === null || $storedPath === null) {
                    continue;
                }

                if ($storage instanceof StorageAdapterInterface) {
                    $url = $storage->url($storedPath);
                } else {
                    $url = $storedPath;
                }

                $srcsetParts[] = sprintf('%s %dw', $url, $width);
            }
        } else {
            foreach ($widths as $w) {
                $srcsetParts[] = sprintf('%s %dw', $path, $w);
            }
        }

        return [
            'src' => $path,
            'srcset' => implode(', ', $srcsetParts),
            'sizes' => $options['sizes'] ?? '100vw',
            'width' => $originalWidth,
            'height' => $originalHeight,
            'type' => $mime,
        ];
    }

    private function computeWidths(int $originalWidth): array
    {
        $candidates = [320, 480, 640, 960, 1280, 1600, $originalWidth];
        $widths = array_values(array_unique(array_filter($candidates, function ($w) use ($originalWidth) {
            return $w > 0 && $w <= $originalWidth;
        })));
        sort($widths);
        return $widths;
    }
}
