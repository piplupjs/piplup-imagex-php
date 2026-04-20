<?php
namespace Piplup\ImageX\Manager;

use Piplup\ImageX\Generators\ResponsiveGenerator;
use Piplup\ImageX\Contracts\ResizerInterface;
use Piplup\ImageX\Contracts\StorageAdapterInterface;
use Piplup\ImageX\Contracts\ConverterInterface;

class VariantManager
{
    private ResizerInterface $resizer;
    private StorageAdapterInterface $storage;
    private ?ConverterInterface $converter;
    private ResponsiveGenerator $generator;

    public function __construct(ResizerInterface $resizer, StorageAdapterInterface $storage, ?ConverterInterface $converter = null, ?ResponsiveGenerator $generator = null)
    {
        $this->resizer = $resizer;
        $this->storage = $storage;
        $this->converter = $converter;
        $this->generator = $generator ?? new ResponsiveGenerator();
    }

    /**
     * Generate variants for a source image and store them via the storage adapter.
     * Returns array of ['url'=>..., 'width'=>..., 'path'=>relativePath]
     */
    public function generateVariants(string $sourcePath, array $options = []): array
    {
        // Delegate to ensureVariants with force=true to always (re)generate
        $opts = $options;
        $opts['force'] = true;
        return $this->ensureVariants($sourcePath, $opts);
    }

    /**
     * Ensure responsive variants exist; create missing ones and return metadata.
     * Returns array of ['url'=>..., 'width'=>..., 'path'=>..., 'created'=>bool]
     */
    public function ensureVariants(string $sourcePath, array $options = []): array
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("File not found: $sourcePath");
        }

        $info = getimagesize($sourcePath);
        if ($info === false) {
            throw new \RuntimeException("Unable to read image size for: $sourcePath");
        }

        $originalWidth = (int)$info[0];
        $widths = $this->generator->computeWidths($originalWidth, $options);

        $storageBase = rtrim($options['storage_path'] ?? 'uploads/variants', '/');
        $basename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $basename = preg_replace('/[^A-Za-z0-9_\-]/', '-', $basename);
        $origExt = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $format = $options['format'] ?? $origExt;
        $now = new \DateTime();

        $variants = [];

        foreach ($widths as $w) {
            $relativePath = $this->buildRelativePath($storageBase, $now, $basename, $w, $format);

            $exists = $this->storage->exists($relativePath);
            if ($exists && empty($options['force'])) {
                $variants[] = ['url' => $this->storage->url($relativePath), 'width' => (int)$w, 'path' => $relativePath, 'created' => false];
                continue;
            }

            $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'imagex_' . uniqid() . '_' . $w . '.' . $origExt;

            $resizeOptions = $options['resize_options'] ?? [];
            if (!empty($options['format'])) {
                $resizeOptions['format'] = $options['format'];
            }

            $this->resizer->resize($sourcePath, $temp, (int)$w, null, $resizeOptions);

            $finalSource = $temp;
            if (!empty($options['format']) && $this->converter !== null) {
                $converted = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'imagex_conv_' . uniqid() . '_' . $w . '.' . $format;
                $this->converter->convert($temp, $converted, $options);
                if (file_exists($temp)) {
                    @unlink($temp);
                }
                $finalSource = $converted;
            }

            $contents = file_get_contents($finalSource);
            $this->storage->put($relativePath, $contents);
            $url = $this->storage->url($relativePath);

            if (file_exists($finalSource) && strpos($finalSource, sys_get_temp_dir()) === 0) {
                @unlink($finalSource);
            }

            $variants[] = ['url' => $url, 'width' => (int)$w, 'path' => $relativePath, 'created' => true];
        }

        return $variants;
    }

    private function buildRelativePath(string $storageBase, \DateTime $now, string $basename, int $width, string $format): string
    {
        return sprintf('%s/%s/%s/%s-%dw.%s', $storageBase, $now->format('Y'), $now->format('m'), $basename, $width, $format);
    }
}
